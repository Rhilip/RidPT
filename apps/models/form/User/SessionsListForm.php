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
    protected $_autoload_data = true;
    protected $_autoload_data_from = ['get'];

    protected function getRemoteTotal(): int
    {
        return app()->pdo->createCommand('SELECT COUNT(`id`) FROM `user_session_log` WHERE uid = :uid')->bindParams([
            'uid' => app()->auth->getCurUser()->getId()
        ])->queryScalar();
    }

    protected function getRemoteData(): array
    {
        return app()->pdo->createCommand([
            ['SELECT `id`, `sid`, `login_at`, `login_ip`, `user_agent`, `last_access_at` FROM `user_session_log` WHERE 1=1 '],
            ['AND uid = :uid ' , 'params' => ['uid' => app()->auth->getCurUser()->getId()]],
            ['ORDER BY `expired` DESC, `id` DESC'],
            ['LIMIT :o, :l', 'params' => ['o' => $this->offset, 'l' => $this->limit]]
        ])->queryAll();
    }
}
