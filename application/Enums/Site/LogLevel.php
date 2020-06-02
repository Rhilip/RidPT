<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Enums\Site;

use Rid\Utils\Enum;

class LogLevel extends Enum
{
    public const NORMAL = 'normal';
    public const MOD = 'mod';
    public const SYSOP = 'sysop';
    public const LEADER = 'leader';
}
