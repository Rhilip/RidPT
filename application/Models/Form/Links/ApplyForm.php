<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/15
 * Time: 20:49
 */

namespace App\Models\Form\Links;

use Rid\Validators\CaptchaTrait;
use Rid\Validators\Validator;

class ApplyForm extends Validator
{
    use CaptchaTrait;

    public $link_name;
    public $link_url;
    public $link_title;
    public $link_admin;
    public $link_email;
    public $link_reason;

    const STATUS_PENDING = 'pending';
    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';

    public static function inputRules(): array
    {
        return [
            'link_name' => 'Required',
            'link_url' => 'Required | Url',
            'link_admin' => 'Required',
            'link_email' => 'Required | Email',
            'link_reason' => 'Required'
        ];
    }

    public static function callbackRules(): array
    {
        return ['validateCaptcha', 'checkExistLinksByUrl'];
    }

    protected function checkExistLinksByUrl()
    {
        $count = app()->pdo->prepare('SELECT COUNT(`id`) FROM `links` WHERE url = :url')->bindParams([
            'url' => $this->getInput('link_url')
        ])->queryScalar();
        if ($count > 0) {
            $this->buildCallbackFailMsg('Link:exist', 'This link is exist in our site , Please don\'t report it again and again');
        }
    }

    public function flush()
    {
        app()->pdo->prepare('INSERT INTO `links`(`name`, `url`, `title`, `status`, `administrator`, `email`, `reason`) VALUES (:name,:url,:title,:status,:admin,:email,:reason)')->bindParams([
            'name' => $this->link_name, 'url' => $this->link_url, 'title' => $this->link_title,
            'status' => self::STATUS_PENDING, 'admin' => $this->link_admin, 'email' => $this->link_email,
            'reason' => $this->link_reason
        ])->execute();
        app()->redis->del('Site:links');
        // TODO Send system PM to site group
    }
}
