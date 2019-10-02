<?php

namespace Rid\Component;

use League\Plates\Extension\ExtensionInterface;
use Rid\Base\Component;

use League\Plates\Engine;

/**
 * Class View
 * @author Rhilip
 */
class View extends Component
{

    /**
     * @var string
     */
    public $templates_path;

    /**
     * @var array
     */
    public $extensions;

    /** @var Engine */
    protected $_templates;

    public function onInitialize()
    {
        $this->_templates = new Engine($this->templates_path);

        // Load Extensions
        foreach ($this->extensions as $extension_name) {
            $extension = new $extension_name();
            if ($extension instanceof ExtensionInterface) {
                $this->_templates->loadExtension($extension);
            }
        }
    }

    public function render($__template__, array $__data__ = null)
    {
        ob_start();
        echo $this->_templates->render($__template__, $__data__);
        return ob_get_clean();
    }
}
