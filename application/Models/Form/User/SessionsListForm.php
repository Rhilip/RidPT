<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/12/2019
 * Time: 2019
 */

namespace App\Models\Form\User;

use Rid\Validators\Pagination;

/**
 * Class SessionsListForm
 * @package App\Models\Form\User
 *
 * @property-read int $uid
 * @property-read array $expired
 */
class SessionsListForm extends Pagination
{
    public $expired = [-1, 0]; // Default not show expired session

    public static $DEFAULT_LIMIT = 10;
    public static $MAX_LIMIT = 50;

    public static function defaultData(): array
    {
        return [
            'page' => static::getDefaultPage(), 'limit' => static::getDefaultLimit(),
            'uid' => app()->auth->getCurUser()->getId()
        ];
    }

    public static function inputRules(): array
    {
        $rules = [
            'expired[*]' => [
                ['Integer'],
                ['Inlist', ['list' => [-1 /* Never Expired */, 0 /* Temporary */, 1 /* Expired */]]]
            ]
        ];

        // TODO allow admin to see other people session log
        $rules['uid'] = ['Integer', ['Equal', ['value' => app()->auth->getCurUser()->getId()]]];

        return $rules;
    }

    protected function getRemoteTotal(): int
    {
        return app()->pdo->prepare([
            ['SELECT COUNT(`id`) FROM sessions WHERE uid = :uid ', 'params' => ['uid' => $this->uid]],
            ['AND `expired` IN (:expired)', 'params' => ['expired' => $this->expired]],
        ])->queryScalar();
    }

    protected function getRemoteData(): array
    {
        return app()->pdo->prepare([
            ['SELECT `id`, session, `login_at`, `login_ip`, `expired` FROM sessions WHERE 1=1 '],
            ['AND `uid` = :uid ', 'params' => ['uid' => $this->uid]],
            ['AND `expired` IN (:expired)', 'params' => ['expired' => $this->expired]],
            ['ORDER BY `expired`, `id` DESC'],
            ['LIMIT :o, :l', 'params' => ['o' => $this->offset, 'l' => $this->limit]]
        ])->queryAll();
    }
}
