<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/4/2020
 * Time: 5:09 PM
 */

declare(strict_types=1);

namespace App\Forms\Torrents;

use App\Enums\Torrent\Status;
use App\Forms\Traits\isValidTorrentTrait;
use Rid\Utils\Arr;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class EditForm extends AbstractValidator
{
    use isValidTorrentTrait;

    public function __construct()
    {
        $this->setInput([
            'nfo_action' => 'keep'
        ]);
    }

    protected function loadInputMetadata(): Assert\Collection
    {
        $rules = $this->loadBaseTorrentInputMetadata();
        $rules['id'] = new AcmeAssert\PositiveInt();

        if (config('torrent_upload.enable_upload_nfo')   // Enable nfo upload
            && container()->get('auth')->getCurUser()->isPrivilege('upload_nfo_file') // This user can upload nfo
        ) {
            $rules['nfo_action'] = new Assert\Choice(['keep', 'remove', 'update']);
            if ($this->getInput('nfo_action') == 'update') { // Nfo file upload
                $rules['nfo'] = new Assert\File([
                    'mimeTypes' => ['text/plain' /* .txt */, 'text/x-nfo' /* .nfo */],
                    'maxSize' => config('upload.max_nfo_file_size')
                ]);
            } else {
                $rules['nfo'] = new Assert\Optional(new Assert\IsNull());
            }
        }

        if (container()->get('auth')->getCurUser()->isPrivilege('manage_torrents')) {
            $rules['status'] = new Assert\Choice(Status::values());
        }

        return new Assert\Collection($rules);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isExistTorrent', 'checkUserPermission'];
    }

    public function flush(): void
    {
        $nfo_action = $this->getInput('nfo_action', 'keep');
        if ($nfo_action == 'remove') {
            $has_nfo = false;
        } elseif ($nfo_action == 'update') {
            $has_nfo = true;
        } else {
            $has_nfo = $this->getTorrent()->hasNfo();
        }

        $tags = $this->getTags();

        container()->get('dbal')->prepare('
            UPDATE `torrents` SET title = :title, subtitle = :subtitle,
                                  category = :category, team = :team,
                                  quality_audio = :audio, quality_codec = :codec,
                                  quality_medium = :medium, quality_resolution = :resolution,
                                  descr = :descr, tags = :tags,
                                  has_nfo=:nfo,uplver = :anonymous, hr = :hr
            WHERE id = :id')->bindParams([
                'tags' => json_encode($tags), 'nfo' => (int)$has_nfo,
            ] + Arr::only($this->getInput(), [
                'id', 'title', 'subtitle',
                'category', 'team',
                'audio', 'codec', 'medium', 'resolution',
                'descr',
                'anonymous', 'hr'
            ]))->execute();

        if ($nfo_action == 'update') {
            $this->insertNfo($this->getInput('id'));
        }

        $this->updateTagsTable($tags);
    }

    /** @noinspection PhpUnused */
    protected function checkUserPermission()
    {
        if (container()->get('auth')->getCurUser()->getId() != $this->torrent->getOwnerId()  // User is torrent owner
            || !container()->get('auth')->getCurUser()->isPrivilege('manage_torrents')  // User can manager torrents
        ) {
            $this->buildCallbackFailMsg('owner', 'You can\'t edit torrent which is not belong to you.');
        }
    }

    protected function insertNfo($tid)
    {
        if ($this->getInput('nfo') instanceof UploadedFile) {
            /** @var UploadedFile $nfo */
            $nfo = $this->getInput('nfo');
            container()->get('dbal')->prepare('INSERT INTO `torrent_nfos` (tid, nfo) VALUES (:tid, :nfo) ON DUPLICATE KEY UPDATE nfo = VALUES(`nfo`)')->bindParams([
                'tid' => $tid, 'nfo' => file_get_contents($nfo->getPathname())
            ])->execute();
        }
    }

    protected function loadBaseTorrentInputMetadata(): array
    {
        $rules = [
            'title' => new Assert\NotBlank(),
            'subtitle' => new Assert\Length(['allowEmptyString' => true, 'max' => 255]),
            'category' => new Assert\Choice(array_map(
                function ($cat) {
                    return (string)$cat['id'];
                },
                container()->get('site')->ruleCanUsedCategory()
            )),
            'descr' => new Assert\NotBlank(),
        ];

        // Add Quality Valid
        foreach (container()->get('site')->getQualityTableList() as $quality => $title) {
            // IF enabled this quality field , then load it value list from setting
            // Else we just allow the default value 0 to prevent cheating
            if (config('torrent_upload.enable_quality_' . $quality)) {
                $quality_id_list = ['0'];
                foreach (container()->get('site')->ruleQuality($quality) as $cat) {
                    $quality_id_list[] = (string)$cat['id'];
                }
                $rules[$quality] = new Assert\Choice($quality_id_list);
            } else {
                $rules[$quality] = new Assert\Optional(new Assert\EqualTo(0));
            }
        }

        // Same reason as quality
        if (config('torrent_upload.enable_teams')) {
            $team_id_list = ['0'];
            foreach (container()->get('site')->ruleTeam() as $team) {
                if (container()->get('auth')->getCurUser()->getClass() >= $team['class_require']) {
                    $team_id_list[] = (string)$team['id'];
                }
            }
            $rules['team'] = new Assert\Choice($team_id_list);
        } else {
            $rules['team'] = new Assert\Optional(new Assert\EqualTo(0));
        }

        // Add Flag Valid
        foreach (['anonymous', 'hr'] as $flag) {
            $config = config('torrent_upload.enable_' . $flag);

            if ($config == 2) {  // if global config force enabled this flag
                $this->setInput([$flag => 1]);
            } elseif ($config == 0) { // if global config disabled this flag
                $this->setInput([$flag => 0]);
            } else {  // check if user can use this flag
                if (!container()->get('auth')->getCurUser()->isPrivilege('upload_flag_' . $flag)) {
                    $rules[$flag] = new Assert\Optional(new Assert\IsFalse());
                } else {
                    $rules[$flag] = new Assert\Optional(new Assert\AtLeastOneOf([
                        new Assert\IsTrue(), new Assert\IsFalse()
                    ]));
                }
            }
        }

        return $rules;
    }

    protected function getTags(): array
    {
        $tags_list = [];
        if (config('torrent_upload.enable_tags')) {
            $tags = str_replace(',', ' ', $this->getInput('tags'));
            $tags_list = explode(' ', $tags);
            $tags_list = array_slice($tags_list, 0, 10); // Get first 10 tags

            if (!config('torrent_upload.allow_new_custom_tags')) {
                $rule_pinned_tags = array_keys(container()->get('site')->rulePinnedTags());
                $tags_list = array_intersect($rule_pinned_tags, $tags_list);
            }
        }

        return $tags_list;
    }

    protected function updateTagsTable(array $tags)
    {
        foreach ($tags as $tag) {
            container()->get('dbal')->prepare('INSERT INTO tags (tag) VALUES (:tag) ON DUPLICATE KEY UPDATE `count` = `count` + 1;')->bindParams([
                'tag' => $tag
            ])->execute();
        }
    }

    public function getTorrentId(): int
    {
        return (int)$this->getInput('id');
    }
}
