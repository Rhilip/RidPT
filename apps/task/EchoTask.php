<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/17
 * Time: 13:14
 */

namespace apps\task;

use Rid\Base\TaskInterface;

class EchoTask implements TaskInterface
{
    public function run($data)
    {
        println(print_r($data['data']));
    }
}
