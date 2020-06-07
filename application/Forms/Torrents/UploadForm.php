<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/4/2020
 * Time: 1:51 PM
 */

declare(strict_types=1);

namespace App\Forms\Torrents;

use App\Enums\Torrent\Status as TorrentStatus;
use App\Enums\Torrent\Type as TorrentType;
use Rhilip\Bencode\Bencode;
use Rhilip\Bencode\ParseErrorException;
use Rid\Utils\Arr;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UploadForm extends EditForm
{
    private int $id = 0;

    private ?string $torrent_name;    // the $torrent_dict['info']['name'] field
    private ?array $torrent_dict;
    private ?array $torrent_structure;  // Array created by $this->getFileTree()
    private ?string $torrent_type = TorrentType::SINGLE; // only in ['single','multi']
    private ?int $torrent_size = 0;  // the count of torrent's content size
    private ?string $info_hash;

    protected function loadInputMetadata(): Assert\Collection
    {
        $rules = $this->loadBaseTorrentInputMetadata();
        $rules['file'] = new AcmeAssert\File([
            'extension' => 'torrent', // 'application/x-bittorrent'
            'maxSize' => config('upload.max_torrent_file_size'),
        ]);

        if (config('torrent_upload.enable_upload_nfo') &&  // Enable nfo upload
            container()->get('auth')->getCurUser()->isPrivilege('upload_nfo_file')   // This user can upload nfo
        ) {
            $rules['nfo'] = new Assert\AtLeastOneOf([
                new Assert\IsNull(),  // User not update Nfo file
                new Assert\File([
                    'mimeTypes' => ['text/plain' /* .txt */, 'text/x-nfo' /* .nfo */],
                    'maxSize' => config('upload.max_nfo_file_size')
                ])
            ]);
        }

        return new Assert\Collection($rules);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['checkUploadPos', 'isValidTorrentFile', 'makePrivateTorrent'];
    }

    public function flush(): void
    {
        container()->get('pdo')->beginTransaction();
        try {
            /** @var UploadedFile $ori_file */
            $ori_file = $this->getInput('file');
            $status = $this->getStatus();
            $tags = $this->getTags();

            // Insert then get id
            container()->get('pdo')->prepare('
                INSERT INTO `torrents` (`owner_id`, `info_hash`, `status`, `added_at`, `title`, `subtitle`, `category`,
                                        `filename`, `torrent_name`, `torrent_type`, `torrent_size`,
                                        `team`, `quality_audio`, `quality_codec`, `quality_medium`, `quality_resolution`,
                                        `descr`, `tags`, `has_nfo`,
                                        `uplver`, `hr`)
                 VALUES (:owner_id, :info_hash, :status, CURRENT_TIMESTAMP, :title, :subtitle, :category,
                         :filename, :torrent_name, :type, :size,
                         :team, :audio, :codec, :medium, :resolution,
                         :descr, :tags, :nfo, :anonymous, :hr)')->bindParams([
                    'owner_id' => container()->get('auth')->getCurUser()->getId(),
                    'info_hash' => $this->info_hash,
                    'status' => $status,
                    'filename' => $ori_file->getClientOriginalName(),
                    'torrent_name' => $this->torrent_name, 'type' => $this->torrent_type, 'size' => $this->torrent_size,
                    'tags' => json_encode($tags),  // JSON
                    'nfo' => (int)!is_null($this->getInput('nfo')),
                ] + Arr::only($this->getInput(), [
                    'title', 'subtitle', 'category',
                    'audio', 'codec', 'medium', 'resolution', 'team',
                    'descr',
                    'anonymous', 'hr'
                ]))->execute();
            $id = (int)container()->get('pdo')->getLastInsertId();

            // Save this torrent
            $save_to = container()->get('path.storage.torrents') . DIRECTORY_SEPARATOR . $id . '.torrent';
            $dump_status = Bencode::dump($save_to, $this->torrent_dict);
            if ($dump_status === false) {
                throw new \Exception('std_torrent_cannot_save');
            }

            // Update Other Torrent Table
            $this->insertStructure($id);
            $this->insertNfo($id);
            // TODO getExternalLinkInfo()
            // TODO setTorrentBuff()

            $this->updateTagsTable($tags);

            container()->get('pdo')->commit();
            $this->id = $id;
        } catch (\Exception $exception) {
            // Delete the saved torrent file when torrent save success but still get Exception on other side
            if (isset($dump_status) && $dump_status === true) {
                unlink(container()->get('path.storage.torrents') . DIRECTORY_SEPARATOR . $this->id . '.torrent');
            }

            container()->get('pdo')->rollback();

            throw $exception;
        } finally {
            if ($this->id > 0) {
                container()->get('site')->writeLog("Torrent {$this->id} ({$this->getInput('title')}) was uploaded by " . ($this->getInput('anonymous') ? 'Anonymous' : container()->get('auth')->getCurUser()->getUsername()));
            }
        }
    }



    protected function insertStructure($tid)
    {
        container()->get('pdo')->prepare('INSERT INTO `torrent_structures` (tid, structure) VALUES (:tid, :structure)')->bindParams([
            'tid' => $tid, 'structure' => json_encode($this->torrent_structure)
        ])->execute();
    }

    // FIXME torrent status by user's status
    protected function getStatus()
    {
        return TorrentStatus::CONFIRMED;
    }

    /** @noinspection PhpUnused */
    protected function checkUploadPos()
    {
        if (!container()->get('auth')->getCurUser()->getUploadpos()) {
            $this->buildCallbackFailMsg('pos', 'your upload pos is disabled');
        }
    }

    /** @noinspection PhpUnused */
    protected function isValidTorrentFile()
    {
        try {
            $torrent_dict = Bencode::load($this->getInput('file')->getPathname());

            $info = $this->checkTorrentDict($torrent_dict, 'info');
            $plen = $this->checkTorrentDict($info, 'piece length', 'integer');  // Only Check without use
            $dname = $this->checkTorrentDict($info, 'name', 'string');
            $pieces = $this->checkTorrentDict($info, 'pieces', 'string');

            if (strlen($pieces) % 20 != 0) {
                throw new ParseErrorException('std_invalid_pieces');
            }

            $torrent_list = [];
            if (isset($info['length'])) {
                $torrent_type = 'single';
                $torrent_size = $info['length'];
                $torrent_list[] = ['filepath' => $dname, 'filename' => $dname, 'size' => $info['length']];
            } else {
                $torrent_type = 'multi';
                $f_list = $this->checkTorrentDict($info, 'files', 'array');
                if (!isset($f_list)) {
                    throw new ParseErrorException('std_missing_length_and_files');
                }
                if (!count($f_list)) {
                    throw new ParseErrorException('no files');
                }

                $torrent_size = 0;
                foreach ($f_list as $fn) {
                    $ll = $this->checkTorrentDict($fn, 'length', 'integer');
                    $path_key = isset($fn['path.utf-8']) ? 'path.utf-8' : 'path';
                    $ff = $this->checkTorrentDict($fn, $path_key, 'array');  // 'list' or you can say 'indexed_array'
                    $torrent_size += $ll;
                    $ffa = [];
                    foreach ($ff as $ffe) {
                        if (!is_string($ffe)) {
                            throw new ParseErrorException('std_filename_errors');
                        } elseif (false !== strpos($ffe, ['<', '>', ':', '"', '/', '\\', '|', '?', '*'])) {
                            /**
                             * Not allow reserved characters in part of directory or file name
                             * @see https://stackoverflow.com/a/31976060/8824471
                             */
                            throw new ParseErrorException('std_filename_errors');
                        }
                        $ffa[] = $ffe;
                    }
                    if (!count($ffa)) {
                        throw new ParseErrorException('std_filename_errors');
                    }

                    $ffe = implode('/', $ffa);
                    $torrent_list[] = ['filepath' => $ffe, 'filename' => end($ffa), 'size' => $ll];
                }
            }

            // Check If torrent name valid
            $check_rule = $this->getFilenameCheckRule();  // FIXME
            foreach ($torrent_list as $value) {
                if (preg_match($check_rule, $value['filename'])) {
                    throw new ParseErrorException('filename hit defender.');
                }
            }

            // Store truth value in Form Object
            $this->torrent_name = $dname;
            $this->torrent_dict = $torrent_dict;
            $this->torrent_structure = $this->makeTorrentStructure($torrent_list, $torrent_type, $dname);
            $this->torrent_type = $torrent_type;
            $this->torrent_size = $torrent_size;
        } catch (ParseErrorException $e) {
            // FIXME Fix message of ParseErrorException
            $this->buildCallbackFailMsg('Parse', $e->getMessage());
            return;
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
        $info_hash = pack('H*', sha1(Bencode::encode($this->torrent_dict['info'])));

        // Check if this torrent is exist or not before insert.
        $id = container()->get('pdo')->prepare('SELECT id FROM torrents WHERE info_hash = :info_hash')->bindParams([
            'info_hash' => $info_hash
        ])->queryScalar();

        // TODO redirect user to exist torrent details page when this torrent exist.
        if ($id !== false) {
            $this->id = (int)$id;
            $this->buildCallbackFailMsg('Torrent', 'std_torrent_existed');
        }

        $this->info_hash = $info_hash;
    }

    private function getFilenameCheckRule()
    {
        return '/sdfghjktrejkjhfdehjhfdsdfgh/i';  // FIXME
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
     * @param $file_list
     * @param $torrent_type
     * @param $torrent_name
     * @return array
     */
    private function makeTorrentStructure(array $file_list, string $torrent_type, string $torrent_name)
    {
        $structure = array_column($file_list, 'size', 'filename');
        if ($torrent_type == TorrentType::MULTI) {
            $structure = [$torrent_name => self::makeFileTree($structure)];
        }
        return $structure;
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

    public function getId(): int
    {
        return $this->id;
    }
}
