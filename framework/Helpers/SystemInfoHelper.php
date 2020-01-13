<?php

namespace Rid\Helpers;

/**
 * PhpInfoHelper
 */
class SystemInfoHelper
{

    /** 是否为 CLI 模式
     * @return bool
     */
    public static function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /** 是否为 Win 系统
     * @return bool
     */
    public static function isWin(): bool
    {
        return stripos(PHP_OS, 'WINNT') !== false;
    }

    /**
     * 是否为 Mac 系统
     * @return bool
     */
    public static function isMac(): bool
    {
        return stripos(PHP_OS, 'Darwin') !== false;
    }

    public static function getAvg(): array
    {
        if (is_readable('/proc/loadavg')) {
            $loadavg = explode(' ', file_get_contents('/proc/loadavg'));
            return array_slice($loadavg, 0, 3);
        }
        return [0, 0, 0];
    }

    public static function getMemory(): array
    {
        if (is_readable('/proc/meminfo')) {
            $content = file_get_contents('/proc/meminfo');
            preg_match('/^MemTotal: \s*(\d*)/m', $content, $matches_total);
            $total = $matches_total[1] * 1024;
            preg_match('/^MemFree: \s*(\d*)/m', $content, $matches_free);
            $free = $matches_free[1] * 1024;
            preg_match('/^MemAvailable: \s*(\d*)/m', $content, $matches_available);
            if ($matches_available) {
                $used = $matches_available[1] * 1024;
            } else {
                $used = $total - $free;
            }
            return ['total' => $total, 'free' => $free, 'used' => $used];
        }
        return ['total' => 0, 'free' => 0, 'used' => 0];
    }

    public static function getUptime(): int
    {
        if (is_readable('/proc/uptime')) {
            return (float)file_get_contents('/proc/uptime');
        }
        return 0;
    }

    public static function getProcessor(): int
    {
        if (is_readable('/proc/cpuinfo')) {
            return (int)substr_count(file_get_contents('/proc/cpuinfo'), 'processor');
        }
        return 0;
    }

    public static function getIdlePercent(): float
    {
        if (is_readable('/proc/cpuinfo') && is_readable('/proc/uptime')) {
            $processors = self::getProcessor();
            if ($processors == 0) {
                return 0;
            }

            [$uptime, $idle] = explode(' ', file_get_contents('/proc/uptime'));
            return (float)$idle / ((float)$uptime * $processors);
        }
        return 0;
    }
}
