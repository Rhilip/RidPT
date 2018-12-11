<?php

namespace mix\http;

use Twig_Environment;
use Twig_Loader_Filesystem;

/**
 * Class View
 * @author Rhilip
 */
class View
{
    public function render($__template__, $__data__)
    {
        $loader = new Twig_Loader_Filesystem(\Mix::app()->getViewPath());
        $twig = new Twig_Environment($loader, array(
            'cache' => \Mix::app()->getRuntimePath() . DIRECTORY_SEPARATOR . "view",
        ));

        ob_start();
        echo $twig->render($__template__, $__data__);
        return ob_get_clean();
    }
}
