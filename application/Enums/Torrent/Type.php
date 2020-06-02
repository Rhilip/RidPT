<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/2/2020
 * Time: 9:51 AM
 */

declare(strict_types=1);

namespace App\Enums\Torrent;

use Rid\Utils\Enum;

class Type extends Enum
{
    public const SINGLE = 'single';
    public const MULTI = 'multi';
}
