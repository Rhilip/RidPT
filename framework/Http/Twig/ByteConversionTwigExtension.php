<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/30
 * Time: 23:34
 */

namespace Mix\Http\Twig;


class ByteConversionTwigExtension extends \Twig_Extension
{


    /**
     * Gets filters
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('format_bytes', array($this, 'formatBytes')),
        );
    }

    public function getName()
    {
        return 'format_bytes';
    }

    function formatBytes($bytes, $precision = 2)
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
