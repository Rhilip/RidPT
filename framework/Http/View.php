<?php

namespace Rid\Http;

use League\Plates\Engine;
use League\Plates\Extension\URI;

/**
 * Class View
 * @author Rhilip
 */
class View
{

    public $twig;
    protected $templates;

    public function __construct()
    {
        $this->templates = new Engine(app()->getViewPath());
        $this->templates->loadExtension(new URI(app()->request->server('path_info')));
        $this->templates->loadExtension(new \Rid\View\ByteConversion());
    }

    public function render($__template__, $__data__)
    {
        ob_start();
        echo $this->templates->render($__template__, $__data__);
        return ob_get_clean();
    }
}
