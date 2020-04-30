<?php

namespace Rid\Component;

use League\Plates\Engine;

/**
 * Class View
 * @author Rhilip
 */
class View
{

    /**
     * @var Engine
     */
    private Engine $engine;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    public function render($__template__, array $__data__ = null)
    {
        ob_start();
        echo $this->engine->render($__template__, $__data__);
        return ob_get_clean();
    }
}
