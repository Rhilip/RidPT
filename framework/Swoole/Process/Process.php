<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/28/2019
 * Time: 10:09 PM
 */

namespace Rid\Swoole\Process;

use Rid\Helpers\IoHelper;

class Process
{
    private $sleep_time;
    protected $_config;

    public function init()
    {
    }

    public function run()
    {
    }

    /**
     * @return mixed
     */
    protected function getSleepTime()
    {
        return $this->sleep_time;
    }

    /**
     * @param mixed $sleep_time
     */
    protected function setSleepTime($sleep_time): void
    {
        $this->sleep_time = $sleep_time;
    }

    protected function resetSleepTime()
    {
        $this->setSleepTime($this->_config['sleep']);
    }

    final public function start($config)
    {
        $this->_config = $config;
        $this->resetSleepTime();

        IoHelper::getIo()->text('New Custom process `' . static::class . '` added.');

        $this->init();
        while (true) {
            $this->run();
            sleep($this->getSleepTime());
        }
    }
}
