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

    protected $_config;

    public function start() {

    }

    final public function run($config) {
        $this->_config = $config;

        while (true) {
            $this->start();
            sleep($this->_config['sleep']);
        }
    }
}
