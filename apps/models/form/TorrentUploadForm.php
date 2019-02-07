<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:45
 */

namespace apps\models\form;

use apps\models\Torrent;

use Rid\Validators\Validator;
use Rid\Bencode\Bencode;
use Rid\Bencode\ParseErrorException;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class TorrentUploadForm extends Validator
{

    public $id = 0;

    /**  @var \Rid\Http\UploadFile */
    public $file;

    public $title;
    public $subtitle = "";
    public $descr;
    public $uplver = "no";  // If user upload this torrent Anonymous

    private $info_hash; // the value of sha1($this->$torrent_dict['info'])

    private $status = 'confirmed';

    private $torrent_dict;
    private $torrent_name;    // the $torrent_dict['info']['name'] field
    private $torrent_list = [];  // the file list like ["filename" => "example.txt" , "size" => 12345]
    private $torrent_structure = [];
    private $torrent_type = 'single'; // only in ['single','multi']
    private $torrent_size = 0;  // the count of torrent's content size

    const TORRENT_TYPE_SINGLE = 'single';
    const TORRENT_TYPE_MULTI = 'multi';


    // 规则
    public static function rules()
    {
        return [
            'title' => [new Assert\NotBlank(),],
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
            'payload' => ['name' => 'file', 'maxSize' => app()->config->get("torrent.max_file_size"), 'mimeTypes' => 'application/x-bittorrent']
        ]));
    }

    public function isValidTorrent(ExecutionContextInterface $context, $payload)
    {
        $this->validateFile($context, $payload);

        try {
            $this->torrent_dict = Bencode::load($this->file->getPathname());
            $info = $this->checkTorrentDict($this->torrent_dict, 'info');
            if ($info) {
                $this->checkTorrentDict($info, 'piece length', 'integer');  // Only Check without use

                $dname = $this->checkTorrentDict($info, 'name', 'string');
                $pieces = $this->checkTorrentDict($info, 'pieces', 'string');

                if (strlen($pieces) % 20 != 0) throw new ParseErrorException('std_invalid_pieces');

                if (isset($info['length'])) {
                    $this->torrent_size = $info['length'];
                    $this->torrent_list[] = ['filename' => $dname, 'size' => $info['length'], 'torrent_id' => &$this->id];
                    $this->torrent_type = 'single';
                } else {
                    $f_list = $this->checkTorrentDict($info, 'files', 'array');
                    if (!isset($f_list)) throw new ParseErrorException('std_missing_length_and_files');
                    if (!count($f_list)) throw new ParseErrorException('no files');

                    $this->torrent_size = 0;
                    foreach ($f_list as $fn) {
                        $ll = $this->checkTorrentDict($fn, 'length', 'integer');
                        $path_key = isset($fn['path.utf-8']) ? 'path.utf-8' : 'path';
                        $ff = $this->checkTorrentDict($fn, $path_key, 'list');
                        $this->torrent_size += $ll;
                        $ffa = [];
                        foreach ($ff as $ffe) {
                            if (!is_string($ffe)) throw new ParseErrorException('std_filename_errors');
                            $ffa[] = $ffe;
                        }
                        if (!count($ffa)) throw new ParseErrorException('std_filename_errors');
                        $ffe = implode("/", $ffa);
                        $this->torrent_list[] = ['filename' => $ffe, 'size' => $ll, 'torrent_id' => &$this->id];
                    }
                    $this->torrent_type = 'multi';
                }
            }
        } catch (ParseErrorException $e) {
            // FIXME Fix message of ParseErrorException
            $context->buildViolation($e->getMessage())->addViolation();
            return;
        }

        $this->torrent_name = $info['name'];
        $this->torrent_structure = $this->getFileTree();
    }

    public function makePrivateTorrent()
    {
        $this->torrent_dict['announce'] = "https://" . app()->config->get("base.site_tracker_url") . "/announce";

        // Remove un-need field in private torrents
        unset($this->torrent_dict['announce-list']); // remove multi-tracker capability
        unset($this->torrent_dict['nodes']); // remove cached peers (Bitcomet & Azareus)

        // Some other change if you need
        //$this->torrent_dict['commit'] = "";

        /**
         * The following line requires uploader to re-download torrents after uploading **Since info_hash change**
         * even the torrent is set as private and with uploader's passkey in it.
         */

        // Clean The `info` dict
        $allowed_keys = [
            'files', 'name', 'piece length', 'pieces', 'private', 'length',
            'name.utf8', 'name.utf-8', 'md5sum', 'sha1', 'source',
            'file-duration', 'file-media', 'profiles'
        ];
        foreach ($this->torrent_dict['info'] as $key => $value) {
            if (!in_array($key, $allowed_keys)) {
                unset($this->torrent_dict['info'][$key]);
            }
        }

        // Make it private and unique by add our source flag
        $this->torrent_dict['info']['private'] = 1;  // add private tracker flag
        $this->torrent_dict['info']['source'] = "Powered by [" . app()->config->get("base.site_url") . "] " . app()->config->get("base.site_name");

        // Get info_hash on new torrent content dict['info']
        $this->info_hash = pack("H*", sha1(Bencode::encode($this->torrent_dict['info'])));
    }

    /**
     * @throws \Exception
     */
    public function flush()
    {
        $this->makePrivateTorrent();

        // Check if this torrent is exist or not before insert.
        $count = app()->pdo->createCommand('SELECT COUNT(*) FROM torrents WHERE info_hash = :info_hash')->bindParams([
            'info_hash' => $this->info_hash
        ])->queryScalar();
        if ($count > 0) throw new \Exception('std_torrent_existed');

        // TODO update torrent status based on user class or their owned torrents count

        app()->pdo->beginTransaction();
        try {
            app()->pdo->insert('torrents', [
                'owner_id' => app()->user->getId(),
                'info_hash' => $this->info_hash,
                'status' => $this->status,
                'title' => $this->title,
                'subtitle' => $this->subtitle,
                'category' => 1,    // FIXME add category support
                'filename' => $this->file->getBaseName(),
                'torrent_name' => $this->torrent_name,
                'torrent_type' => $this->torrent_type,
                'torrent_size' => $this->torrent_size,
                'torrent_structure' => $this->torrent_structure,
                'descr' => $this->descr,
                'uplver' => $this->uplver,
            ])->execute();
            $this->id = app()->pdo->getLastInsertId();

            // Insert files table
            app()->pdo->batchInsert('files', $this->torrent_list)->execute();

            $this->setBuff();

            // Save this torrent
            $dump_status = Bencode::dump(Torrent::TorrentFileLoc($this->id), $this->torrent_dict);
            if ($dump_status == false) {
                throw new \Exception('std_torrent_cannot_save');
            }

            app()->pdo->commit();
        } catch (\Exception $e) {
            // Delete the saved torrent file when torrent save success but still get Exception on other side
            if (isset($dump_status) && $dump_status == true) {
                unlink(Torrent::TorrentFileLoc($this->id));
            }

            app()->pdo->rollback();

            throw $e;
        } finally {
            unlink($this->file->getPathname());  // Remove temp file
        }
    }

    private function setBuff()
    {
        // Add Large Buff and Random Buff
        if (app()->config->get("buff.enable_large") && $this->file->getSize() > app()->config->get("buff.large_size")) {
            // TODO app()->pdo->createCommand();
        } elseif (app()->config->get("buff.enable_random")) {
            // TODO app()->pdo->createCommand();
        }

        // TODO get uploader (or you can say torrents owner) buff

    }

    /**
     * the return array is like this when it's `single` torrent
     *
     * [
     *    "f1.text" => 1234
     * ]
     *
     * And will convert to `tree` like this when it's `multi` torrent by using the
     * private static function makeFileTree($array, $delimiter = '/')
     *
     * [
     *    "f1" => [
     *        "f2.text" => 1234,
     *        "f3.text" => 2234
     *     ]
     * ]
     *
     * Each result will be cached in redis since it will never change.
     *
     * @return bool|string
     */
    private function getFileTree() {
        $structure = array_column($this->torrent_list, 'size', 'filename');
        if ($this->torrent_type == self::TORRENT_TYPE_MULTI) {
            $structure = [$this->torrent_name => self::makeFileTree($structure)];
        }
        return json_encode($structure);
    }

    private static function makeFileTree($array, $delimiter = '/')
    {
        if (!is_array($array)) return array();

        $splitRE = '/' . preg_quote($delimiter, '/') . '/';
        $returnArr = array();
        foreach ($array as $key => $val) {
            // Get parent parts and the current leaf
            $parts = preg_split($splitRE, $key, -1, PREG_SPLIT_NO_EMPTY);
            $leafPart = array_pop($parts);

            // Build parent structure
            // Might be slow for really deep and large structures
            $parentArr = &$returnArr;
            foreach ($parts as $part) {
                if (!isset($parentArr[$part])) {
                    $parentArr[$part] = array();
                } elseif (!is_array($parentArr[$part])) {
                    $parentArr[$part] = array();
                }
                $parentArr = &$parentArr[$part];
            }

            // Add the final part to the structure
            if (empty($parentArr[$leafPart])) {
                $parentArr[$leafPart] = $val;
            }
        }
        return $returnArr;
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
