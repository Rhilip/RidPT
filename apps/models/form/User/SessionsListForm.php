<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/12/2019
 * Time: 2019
 */

namespace apps\models\form\User;


use Rid\Validators\Pager;

class SessionsListForm extends Pager
{
    public $uid;
    public $expired = [-1, 0]; // Default not show expired session

    public static $DEFAULT_LIMIT = 10;
    public static $MAX_LIMIT = 50;

    protected $_autoload_data = true;
    protected $_autoload_data_from = ['get'];

    public static function defaultData()
    {
        return [
            'page' => static::getDefaultPage(), 'limit' => static::getDefaultLimit(),
            'uid' => app()->auth->getCurUser()->getId()
        ];
    }

    public static function inputRules()
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
        var_dump($this->getData('expired'));
        return app()->pdo->createCommand([
            ['SELECT COUNT(`id`) FROM `user_session_log` WHERE uid = :uid ', 'params' => ['uid' => $this->getData('uid')]],
            ['AND `expired` IN (:expired)', 'params' => ['expired' => $this->getData('expired')]],
        ])->queryScalar();
    }

    protected function getRemoteData(): array
    {
        return app()->pdo->createCommand([
            ['SELECT `id`, `sid`, `login_at`, `login_ip`, `user_agent`, `last_access_at`, `expired` FROM `user_session_log` WHERE 1=1 '],
            ['AND `uid` = :uid ', 'params' => ['uid' => app()->auth->getCurUser()->getId()]],
            ['AND `expired` IN (:expired)', 'params' => ['expired' => $this->expired]],
            ['ORDER BY `expired`, `id` DESC'],
            ['LIMIT :o, :l', 'params' => ['o' => $this->offset, 'l' => $this->limit]]
        ])->queryAll();
    }
}
