<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/6
 * Time: 19:46
 */

namespace Mix\Facades;

use Mix\Base\Facade;

/**
 * Class SwiftMailer
 * @package apps\common\facades
 *
 * @method send(array $receiver, string $subject, string $body) static
 */
class SwiftMailer extends Facade
{
    // 获取实例
    public static function getInstance()
    {
        return app()->swiftmailer;
    }
}
