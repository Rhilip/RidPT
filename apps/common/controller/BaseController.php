<?php
/**
 * Created by PhpStorm.
 * User: Rhili
 * Date: 2018/11/22
 * Time: 15:52
 */

namespace apps\common\controller;

use mix\http\Controller;

class BaseController extends Controller
{
    /** @var \mix\http\Request */
    public $Request;

    /** @var \mix\http\Response */
    public $Response;

    /** @var \mix\client\Redis */
    public $Redis;

    /** @var \mix\client\PDO */
    public $Database;

    /** @var \apps\common\components\ConfigLoadComponent */
    public $Config;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->loadProvider();
    }

    private function loadProvider()
    {
        $this->Request = app()->request;
        $this->Response = app()->response;
        $this->Redis = app()->redis;
        $this->Database = app()->pdo;
        $this->Config = app()->config;
    }
}
