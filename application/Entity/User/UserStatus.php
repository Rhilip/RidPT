<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 1/11/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Entity\User;

class UserStatus
{
    // User Status
    public const DISABLED = 'disabled';
    public const PENDING = 'pending';
    public const PARKED = 'parked';
    public const CONFIRMED = 'confirmed';
}
