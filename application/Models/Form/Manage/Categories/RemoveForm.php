<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/16/2019
 * Time: 4:13 PM
 */

namespace App\Models\Form\Manage\Categories;

use Rid\Validators\Validator;

class RemoveForm extends Validator
{
    public $cat_id;

    private $category_data;
    private $child_count;

    public static function inputRules(): array
    {
        return [
            'cat_id' => 'Required | Integer',
        ];
    }

    public static function callbackRules(): array
    {
        return ['getExistCategoryData', 'checkChildNode'];
    }

    /** @noinspection PhpUnused */
    protected function getExistCategoryData()
    {
        $this->category_data = app()->pdo->prepare('SELECT * FROM `categories` WHERE id = :id')->bindParams([
            'id' => $this->getInput('cat_id')
        ])->queryScalar();
        if ($this->category_data === false) {
            $this->buildCallbackFailMsg('Categories:exist', 'This category isn\'t exist in our site.');
        }
    }

    /** @noinspection PhpUnused */
    protected function checkChildNode()
    {
        $this->child_count = app()->pdo->prepare('SELECT COUNT(`id`) FROM `categories` WHERE `parent_id` = :pid')->bindParams([
            'pid' => $this->getInput('cat_id')
        ])->queryScalar();
        if ($this->child_count !== 0) {
            $this->buildCallbackFailMsg('Categories;child', 'This category has sub category exist, Please clean subcategory first.');
        }
    }

    public function flush()
    {
        // FIXME Move Category's torrent from this to it's parent
        app()->pdo->prepare('UPDATE `torrents` SET `category` = :new WHERE `category` = :old ')->bindParams([
            'new' => $this->category_data['parent_id'], 'old' => $this->category_data['id']
        ])->execute();

        // Delete it~
        app()->pdo->prepare('DELETE FROM `categories` WHERE id = :id')->bindParams([
            'id' => $this->cat_id
        ])->execute();

        // Enabled parent category if no siblings
        $siblings_count = app()->pdo->prepare('SELECT COUNT(`id`) FROM `categories` WHERE `parent_id` = :pid')->bindParams([
            'pid' => $this->category_data['parent_id']
        ])->queryScalar();

        if ($siblings_count == 0) {
            app()->pdo->prepare('UPDATE `categories` SET `enabled` = 1 WHERE `id` = :id')->bindParams([
                'id' => $this->category_data['parent_id']
            ])->execute();
        }


        // TODO flush Redis cache
        app()->redis->del('site:enabled_torrent_category');
    }
}
