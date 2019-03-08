<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 16:55
 */

namespace Rid\View;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class ByteConversion implements ExtensionInterface
{

    public function register(Engine $engine)
    {
        $engine->registerFunction('format_bytes', [$this, 'format_bytes']);
    }

    public function format_bytes($bytes, $precision = 2)
    {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
