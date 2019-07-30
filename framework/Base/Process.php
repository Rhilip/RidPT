<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/28/2019
 * Time: 10:09 PM
 */

namespace Rid\Base;


class Process implements StaticInstanceInterface
{

    use StaticInstanceTrait;

    private $sleep_time;
    protected $_config;

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

        println('New Custom process `' . static::class . '` added.');

        while (true) {
            $this->run();
            sleep($this->getSleepTime());
        }
    }
}
