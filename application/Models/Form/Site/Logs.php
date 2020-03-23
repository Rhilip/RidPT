<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/17/2019
 * Time: 2019
 */

namespace App\Models\Form\Site;

use Rid\Validators\Pagination;

/**
 * Class Logs
 * @package App\Models\Form\Site
 *
 * @property-read string $query
 * @property-read string $level
 */
class Logs extends Pagination
{
    protected $_levels;

    public static $MAX_LIMIT = 100;

    public static function defaultData(): array
    {
        return [
            'page' => static::getDefaultPage(),
            'limit' => static::getDefaultLimit(),
            'level' => 'all'
        ];
    }

    public static function inputRules(): array
    {
        $level_list = ['all', 'normal'];
        if (app()->auth->getCurUser()->isPrivilege('see_site_log_mod')) {
            $level_list[] = 'mod';
        }
        if (app()->auth->getCurUser()->isPrivilege('see_site_log_leader')) {
            $level_list[] = 'leader';
        }

        return [
            'page' => 'Integer', 'limit' => 'Integer',
            'level' => [
                ['RequiredWith', ['item' => 'query']],
                ['inList', ['list' => $level_list]]
            ]
        ];
    }

    private function getLevels()
    {
        if (!is_null($this->_levels)) {
            return $this->_levels;
        }

        if ('all' == $input_level = $this->level) {
            $levels = ['normal'];
            if (app()->auth->getCurUser()->isPrivilege('see_site_log_mod')) {
                $levels[] = 'mod';
            }
            if (app()->auth->getCurUser()->isPrivilege('see_site_log_leader')) {
                $levels[] = 'leader';
            }
        } else {
            $levels = [$input_level];
        }

        $this->_levels = $levels;
        return $this->_levels;
    }

    protected function getRemoteTotal(): int
    {
        $search = $this->query;
        return app()->pdo->prepare([
            ['SELECT COUNT(*) FROM `site_log` WHERE 1=1 '],
            ['AND `level` IN (:l) ', 'params' => ['l' => $this->getLevels()]],
            ['AND `msg` LIKE :search ', 'if' => strlen($search), 'params' => ['search' => "%$search%"]]
        ])->queryScalar();
    }

    protected function getRemoteData(): array
    {
        $search = $this->query;
        return app()->pdo->prepare([
            ['SELECT * FROM `site_log` WHERE 1=1 '],
            ['AND `level` IN (:l) ', 'params' => ['l' => $this->getLevels()]],
            ['AND `msg` LIKE :search ', 'if' => strlen($search), 'params' => ['search' => "%$search%"]],
            ['ORDER BY create_at DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => $this->offset, 'rows' => $this->limit]]
        ])->queryAll();
    }
}
