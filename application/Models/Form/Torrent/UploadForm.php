<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:45
 */

namespace App\Models\Form\Torrent;

use App\Libraries\Constant;
use App\Entity\Torrent\TorrentStatus;
use App\Entity\Torrent\TorrentType;

use Rid\Http\UploadFile;

use Rhilip\Bencode\Bencode;
use Rhilip\Bencode\ParseErrorException;

class UploadForm extends EditForm
{

    /**  @var UploadFile */
    public $file;

    private $info_hash; // the value of sha1($this->$torrent_dict['info'])

    private $status = TorrentStatus::CONFIRMED;

    private $torrent_dict;
    private $torrent_name;    // the $torrent_dict['info']['name'] field
    private $torrent_list = [];  // the file list like ["filename" => "example.txt" , "size" => 12345]
    private $torrent_structure;  // JSON encode string
    private $torrent_type = TorrentType::SINGLE; // only in ['single','multi']
    private $torrent_size = 0;  // the count of torrent's content size

    protected $file_name_check_rules;

    public function getId(): int
    {
        return $this->id;
    }

    public static function defaultData(): array
    {
        return [
            'subtitle' => '',
            'anonymous' => 0, 'hr' => 0,
            'audio' => 0, 'codec' => 0, 'medium' => 0, 'resolution' => 0,
            'team' => 0,
            'tags' => ''
        ];
    }

    public static function inputRules(): array
    {
        $rules = static::baseTorrentRules();
        $rules['file'] = [
            ['required'],
            ['Upload\Required'],
            ['Upload\Extension', ['allowed' => 'torrent']],
            ['Upload\Size', ['size' => config('upload.max_torrent_file_size') . 'B']]
        ];

        if (config('torrent_upload.enable_upload_nfo') &&  // Enable nfo upload
            app()->auth->getCurUser()->isPrivilege('upload_nfo_file') &&  // This user can upload nfo
            app()->request->request->get('nfo')  // Nfo file upload
        ) {
            $rules['nfo'] = [
                ['Upload\Extension', ['allowed' => ['nfo', 'txt']]],
                ['Upload\Size', ['size' => config('upload.max_nfo_file_size') . 'B']]
            ];
        }

        return $rules;
    }

    public static function callbackRules(): array
    {
        return ['checkUploadPos', 'isValidTorrentFile', 'makePrivateTorrent'];
    }

    /** @noinspection PhpUnused */
    protected function checkUploadPos()
    {
        if (!app()->auth->getCurUser()->getUploadpos()) {
            $this->buildCallbackFailMsg('pos', 'your upload pos is disabled');
        }
    }

    /** @noinspection PhpUnused */
    protected function isValidTorrentFile()
    {
        try {
            $this->torrent_dict = Bencode::load($this->getInput('file')->tmpName);
            $info = $this->checkTorrentDict($this->torrent_dict, 'info');
            if ($info) {
                $this->checkTorrentDict($info, 'piece length', 'integer');  // Only Check without use

                $dname = $this->checkTorrentDict($info, 'name', 'string');
                $pieces = $this->checkTorrentDict($info, 'pieces', 'string');

                if (strlen($pieces) % 20 != 0) {
                    throw new ParseErrorException('std_invalid_pieces');
                }

                if (isset($info['length'])) {
                    $this->torrent_size = $info['length'];
                    $this->torrent_list[] = ['filename' => $dname, 'size' => $info['length']];
                    $this->torrent_type = 'single';
                } else {
                    $f_list = $this->checkTorrentDict($info, 'files', 'array');
                    if (!isset($f_list)) {
                        throw new ParseErrorException('std_missing_length_and_files');
                    }
                    if (!count($f_list)) {
                        throw new ParseErrorException('no files');
                    }

                    $this->torrent_size = 0;
                    foreach ($f_list as $fn) {
                        $ll = $this->checkTorrentDict($fn, 'length', 'integer');
                        $path_key = isset($fn['path.utf-8']) ? 'path.utf-8' : 'path';
                        $ff = $this->checkTorrentDict($fn, $path_key, 'array');  // 'list' or you can say 'indexed_array'
                        $this->torrent_size += $ll;
                        $ffa = [];
                        foreach ($ff as $ffe) {
                            if (!is_string($ffe)) {
                                throw new ParseErrorException('std_filename_errors');
                            }
                            $ffa[] = $ffe;
                        }
                        if (!count($ffa)) {
                            throw new ParseErrorException('std_filename_errors');
                        }
                        $this->checkFileName($ffa);
                        $ffe = implode('/', $ffa);
                        $this->torrent_list[] = ['filename' => $ffe, 'size' => $ll];
                    }
                    $this->torrent_type = 'multi';
                }
            }
        } catch (ParseErrorException $e) {
            // FIXME Fix message of ParseErrorException
            $this->buildCallbackFailMsg('Parse', $e->getMessage());
            return;
        }

        $this->torrent_name = $info['name'];
        $this->torrent_structure = $this->getFileTree();
    }

    protected function getFileNameCheckRules()
    {
        if (is_null($this->file_name_check_rules)) {
            $rules = app()->pdo->prepare('SELECT `rules` FROM `file_defender` WHERE `category_id` = 0 OR `category_id` = :cat')->bindParams([
                'cat' => $this->getInput('category')  // Fix cat_id
            ])->queryColumn();
            $this->file_name_check_rules = '/' . implode('|', $rules) . '/iS';
        }

        return $this->file_name_check_rules;
    }

    protected function checkFileName($filenames)
    {
        $filename = end($filenames);  // Only Check filename without path info
        if (preg_match($this->getFileNameCheckRules(), $filename)) {
            throw new ParseErrorException('filename hit defender.');
        }
    }

    /** @noinspection PhpUnused */
    protected function makePrivateTorrent()
    {
        $this->torrent_dict['announce'] = 'https://' . config('base.site_tracker_url') . '/announce';

        // Remove un-need field in private torrents
        unset($this->torrent_dict['announce-list']); // remove multi-tracker capability
        unset($this->torrent_dict['nodes']); // remove cached peers (Bitcomet & Azareus)

        // Rewrite `commit` and `created by` if enabled this config
        if (config('torrent_upload.rewrite_commit_to')) {
            $this->torrent_dict['commit'] = config('torrent_upload.rewrite_commit_to');
        }
        if (config('torrent_upload.rewrite_createdby_to')) {
            $this->torrent_dict['created by'] = config('torrent_upload.rewrite_createdby_to');
        }

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
        $this->torrent_dict['info']['source'] = config('torrent_upload.rewrite_source_to') ?: 'Powered by [' . config('base.site_url') . '] ' . config('base.site_name');

        // Get info_hash on new torrent content dict['info']
        $this->info_hash = pack('H*', sha1(Bencode::encode($this->torrent_dict['info'])));

        // Check if this torrent is exist or not before insert.
        $count = app()->pdo->prepare('SELECT COUNT(*) FROM torrents WHERE info_hash = :info_hash')->bindParams([
            'info_hash' => $this->info_hash
        ])->queryScalar();

        // TODO redirect user to exist torrent details page when this torrent exist.
        if ($count > 0) {
            $this->buildCallbackFailMsg('Torrent', 'std_torrent_existed');
        }
    }

    /**
     * @throws \Exception
     */
    public function flush()
    {
        $nfo_blob = '';
        if (isset($this->nfo)) {  // FIXME it seem always be true ???
            $nfo_blob = $this->nfo->getFileContent();
        }

        $this->rewriteFlags();
        $this->determineTorrentStatus();
        app()->pdo->beginTransaction();
        try {
            $tags = $this->getTags();

            app()->pdo->prepare('INSERT INTO `torrents` (`owner_id`,`info_hash`,`status`,`added_at`,`title`,`subtitle`,`category`,`filename`,`torrent_name`,`torrent_type`,`torrent_size`,`torrent_structure`,`team`,`quality_audio`,`quality_codec`,`quality_medium`,`quality_resolution`,`descr`,`tags`,`nfo`,`uplver`,`hr`)
VALUES (:owner_id, :info_hash, :status, CURRENT_TIMESTAMP, :title, :subtitle, :category, :filename, :torrent_name, :type, :size, :structure,:team,:audio,:codec,:medium,:resolution,:descr, JSON_ARRAY(:tags), :nfo, :uplver, :hr)')->bindParams([
                'owner_id' => app()->auth->getCurUser()->getId(),
                'info_hash' => $this->info_hash,
                'status' => $this->status,
                'title' => $this->title, 'subtitle' => $this->subtitle,
                'category' => $this->category,
                'filename' => $this->file->getClientOriginalName(),
                'torrent_name' => $this->torrent_name, 'type' => $this->torrent_type, 'size' => $this->torrent_size,
                'structure' => $this->torrent_structure, 'tags' => $tags,  // JSON
                'audio' => (int)$this->audio, 'codec' => (int)$this->codec,
                'medium' => (int)$this->medium, 'resolution' => (int)$this->resolution,
                'team' => $this->team,
                'descr' => $this->descr,
                'nfo' => $nfo_blob,
                'uplver' => $this->anonymous, 'hr' => $this->hr
            ])->execute();
            $this->id = app()->pdo->getLastInsertId();

            $this->updateTagsTable($tags);

            $this->getExternalLinkInfo();
            $this->setBuff();

            // Save this torrent
            $dump_status = Bencode::dump(Constant::getTorrentFileLoc($this->id), $this->torrent_dict);
            if ($dump_status === false) {
                throw new \Exception('std_torrent_cannot_save');
            }

            app()->pdo->commit();
        } catch (\Exception $e) {
            // Delete the saved torrent file when torrent save success but still get Exception on other side
            if (isset($dump_status) && $dump_status === true) {
                unlink(Constant::getTorrentFileLoc($this->id));
            }

            app()->pdo->rollback();

            throw $e;
        }

        app()->site->writeLog("Torrent {$this->id} ({$this->title}) was uploaded by " . ($this->anonymous ? 'Anonymous' : app()->auth->getCurUser()->getUsername()));
    }

    // TODO update torrent status based on user class or their owned torrents count
    private function determineTorrentStatus()
    {
        $this->status = TorrentStatus::CONFIRMED;
    }

    // TODO sep to Traits
    private function setTorrentBuff($operator_id = 0, $beneficiary_id = 0, $buff_type = 'mod', $ratio_type = 'Normal', $upload_ratio = 1, $download_ratio = 1)
    {
    }

    // TODO it may take long time to get link details , so when torrent upload, we just push it to task worker
    private function getExternalLinkInfo()
    {
        if ($this->links) {
            app()->redis->lPush('queue:external_link_via_torrent_upload', ['tid' => $this->id, 'links' => $this->links]);
        }
    }

    private function setBuff()
    {
        $operator_id = 0;  // The buff operator id when torrent upload will be system
        // Add Large Buff and Random Buff
        if (config("buff.enable_large") && $this->file->size > config("buff.large_size")) {
            // TODO app()->pdo->createCommand();
        } elseif (config("buff.enable_random")) {
            // TODO app()->pdo->createCommand();
        }

        // TODO set uploader (or you can say torrents owner) buff
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
    private function getFileTree()
    {
        $structure = array_column($this->torrent_list, 'size', 'filename');
        if ($this->torrent_type == TorrentType::MULTI) {
            $structure = [$this->torrent_name => self::makeFileTree($structure)];
        }
        return json_encode($structure);
    }

    private static function makeFileTree(array $array, $delimiter = '/')
    {
        if (!is_array($array)) {
            return [];
        }

        $splitRE = '/' . preg_quote($delimiter, '/') . '/';
        $returnArr = [];
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
        if (!is_array($dict)) {
            throw new ParseErrorException("std_not_a_dictionary");
        }

        $value = $dict[$key];
        if (!isset($value)) {
            throw new ParseErrorException("std_dictionary_is_missing_key");
        }

        if (!is_null($type)) {
            $isFunction = 'is_' . $type;
            if (\function_exists($isFunction) && !$isFunction($value)) {
                throw new ParseErrorException("std_invalid_entry_in_dictionary");
            }
        }
        return $value;
    }
}
