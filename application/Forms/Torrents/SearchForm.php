<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 6:32 PM
 */

declare(strict_types=1);

namespace App\Forms\Torrents;

use App\Entity\Torrent\TorrentFactory;
use App\Forms\Traits\PaginationTrait;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class SearchForm extends AbstractValidator
{
    use PaginationTrait;

    public function __construct()
    {
        $this->setInput([
            'page' => 1, 'limit' => 50,
            'search_mode' => 0,  // 0 - AND "Each words should appear"; 1 - OR "One of words appear"; 2 - exact
            'search_area' => 0,  // 0 - Search in `title` and `subtitle` ; 1 - Search in `descr`
        ]);
    }

    protected function loadInputMetadata(): Assert\Collection
    {
        $rules = [
            'page' => new AcmeAssert\PositiveOrZeroInt(),
            'limit' => new AcmeAssert\RangeInt(['min' => 0, 'max' => 100]),
        ];

        // Quality
        foreach (container()->get('site')->getQualityTableList() as $quality => $title) {
            $quality_id_list = [];
            if (config('torrent_upload.enable_quality_' . $quality)) {
                $quality_id_list = [0] + array_map(function ($cat) {
                    return $cat['id'];
                }, container()->get('site')->ruleQuality($quality));
            }

            $rules[$quality] = new Assert\Optional(new AcmeAssert\looseChoice([
                'choices' => $quality_id_list,
                'multiple' => true
            ]));
        }

        // Teams
        if (config('torrent_upload.enable_teams')) {
            $team_id_list = array_map(function ($team) {
                return $team['id'];
            }, container()->get('site')->ruleTeam());
            $rules['team'] = new Assert\Optional(new AcmeAssert\looseChoice([
                'choices' => $team_id_list,
                'multiple' => true
            ]));
        }

        if ($this->hasInput('search')) {
            $rules['search_mode'] = new AcmeAssert\looseChoice([0 /* AND */, 1 /* OR */, 2 /* exact */]);
            $rules['search_area'] = new AcmeAssert\looseChoice([0 /* `title` and `subtitle` */, 1 /* `descr` */]);
        }

        if ($this->hasInput('tags')) {
            $rules['tags'] = new Assert\Type('array');
        }

        return new Assert\Collection($rules);
    }

    protected function loadCallbackMetaData(): array
    {
        return [];
    }

    public function flush()
    {
        $fields = [];

        // Quality
        foreach (container()->get('site')->getQualityTableList() as $quality => $title) {
            if (config('torrent_upload.enable_quality_' . $quality) &&
                $this->hasInput($quality)
            ) {
                $fields[] = ["AND `quality_$quality` IN (:quality) ", 'params' => [
                    'quality' => (array)$this->getInput($quality)
                ]];
            }
        }

        // Teams
        if (config('torrent_upload.enable_teams') && $this->hasInput('team')) {
            $fields[] = ['AND `team` IN (:team) ', 'params' => [
                'team' => (array) $this->getInput('team')
            ]];
        }

        $favour = $this->getInput('favour');
        if ($favour == 1) {  // bookmarked in favour
            $fields[] = ['AND `id` IN (SELECT `tid` FROM `bookmarks` WHERE `uid` = :uid)', 'params' => ['uid' => container()->get('auth')->getCurUser()->getId()]];
        } elseif ($favour == 2) {  // not bookmarked in favour
            $fields[] = ['AND `id` NOT IN (SELECT `tid` FROM `bookmarks` WHERE `uid` = :uid)', 'params' => ['uid' => container()->get('auth')->getCurUser()->getId()]];
        }

        // TODO we may not use `&search_area=` to search in non-title/subtitle/descr field, Use sep `&ownerid=` , `&doubanid=` instead.
        if ($this->hasInput('search')) {
            $searchstr = $this->getInput('search');
            $search_mode = $this->getInput('search_mode');
            $ANDOR = ($search_mode == 0 ? 'AND' : 'OR');    // only affects mode 0 and mode 1
            $searchstr_exploded = [];

            if ($search_mode == 2) {  // exact
                $ANDOR = 'AND';
                $searchstr_exploded[] = trim($searchstr);
            } else {
                $searchstr = str_replace(str_split('.'), ' ', $searchstr);
                $searchstr_exploded_raw = array_map('trim', explode(' ', $searchstr));

                $searchstr_exploded_count = 0;
                foreach ($searchstr_exploded_raw as $value) {
                    if (strlen($value) > 2) {
                        $searchstr_exploded_count++;
                        $searchstr_exploded[] = $value;
                    }
                    if ($searchstr_exploded_count > 10) {
                        break;
                    }
                }
            }

            $search_area = $this->getInput('search_area');
            foreach ($searchstr_exploded as $i => $item) {
                if ($search_area == 0) {
                    $fields[] = ["$ANDOR (`title` LIKE :title_$i OR `subtitle` LIKE :subtitle_$i) ", 'params' => ["title_$i" => "%$item%", "subtitle_$i" => "%$item%"]];
                } else {
                    $fields[] = ["$ANDOR `descr` LIKE :descr_$i ", 'params' => ["descr_$i" => "%$item%"]];
                }
            }
        }

        if ($this->hasInput('tags')) {
            $tags = $this->getInput('tags');
            $fields[] = ['AND JSON_CONTAINS(`tags`, JSON_ARRAY(:tags)) ', 'if' => count($tags), 'params' => ['tags' => $tags]];
        }


        $count = container()->get('pdo')->prepare([
            ['SELECT COUNT(`id`) FROM `torrents` WHERE 1=1 '],
            ...$fields
        ])->queryScalar();
        $this->setPaginationTotal($count);

        $this->setPaginationLimit($this->getInput('limit'));
        $this->setPaginationPage($this->getInput('page'));

        $data = container()->get(TorrentFactory::class)->getTorrentBySearch($fields, $this->getPaginationOffset(), $this->getPaginationLimit());
        $this->setPaginationData($data);
    }
}
