<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 9:10 PM
 */

namespace App\Models\Form\Torrents;

use Rid\Validators\Pagination;

class SearchForm extends Pagination
{
    public static $MAX_LIMIT = 100;

    private $_tags;
    private $_field;

    public static function defaultData(): array
    {
        return [
            'search_mode' => 0,  // 0 - AND "Each words should appear"; 1 - OR "One of words appear"; 2 - exact
            'search_area' => 0,  // 0 - Search in `title` and `subtitle` ; 1 - Search in `descr`
        ];
    }

    public static function inputRules(): array
    {
        $rules = ['page' => 'Integer', 'limit' => 'Integer'];

        // Quality
        foreach (app()->site->getQualityTableList() as $quality => $title) {
            $quality_id_list = [];
            if (config('torrent_upload.enable_quality_' . $quality)) {
                $quality_id_list = [0] + array_map(function ($cat) {
                    return $cat['id'];
                }, app()->site->ruleQuality($quality));
            }

            $rules[$quality . '[*]'] = [
                ['Integer'],
                ['InList', ['list' => $quality_id_list]]
            ];
        }

        // Teams
        if (config('torrent_upload.enable_teams')) {
            $team_id_list = array_map(function ($team) {
                return $team['id'];
            }, app()->site->ruleTeam());
            $rules['team[*]'] = [
                ['Integer'],
                ['InList', ['list' => $team_id_list]]
            ];
        }

        // Search key
        $rules['search_mode'] = [
            ['RequiredWith', ['item' => 'search']],
            ['inList', ['list' => [0 /* AND */, 1 /* OR */, 2 /* exact */]]]
        ];

        $rules['search_area'] = [
            ['RequiredWith', ['item' => 'search']],
            ['inList', ['list' => [0 /* `title` and `subtitle` */, 1 /* `descr` */]]]
        ];

        return $rules;
    }

    /**
     * user input may '&tags=<tag1>,<tag2>' (string)
     *             or '&tags[]=<tag1>&tags[]=<tag2>' (array)
     * We deal those with an `AND` operation -> 'AND JSON_CONTAINS(`tags`, JSON_ARRAY(:tags))'
     *
     */
    private function getTagsArray()
    {
        if (is_null($this->_tags)) {
            $tags = $this->getInput('tags') ?? [];

            if (is_string($tags)) {
                $tags = explode(',', $tags);
            }
            $this->_tags = array_map('trim', $tags);
        }

        return $this->_tags;
    }

    private function getSearchField(): array
    {
        if (!is_null($this->_field)) {
            return $this->_field;
        }  // return cached search field

        $fields = [];

        // Quality
        foreach (app()->site->getQualityTableList() as $quality => $title) {
            if (config('torrent_upload.enable_quality_' . $quality)) {
                $value = $this->getInput($quality);
                if (is_array($value)) {
                    $fields[] = ["AND `quality_$quality` IN (:quality) ", 'params' => ['quality' => array_map('intval', $value)]];
                }
            }
        }

        // Teams
        if (config('torrent_upload.enable_teams')) {
            $value = $this->getInput('team');
            if (is_array($value)) {
                $fields[] = ['AND `team` IN (:team) ', 'params' => ['team' => array_map('intval', $value)]];
            }
        }

        // TODO we may not use `&search_area=` to search in non-title/subtitle/descr field, Use sep `&ownerid=` , `&doubanid=` instead.

        $searchstr = $this->getInput('search');
        if (!is_null($searchstr)) {
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

        // Tags
        $tags = $this->getTagsArray();
        $fields[] = ['AND JSON_CONTAINS(`tags`, JSON_ARRAY(:tags)) ', 'if' => count($tags), 'params' => ['tags' => $tags]];

        // Cache fields and return
        $this->_field = $fields;
        return $this->_field;
    }

    protected function getRemoteTotal(): int
    {
        return app()->pdo->prepare(array_merge([
            ['SELECT COUNT(`id`) FROM `torrents` WHERE 1=1 ']
        ], $this->getSearchField()))->queryScalar();
    }

    protected function getRemoteData(): array
    {
        return app()->site->getTorrentFactory()->getTorrentBySearch($this->getSearchField(), $this->offset, $this->limit);
    }
}
