<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/16/2019
 * Time: 4:13 PM
 */

namespace apps\models\form\Manage\Categories;


use Rid\Validators\Validator;

class RemoveForm extends Validator
{
    public $cat_id;

    private $category_data;

    public static function inputRules()
    {
        return [
            'cat_id' => 'Required | Integer',
        ];
    }

    public static function callbackRules()
    {
        return ['getExistCategoryData', 'checkChildNode'];
    }

    protected function getExistCategoryData()
    {
        $this->category_data = app()->pdo->createCommand('SELECT * FROM `torrents_categories` WHERE id = :id')->bindParams([
            'id' => $this->cat_id
        ])->queryScalar();
        if ($this->category_data === false) {
            $this->buildCallbackFailMsg('Categories:exist', 'This category isn\'t exist in our site.');
        }
    }

    protected function checkChildNode()
    {
        $child_chount = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `torrents_categories` WHERE `parent_id` = :pid')->bindParams([
            'pid' => $this->cat_id
        ])->queryScalar();
        if ($child_chount !== 0) {
            $this->buildCallbackFailMsg('Categories;child', 'This category has sub category exist, Please clean subcategory first.');
        }
    }

    public function flush()
    {
        // FIXME Move Category's torrent from this to it's parent
        app()->pdo->createCommand('UPDATE `torrents` SET `category` = :new WHERE `category` = :old ')->bindParams([
            'new' => $this->category_data['parent_id'], 'old' => $this->category_data['id']
        ])->execute();

        // Delete it~
        app()->pdo->createCommand('DELETE FROM `torrents_categories` WHERE id = :id')->bindParams([
            'id' => $this->cat_id
        ])->execute();
        // TODO flush Redis cache
    }
}
