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

    protected $templates;

    public function __construct($url = true)
    {
        $this->templates = new Engine(app()->getViewPath());
        if ($url) $this->templates->loadExtension(new URI(app()->request->server('path_info')));
        $this->templates->loadExtension(new \Rid\View\Conversion());
    }

    public function render($__template__, array $__data__ = null)
    {
        ob_start();
        echo $this->templates->render($__template__, $__data__);
        return ob_get_clean();
    }
}
