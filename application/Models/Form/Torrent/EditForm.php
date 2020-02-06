<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 10/2/2019
 * Time: 2019
 */

namespace App\Models\Form\Torrent;

use App\Libraries\Constant;
use App\Models\Form\Traits\isValidTorrentTrait;
use App\Entity\Torrent\Torrent;

use App\Entity\Torrent\TorrentStatus;
use Rid\Http\UploadFile;
use Rid\Validators\Validator;

class EditForm extends Validator
{
    use isValidTorrentTrait;

    public $category;
    public $title;
    public $subtitle = '';
    public $links;
    public $descr;

    /**
     * The upload file class of Nfo
     *
     * @var UploadFile
     */
    public $nfo;

    public $anonymous = 0;  // If user upload this torrent Anonymous
    public $hr = 0;  // If This torrent require hr check

    // Quality
    public $audio = 0; /* 0 is default value. */
    public $codec = 0;
    public $medium = 0;
    public $resolution = 0;

    public $team = 0;

    public $tags;

    /**
     * @return array
     */
    public static function baseTorrentRules(): array
    {
        $rules = [
            'title' => 'required',
            'category' => [
                ['required'], ['Integer'],
                ['InList', ['list' => array_map(
                    function ($cat) {
                        return $cat['id'];
                    },
                    app()->site->ruleCanUsedCategory()
                )]]
            ],
            'descr' => 'required',
        ];

        // Add Quality Valid
        foreach (app()->site->getQualityTableList() as $quality => $title) {
            $quality_id_list = [0];
            // IF enabled this quality field , then load it value list from setting
            // Else we just allow the default value 0 to prevent cheating
            if (config('torrent_upload.enable_quality_' . $quality)) {
                foreach (app()->site->ruleQuality($quality) as $cat) {
                    $quality_id_list[] = $cat['id'];
                }
            }

            $rules[$quality] = [
                ['Integer'],
                ['InList', ['list' => $quality_id_list]]
            ];
        }

        // Add Team id Valid
        $team_id_list = [0];
        if (config('torrent_upload.enable_teams')) {
            foreach (app()->site->ruleTeam() as $team) {
                if (app()->auth->getCurUser()->getClass() >= $team['class_require']) {
                    $team_id_list[] = $team['id'];
                }
            }
        }

        $rules['team'] = [
            ['Integer'],
            ['InList', ['list' => $team_id_list]]
        ];

        // Add Flag Valid
        // Notice: we don't valid if user have privilege to use this value,
        // Un privilege flag will be rewrite in rewriteFlags() when call flush()
        if (config('torrent_upload.enable_anonymous')) {
            $rules['uplver'] = [
                ['InList', ['list' => [0, 1]]]
            ];
        }
        if (config('torrent_upload.enable_hr')) {
            $rules['hr'] = [
                ['InList', ['list' => [0, 1]]]
            ];
        }

        return $rules;
    }

    /**
     * @return array
     */
    public static function inputRules(): array
    {
        $rules = static::baseTorrentRules();
        $rules['id'] = 'Required | Integer';

        if (config('torrent_upload.enable_upload_nfo')   // Enable nfo upload
            && app()->auth->getCurUser()->isPrivilege('upload_nfo_file') // This user can upload nfo
        ) {
            $rules['nfo_action'] = [
                ['required'],
                ['InList', ['list' => ['keep', 'remove', 'update']]]
            ];

            // Nfo file upload
            if (app()->request->request->get('nfo_action', 'keep') == 'update') {
                $rules['nfo'] = [
                    ['Upload\Extension', ['allowed' => ['nfo', 'txt']]],
                    ['Upload\Size', ['size' => config('upload.max_nfo_file_size') . 'B']]
                ];
            }
        }

        if (app()->auth->getCurUser()->isPrivilege('manage_torrents')) {
            $rules['status'] = [
                ['required'],
                ['InList', ['list' => TorrentStatus::TORRENT_STATUSES]]
            ];
        }

        return $rules;
    }

    public static function callbackRules(): array
    {
        return ['isExistTorrent', 'checkUserPermission'];
    }

    public function checkUserPermission()
    {
        // Get Torrent if not in validate
        if ($this->torrent === null) {
            $this->isExistTorrent();
            if ($this->getError()) {
                return false;
            }
        }

        if (app()->auth->getCurUser()->getId() != $this->torrent->getOwnerId()  // User is torrent owner
            || !app()->auth->getCurUser()->isPrivilege('manage_torrents')  // User can manager torrents
        ) {
            $this->buildCallbackFailMsg('owner', 'You can\'t edit torrent which is not belong to you.');
            return false;
        }

        // TODO Check Other Permission and store in class pro

        return true;
    }

    public function flush()
    {
        $this->rewriteFlags();
        $tags = $this->getTags();
        app()->pdo->prepare('
            UPDATE `torrents` SET title = :title, subtitle = :subtitle,
                      category = :category, team = :team, quality_audio = :audio, quality_codec = :codec,
                      quality_medium = :medium, quality_resolution = :resolution,
                      descr = :descr, tags =  JSON_ARRAY(:tags), nfo=:nfo,uplver = :uplver, hr = :hr
            WHERE id = :tid')->bindParams([
            'tid' => $this->id,
            'title' => $this->title, 'subtitle' => $this->subtitle,
            'category' => $this->category, 'team' => $this->team,
            'audio' => $this->audio, 'codec' => $this->codec, 'medium' => $this->medium, 'resolution' => $this->resolution,
            'descr' => $this->descr, 'tags' => $tags, 'nfo' => $this->getInputNfo(),
            'uplver' => $this->anonymous, 'hr' => $this->hr
        ])->execute();

        app()->redis->del(Constant::torrentContent($this->id));
        // Delete cache
    }

    private function getInputNfo()
    {
        $action = $this->getInput('nfo_action', 'keep');
        if ($action == 'remove') {
            return '';
        } elseif ($action == 'update') {
            return $this->nfo->getFileContent();
        } else {
            return $this->torrent->getNfo(false);
        }
    }

    // Check and rewrite torrent flags based on site config and user's privilege of upload flags
    protected function rewriteFlags()
    {
        foreach (['anonymous', 'hr'] as $flag) {
            $config = config('torrent_upload.enable_' . $flag);
            if ($config == 2) {  // if global config force enabled this flag
                $this->$flag = 1;
            } elseif ($config == 0) { // if global config disabled this flag
                $this->$flag = 0;
            } else {  // check if user can use this flag
                if (!app()->auth->getCurUser()->isPrivilege('upload_flag_' . $flag)) {
                    $this->$flag = 0;
                }
            }
        }
    }

    protected function getTags(): array
    {
        $tags_list = [];
        if (config('torrent_upload.enable_tags')) {
            $tags = str_replace(',', ' ', $this->tags);
            $tags_list = explode(' ', $tags);
            $tags_list = array_slice($tags_list, 0, 10); // Get first 10 tags

            if (!config('torrent_upload.allow_new_custom_tags')) {
                $rule_pinned_tags = array_keys(app()->site->rulePinnedTags());
                $tags_list = array_intersect($rule_pinned_tags, $tags_list);
            }
        }

        return $tags_list;
    }

    protected function updateTagsTable(array $tags)
    {
        foreach ($tags as $tag) {
            app()->pdo->prepare('INSERT INTO tags (tag) VALUES (:tag) ON DUPLICATE KEY UPDATE `count` = `count` + 1;')->bindParams([
                'tag' => $tag
            ])->execute();
        }
    }
}
