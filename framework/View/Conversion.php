<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 16:55
 */

namespace Rid\View;

use Decoda\Decoda;
use Decoda\Storage\RedisStorage;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class Conversion implements ExtensionInterface
{
    public function register(Engine $engine)
    {
        $engine->registerFunction('format_bytes', [$this, 'format_bytes']);
        $engine->registerFunction('format_bytes_compact', [$this, 'format_bytes_compact']);
        $engine->registerFunction('format_bytes_loose', [$this, 'format_bytes_loose']);
        $engine->registerFunction('format_ubbcode', [$this, 'format_ubbcode']);
        $engine->registerFunction('sec2hms', [$this, 'sec2hms']);
    }

    public function format_bytes($bytes, $precision = 2, $separator = ' ')
    {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = max(min($pow, count($units) - 1), 0);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . $separator . $units[$pow];
    }

    public function format_bytes_compact($var)
    {
        return $this->format_bytes($var, 2, '<br />');
    }

    public function format_bytes_loose($var)
    {
        return $this->format_bytes($var, 2, '&nbsp;');
    }

    public function format_ubbcode($string)
    {
        $code = new Decoda($string, [
            'escapeHtml' => true
        ], 'Decoda:' . md5($string));

        $code->defaults(); // TODO add support of tag [mediainfo]

        $code->setStorage(new RedisStorage(app()->redis->getRedis()));
        return $code->parse();
    }

    public function sec2hms($sec, $padHours = false)
    {
        $hms = '';

        $hours = intval(intval($sec) / 3600);
        $minutes = intval(($sec / 60) % 60);
        $seconds = intval($sec % 60);

        $hms .= ($padHours)
            ? str_pad($hours, 2, '0', STR_PAD_LEFT) . ':'
            : $hours . ':';

        $hms .= str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':';
        $hms .= str_pad($seconds, 2, '0', STR_PAD_LEFT);

        return $hms;
    }
}
