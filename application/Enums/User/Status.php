<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/2/2020
 * Time: 9:54 AM
 */

declare(strict_types=1);

namespace App\Enums\User;

class Status
{
    public const DISABLED = 'disabled';
    public const PENDING = 'pending';
    public const PARKED = 'parked';
    public const CONFIRMED = 'confirmed';
}
