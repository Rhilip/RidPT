<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 11:15 PM
 */

declare(strict_types=1);

namespace App\Enums\Torrent;

use Rid\Utils\Enum;

class Status extends Enum
{
    public const DELETED = 'deleted';
    public const BANNED = 'banned';
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
}
