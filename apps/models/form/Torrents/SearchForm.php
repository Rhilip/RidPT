<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 9:10 PM
 */

namespace apps\models\form\Torrents;

use Rid\Validators\Pager;

class SearchForm extends Pager
{

    public static $MAX_LIMIT = 100;

    private $_tags;

    public static function defaultData(): array
    {
        return [
            'search_mode' => 0,  // AND
            'search_area' => 0,  // title and subtitle
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

            $rules[$quality. '[*]'] = [
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

            if (is_string($tags)) $tags = explode(',', $tags);
            $this->_tags = array_map('trim', $tags);
        }

        return $this->_tags;
    }

    private function getSearchField(): array
    {
        $fields = [];

        // Quality
        foreach (app()->site->getQualityTableList() as $quality => $title) {
            if (config('torrent_upload.enable_quality_' . $quality)) {
                $value = $this->getInput($quality);
                if (is_array($value)) $fields[] = ["AND `quality_$quality` IN (:quality) ", 'params' => ['quality' => array_map('intval', $value)]];
            }
        }

        // Teams
        if (config('torrent_upload.enable_teams')) {
            $value = $this->getInput('team');
            if (is_array($value)) $fields[] = ['AND `team` IN (:team) ', 'params' => ['team' => array_map('intval', $value)]];
        }

        // TODO ADD search area support
        $searchstr = $this->getInput('search');
        if (!is_null($searchstr)) {
            $search_area = $this->getInput('search_area');
            if ($search_area == 3) {  // TODO Search in owner_id

            } elseif (false) {}  // TODO imdb or douban
            else {  // Search in title and subtitle
                $search_col = ($search_area == 1) ? '(`descr`)' : '(`title`, `subtitle`)';

                $searchstr = str_replace(str_split('.+-_*"()<>~'), ' ', $searchstr);
                $searchstr_exploded_raw = array_map('trim', explode(' ', $searchstr));

                $searchstr_exploded_count= 0;
                $searchstr_exploded = [];
                foreach ($searchstr_exploded_raw as $value) {
                    if (strlen($value) > 0) {
                        $searchstr_exploded_count++;
                        $searchstr_exploded[] = $value;
                    }
                    if ($searchstr_exploded_count > 10) break;
                }

                $search_mode = $this->getInput('search_mode');
                if ($search_mode == 2) {  // exact
                    $search = '"' . implode(' ', $searchstr_exploded) . '"';
                } else {
                    if ($search_mode == 1) {  // AND
                        $searchstr_exploded = array_map(function ($x) {
                            return '+' . $x;
                        }, $searchstr_exploded);
                    }
                    $search = implode(' ', $searchstr_exploded);
                }

                $fields[] = ["AND MATCH $search_col AGAINST (:search IN BOOLEAN MODE)", 'params' => ['search' => $search]];
            }
        }

        // Tags
        $tags = $this->getTagsArray();
        $fields[] = ['AND JSON_CONTAINS(`tags`, JSON_ARRAY(:tags)) ', 'if' => count($tags), 'params' => ['tags' => $tags]];



        return $fields;
    }

    protected function getRemoteTotal(): int
    {
        return app()->pdo->createCommand(array_merge([
            ['SELECT COUNT(`id`) FROM `torrents` WHERE 1=1 ']
        ], $this->getSearchField()))->queryScalar();
    }

    protected function getRemoteData(): array
    {

        $fetch = app()->pdo->createCommand(array_merge(array_merge([
            ['SELECT `id`, `added_at` FROM `torrents` WHERE 1=1 ']
        ], $this->getSearchField()),[
            ['ORDER BY `added_at` DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => $this->offset, 'rows' => $this->limit]]
        ]))->queryColumn();

        return array_map(function ($id) {
            return app()->site->getTorrent($id);
        }, $fetch);
    }
}
