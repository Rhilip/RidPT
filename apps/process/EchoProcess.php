<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/28/2019
 * Time: 10:22 PM
 */

namespace apps\process;


use Rid\Base\Process;

class EchoProcess extends Process
{
    public function start()
    {
        println(time() . config('base.site_name'));
        sleep(5);
    }
}
