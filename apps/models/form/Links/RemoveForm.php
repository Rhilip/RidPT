<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/16
 * Time: 10:23
 */

namespace apps\models\form\Links;


use Rid\Validators\Validator;

class RemoveForm extends Validator
{
    public $link_id;

    public static function inputRules()
    {
        return [
            'link_id' => 'Required | Integer',
        ];
    }

    public static function callbackRules()
    {
        return ['checkExistLinksById'];
    }

    protected function checkExistLinksById()
    {
        $count = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `links` WHERE id = :id')->bindParams([
            'id' => $this->getData('link_id')
        ])->queryScalar();
        if ($count == 0) {
            $this->buildCallbackFailMsg('Link:exist', 'This link is exist in our site , Please don\'t report it again and again');
        }
    }

    public function flush()
    {
        app()->pdo->createCommand('DELETE FROM `links` WHERE id = :id')->bindParams([
            'id' => $this->link_id
        ])->execute();
        app()->redis->del('Site:links');
    }
}
