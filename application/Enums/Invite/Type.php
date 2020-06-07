<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 11:24 PM
 */

declare(strict_types=1);

namespace App\Enums\Invite;

use Rid\Utils\Enum;

class Type extends Enum
{
    public const TEMPORARILY = 'temporarily';
    public const PERMANENT = 'permanent';
}
