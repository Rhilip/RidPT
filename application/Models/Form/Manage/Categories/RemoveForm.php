<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/16/2019
 * Time: 4:13 PM
 */

namespace App\Models\Form\Manage\Categories;

use Rid\Validators\Validator;

/**
 * Class RemoveForm
 * @package App\Models\Form\Manage\Categories
 * @property-read int $cat_id
 */
class RemoveForm extends Validator
{
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
        $this->category_data = \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('SELECT * FROM `categories` WHERE id = :id')->bindParams([
            'id' => $this->cat_id
        ])->queryScalar();
        if ($this->category_data === false) {
            $this->buildCallbackFailMsg('Categories:exist', 'This category isn\'t exist in our site.');
        }
    }

    /** @noinspection PhpUnused */
    protected function checkChildNode()
    {
        $this->child_count = \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('SELECT COUNT(`id`) FROM `categories` WHERE `parent_id` = :pid')->bindParams([
            'pid' => $this->cat_id
        ])->queryScalar();
        if ($this->child_count !== 0) {
            $this->buildCallbackFailMsg('Categories:child', 'This category has sub category exist, Please clean subcategory first.');
        }
    }

    public function flush()
    {
        // FIXME Move Category's torrent from this to it's parent
        \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('UPDATE `torrents` SET `category` = :new WHERE `category` = :old ')->bindParams([
            'new' => $this->category_data['parent_id'], 'old' => $this->category_data['id']
        ])->execute();

        // Delete it~
        \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('DELETE FROM `categories` WHERE id = :id')->bindParams([
            'id' => $this->cat_id
        ])->execute();

        // Enabled parent category if no siblings
        $siblings_count = \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('SELECT COUNT(`id`) FROM `categories` WHERE `parent_id` = :pid')->bindParams([
            'pid' => $this->category_data['parent_id']
        ])->queryScalar();

        if ($siblings_count == 0) {
            \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('UPDATE `categories` SET `enabled` = 1 WHERE `id` = :id')->bindParams([
                'id' => $this->category_data['parent_id']
            ])->execute();
        }

        // TODO flush Redis cache
        \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->del('site:enabled_torrent_category');
    }
}
