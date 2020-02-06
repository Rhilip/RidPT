<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2/6/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Entity\Site;


class LogLevel
{
    const LOG_LEVEL_NORMAL = 'normal';
    const LOG_LEVEL_MOD = 'mod';
    const LOG_LEVEL_SYSOP = 'sysop';
    const LOG_LEVEL_LEADER = 'leader';
}
