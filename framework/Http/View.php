<?php

namespace Rid\Http;

use Rid\Base\BaseObject;

use League\Plates\Engine;

/**
 * Class View
 * @author Rhilip
 */
class View extends BaseObject
{

    /** @var Engine */
    protected $_templates;

    public function onConstruct()
    {
        $this->_templates = new Engine(app()->getViewPath());
        $this->_templates->loadExtension(new \Rid\View\Conversion());
    }

    public function render($__template__, array $__data__ = null)
    {
        ob_start();
        echo $this->_templates->render($__template__, $__data__);
        return ob_get_clean();
    }
}
