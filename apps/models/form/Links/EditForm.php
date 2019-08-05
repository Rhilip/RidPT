<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/16
 * Time: 10:23
 */

namespace apps\models\form\Links;


class EditForm extends ApplyForm
{
    public $link_id;
    public $link_status;

    private $link_new_data;
    private $link_old_data;
    private $link_data_diff;

    public static function defaultData()
    {
        return [
            'link_title' => '',
        ];
    }

    public static function inputRules()
    {
        return [
            'link_id' => 'Required | Integer',
            'link_name' => 'Required',
            'link_url' => 'Required | Url',
            'link_status' => [
                'Required',
                ['InList', ['list' => [self::STATUS_PENDING, self::STATUS_ENABLED, self::STATUS_DISABLED]], 'Unknown link status.']
            ],
            'link_email' => 'Email',
        ];
    }

    public static function callbackRules()
    {
        return ['checkLinkData'];
    }

    protected function checkLinkData()
    {
        $this->link_new_data = [
            'name' => $this->getData('link_name'), 'url' => $this->getData('link_url'),
            'title' => $this->getData('link_title'), 'status' => $this->getData('link_status'),
            'administrator' => $this->getData('link_status'), 'email' => $this->getData('link_email'),
            'reason' => $this->getData('link_reason')
        ];
        $link_id = (int) $this->getData('link_id');
        if ($link_id !== 0) {  // Check if old links should be update
            $this->link_old_data = app()->pdo->createCommand('SELECT * FROM `links` WHERE id = :id')->bindParams([
                'id' => $link_id
            ])->queryOne();
            if (false === $this->link_old_data) {
                $this->buildCallbackFailMsg('links', 'the link data not found in our database');
                return;
            }
            $this->link_new_data['id'] = $link_id;

            // Diff old and new data.
            $this->link_data_diff = array_diff_assoc($this->link_new_data, $this->link_old_data);
            if (count($this->link_data_diff) === 0) {
                $this->buildCallbackFailMsg('links:update', 'No data update');
            }
        } else {
            $this->checkExistLinksByUrl();
        }
    }

    public function flush()
    {
        if ((int) $this->link_id !== 0) {  // to edit exist links
            app()->pdo->update('links', $this->link_data_diff, [['id', '=', $this->link_id]])->execute();
            app()->site->writeLog('The links data of ' . $this->link_old_data['name'] . '( ' . $this->link_old_data['url'] . ' ) is update by ' .
                app()->site->getCurUser()->getUsername() . '(' . app()->site->getCurUser()->getId() . ').', app()->site::LOG_LEVEL_MOD);
        } else {  // to new a links
            app()->pdo->insert('links', $this->link_new_data)->execute();
           app()->site->writeLog('The links data of ' . $this->link_new_data['name'] . '( ' . $this->link_new_data['url'] . ' ) is update by ' .
                app()->site->getCurUser()->getUsername() . '(' . app()->site->getCurUser()->getId() . ').', app()->site::LOG_LEVEL_MOD);
        }
        app()->redis->del('Site:links');
    }
}
