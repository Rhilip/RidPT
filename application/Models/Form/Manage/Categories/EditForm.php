<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/16/2019
 * Time: 4:13 PM
 */

namespace App\Models\Form\Manage\Categories;

use Rid\Validators\Validator;

class EditForm extends Validator
{
    public $cat_id;
    public $cat_parent_id;
    public $cat_name;
    public $cat_enabled = 0;
    public $cat_image;
    public $cat_class_name;

    private $cat_new_data;
    private $cat_old_data;
    private $cat_data_diff;

    public static function inputRules(): array
    {
        return [
            'cat_id' => 'Required | Integer',
            'cat_parent_id' => 'Required | Integer',
            'cat_name' => 'Required | AlphaNumHyphen',
            'cat_enabled' => 'Integer',
            'cat_image' => [
                ['Regex', ['pattern' => '/^[a-z0-9_.\/]*$/']]
            ],
            'cat_class_name' => [
                ['Regex', ['pattern' => '/^[a-z][a-z0-9_\-]*?$/']]
            ],
        ];
    }

    public static function callbackRules(): array
    {
        return ['checkCategoryData'];
    }

    /** @noinspection PhpUnused */
    protected function checkCategoryData()
    {
        $cat_parent_id = (int)$this->getInput('cat_parent_id');
        $this->cat_new_data = [
            'parent_id' => $cat_parent_id,
            'name' => $this->getInput('cat_name'), 'enabled' => $this->getInput('cat_enabled'),
            'image' => $this->getInput('cat_image'), 'class_name' => $this->getInput('cat_class_name')
        ];

        // Generate New Full Path Key
        $parent_cat_fpath = app()->pdo->prepare('SELECT `full_path` FROM `categories` WHERE `id` = :pid')->bindParams([
            'pid' => $cat_parent_id
        ])->queryScalar();
        if ($parent_cat_fpath === false) {
            $this->buildCallbackFailMsg('Category:parent', 'The parent category can\'t found.');
            return;
        }

        if ($cat_parent_id == 0) {
            $full_path = $this->getInput('cat_name');
        } else {
            $full_path = $parent_cat_fpath . ' - ' . $this->getInput('cat_name');
        }

        $this->cat_new_data['full_path'] = $full_path;
        $this->cat_new_data['level'] = substr_count($full_path, ' - ');
        $flag_check_full_path = true;

        $cat_id = (int)$this->getInput('cat_id');
        if ($cat_id !== 0) {  // Check if old links should be update
            $this->cat_old_data = app()->pdo->prepare('SELECT * FROM `categories` WHERE id = :id')->bindParams([
                'id' => $cat_id
            ])->queryOne();
            if ($this->cat_old_data === false) {
                $this->buildCallbackFailMsg('Category:exist', 'the link data not found in our database');
                return;
            }
            $this->cat_new_data['id'] = $cat_id;

            // Diff old and new data.
            $this->cat_data_diff = array_diff_assoc($this->cat_new_data, $this->cat_old_data);
            if (count($this->cat_data_diff) === 0) {
                $this->buildCallbackFailMsg('Category:update', 'No data update');
                return;
            }
            if (!isset($this->cat_data_diff['full_path'])) {
                $flag_check_full_path = false;
            }  // It means full path key not update, We shouldn't check anymore.
        }

        if ($flag_check_full_path) {  // Check if full path key is duplicate or not.
            $check_full_path = app()->pdo->prepare('SELECT COUNT(`id`) FROM `categories` WHERE `full_path` = :fpath')->bindParams([
                'fpath' => $full_path
            ])->queryScalar();
            if ($check_full_path > 0) {
                $this->buildCallbackFailMsg('Category:duplicate', 'This Path is already exist.');
                return;
            }
        }
    }

    public function flush()
    {
        if ((int)$this->cat_id !== 0) {  // to edit exist cat
            app()->pdo->update('categories', $this->cat_data_diff, [['id', '=', $this->cat_id]])->execute();
            if ($this->cat_parent_id !== 0) {  // Disabled post to parent cat
                app()->pdo->prepare('UPDATE `categories` SET `enabled` = 0 WHERE `id` = :pid')->bindParams([
                    'pid' => $this->cat_parent_id
                ])->execute();
            }
            // TODO Add site log
        } else {  // to new a cat
            app()->pdo->insert('categories', $this->cat_new_data)->execute();
            // TODO Add site log
        }

        // flush Redis Cache
        app()->redis->del('site:enabled_torrent_category');
    }
}
