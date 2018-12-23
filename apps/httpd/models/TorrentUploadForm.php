<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:45
 */

namespace apps\httpd\models;

use Mix\Facades\Config;
use Mix\Facades\PDO;
use Mix\Facades\Session;
use Mix\Validators\Validator;

use SandFoxMe\Bencode\Bencode;
use SandFoxMe\Bencode\Exceptions\ParseErrorException;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class TorrentUploadForm extends Validator
{

    public $id = 0;

    public $name;

    /**  @var \mix\Http\UploadFile */
    public $file;

    public $descr;

    public $uplver = "no";

    private $info_hash;

    private $file_dict;
    private $file_list = [];
    private $file_type = 'single';
    private $total_length = 0;

    // 规则
    public static function rules()
    {
        return [
            'name' => [new Assert\NotBlank(),],
            'file' => [new Assert\NotBlank(),],  // We use Callback `isValidTorrent` since Assert\File() is broken in this project
            'descr' => [new Assert\NotBlank(),],
            'uplver' => [new Assert\Choice(['yes', 'no'])]
        ];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $rules = self::rules();
        foreach ($rules as $property => $constraints) {
            $metadata->addPropertyConstraints($property, $constraints);
        }
        $metadata->addConstraint(new Assert\Callback([
            'callback' => 'isValidTorrent',
            'payload' => ["name" => "file", "maxSize" => Config::get("torrent.max_file_size"), "mimeTypes" => "application/x-bittorrent"]
        ]));
    }

    public function isValidTorrent(ExecutionContextInterface $context, $payload)
    {
        $this->validateFile($context, $payload);
        $this->file_dict = Bencode::load($this->file->getPathname());

        try {
            $info = $this->checkTorrentDict($this->file_dict, 'info');
            if ($info) {
                $dname = $this->checkTorrentDict($info, 'name', 'string');
                $plen = $this->checkTorrentDict($info, 'piece length', 'integer');
                $pieces = $this->checkTorrentDict($info, 'pieces', 'string');

                if (strlen($pieces) % 20 != 0) throw new ParseErrorException('std_invalid_pieces');

                if (isset($info['length'])) {
                    $this->total_length = $info['length'];
                    $this->file_list[] = [$dname, $info['length']];
                    $this->file_type = "single";
                } else {
                    $f_list = $this->checkTorrentDict($info, "files", "array");
                    if (!isset($f_list)) throw new ParseErrorException('std_missing_length_and_files');
                    if (!count($f_list)) throw new ParseErrorException('no files');

                    $this->total_length = 0;
                    foreach ($f_list as $fn) {
                        $ll = $this->checkTorrentDict($fn, "length", "integer");
                        $ff = $this->checkTorrentDict($fn, "path", "list");
                        $this->total_length += $ll;
                        $ffa = [];
                        foreach ($ff as $ffe) {
                            if (is_string($ffe)) throw new ParseErrorException('std_filename_errors');
                            $ffa[] = $ffe;
                        }
                        if (!count($ffa)) throw new ParseErrorException('std_filename_errors');
                        $ffe = implode("/", $ffa);
                        $this->file_list[] = array($ffe, $ll);
                    }
                    $this->file_type = "multi";
                }
            }
        } catch (ParseErrorException $e) {
            $context->buildViolation($e->getMessage())->addViolation();
        }
    }

    public function makePrivateTorrent()
    {
        $this->file_dict['announce'] = Config::get("base.site_tracker_url") . "/announce";

        // Remove un-need field in private torrents
        unset($this->file_dict['announce-list']); // remove multi-tracker capability
        unset($this->file_dict['nodes']); // remove cached peers (Bitcomet & Azareus)

        // The following line requires uploader to re-download torrents after uploading **Since info_hash change**
        // even the torrent is set as private and with uploader's passkey in it.
        $this->file_dict['info']['private'] = 1;  // add private tracker flag
        $this->file_dict['info']['source'] = "Powered by [" . Config::get("base.site_url") . "] " . Config::get("base.site_name");

        // Get info_hash on new torrent content dict['info']
        $this->info_hash = pack("H*", sha1(Bencode::encode($this->file_dict['info'])));
    }

    /**
     * @throws \Exception
     */
    public function flush()
    {
        $this->makePrivateTorrent();

        $data = [
            'owner_id'    => Session::get('userInfo')['uid'],
            'info_hash' => $this->info_hash,
            'status' => 'confirmed',  // TODO set torrent status when upload
            'name'=> $this->name,
            'filename' => $this->file->getBaseName(),
            'descr' => $this->descr,
            'size' => $this->total_length
        ];

        PDO::beginTransaction();
        try {
            PDO::insert('torrents', $data)->execute();
            $this->id = PDO::getLastInsertId();

            // Insert files table
            PDO::delete('files',[['torrent_id' ,'=', $this->id]])->execute();
            $files = array_map(function ($v) {
                return ["torrent_id" => $this->id, "filename" => $v[0], "size" => $v[1]];
            }, $this->file_list);
            PDO::batchInsert('files', $files)->execute();

            $this->setBuff();

            $this->file->saveAs(app()->getPrivatePath('torrents') . DIRECTORY_SEPARATOR . $this->id . ".torrent");

            PDO::commit();
        } catch (\Exception $e) {
            PDO::rollback();
            if ($this->id != 0) {
                unlink(app()->getPrivatePath('torrents') . DIRECTORY_SEPARATOR . $this->id . ".torrent");
            }

            if ($e->getCode() == 23000)
                throw new \Exception('std_torrent_existed');

            throw $e;
        }
    }


    private function setBuff()
    {
        // Add Large Buff and Random Buff
        if (Config::get("buff.enable_large") && $this->file->getSize() > Config::get("buff.large_size")) {
            // TODO PDO::createCommand();
        } elseif (Config::get("buff.enable_random")) {
            // TODO PDO::createCommand();
        }

        // TODO get uploader (or you can say torrents owner) buff

    }


    /**
     * @param $dict
     * @param $key
     * @param null $type
     * @return mixed
     * @throws ParseErrorException
     */
    private function checkTorrentDict($dict, $key, $type = null)
    {
        if (!is_array($dict)) throw new ParseErrorException("std_not_a_dictionary");

        $value = $dict[$key];
        if (!$value) throw new ParseErrorException("std_dictionary_is_missing_key");

        if (!is_null($type)) {
            $isFunction = 'is_' . $type;
            if (\function_exists($isFunction) && !$isFunction($value)) {
                throw new ParseErrorException("std_invalid_entry_in_dictionary");
            }
        }
        return $value;
    }
}
