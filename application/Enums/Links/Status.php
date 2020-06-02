<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Enums\Links;

use Rid\Utils\Enum;

class Status extends Enum
{
    public const PENDING = 'pending';
    public const ENABLED = 'enabled';
    public const DISABLED = 'disabled';
}
