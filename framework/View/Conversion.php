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

    protected const UBBCODE_MAP = [
        ' ' => '&ensp;',
    ];

    public function register(Engine $engine)
    {
        $engine->registerFunction('format_bytes', [$this, 'format_bytes']);
        $engine->registerFunction('format_bytes_compact', [$this, 'format_bytes_compact']);
        $engine->registerFunction('format_bytes_loose', [$this, 'format_bytes_loose']);
        $engine->registerFunction('format_ubbcode', [$this, 'format_ubbcode']);
    }

    public static function setDefault(&$array, $defaults)
    {
        if (!is_array($array)) $array = [$array];
        foreach ($defaults as  $key => $default) {
            if (!array_key_exists($key, $array)) {
                $array[$key] = $default;
            }
        }
    }

    public function format_bytes($var)
    {
        self::setDefault($var, ['precision' => 2, 'separator' => ' ']);
        $bytes = array_shift($var);

        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);

        return round($bytes, $var['precision']) . $var['separator'] . $units[$pow];
    }

    public function format_bytes_compact($var)
    {
        self::setDefault($var, ['precision' => 2, 'separator' => '<br />']);
        return $this->format_bytes($var);
    }

    public function format_bytes_loose($var)
    {
        self::setDefault($var, ['precision' => 2, 'separator' => '&nbsp;']);
        return $this->format_bytes($var);
    }

    public function format_ubbcode($var)
    {
        self::setDefault($var, ['escapeHtml' => true]);
        $string = array_shift($var);

        $code = new Decoda($string, $var,'Post_cache:' . md5($string));

        $code->defaults(); // TODO add support of tag [mediainfo] , [nfo]

        $code->setStorage(new RedisStorage(app()->redis->getRedis()));
        return $code->parse();
    }
}
