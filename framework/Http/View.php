<?php

namespace Mix\Http;

use Mix\Facades\Config;
use Twig_Loader_Filesystem;
use Twig_Environment;

use Twig_Extensions_Extension_Array;
use Twig_Extensions_Extension_Date;
use Twig_Extensions_Extension_I18n;
use Twig_Extensions_Extension_Intl;
use Twig_Extensions_Extension_Text;


/**
 * Class View
 * @author Rhilip
 */
class View
{

    public $twig;

    public function __construct()
    {
        $loader = new Twig_Loader_Filesystem(\Mix::app()->getViewPath());
        $this->twig = new Twig_Environment($loader, array(
            'debug' => env("APP_DEBUG"),
            'cache' => \Mix::app()->getRuntimePath() . DIRECTORY_SEPARATOR . "view",
        ));

        $this->twig->addExtension(new Twig_Extensions_Extension_Text());
        $this->twig->addExtension(new Twig_Extensions_Extension_I18n());
        $this->twig->addExtension(new Twig_Extensions_Extension_Intl());
        $this->twig->addExtension(new Twig_Extensions_Extension_Array());
        $this->twig->addExtension(new Twig_Extensions_Extension_Date());

        $this->twig->addGlobal("config", Config::getInstance());

        if ($user = \Mix\Facades\Session::get("userInfo"))
            $this->twig->addGlobal('user',$user);
    }

    public function render($__template__, $__data__)
    {
        ob_start();
        echo $this->twig->render($__template__, $__data__);
        return ob_get_clean();
    }
}
