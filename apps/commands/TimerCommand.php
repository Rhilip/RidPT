<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/30
 * Time: 15:28
 */

namespace apps\commands;

use Mix\Console\Command;
use Mix\Base\Timer;

/**
 * 定时器范例
 * @author LIUJIAN <coder.keda@gmail.com>
 */
class TimerCommand extends Command
{
    /**
     * 主函数
     */
    public function actionExec()
    {
        // 一次性定时
        Timer::new()->after(1000, function () {
            var_dump(time());
        });
        // 持续定时
        $timer = new Timer();
        $timer->tick(1000, function () {
            var_dump(time());
        });
        // 停止定时
        Timer::new()->after(10000, function () use ($timer) {
            $timer->clear();
        });
    }
}
