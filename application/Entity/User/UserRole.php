<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 1/11/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Entity\User;

class UserRole
{
    // Anonymous Visitor
    public const ANONYMOUS = -1;

    // User class
    public const PEASANT = 0;
    public const USER = 1;
    public const POWER_USER = 2;
    public const ELITE_USER = 3;
    public const CRAZY_USER = 4;
    public const INSANE_USER = 5;
    public const VETERAN_USER = 6;
    public const EXTREME_USER = 7;
    public const ULTIMATE_USER = 8;
    public const MASTER_USER = 9;  # The max level that user can reached if they reached the level setting
    public const TEMP_VIP = 10;    # The max level that user can reached via bonus exchange

    // Contributor class
    public const VIP = 20;
    public const HONORARY = 25;
    public const RETIREE = 30;

    // Uploader class
    public const UPLOADER = 40;
    public const HELPER = 50;

    // Administrator class
    public const FORUM_MODERATOR = 60;
    public const MODERATOR = 70;
    public const ADMINISTRATOR = 80;
    public const SYSOP = 90;
    public const STAFFLEADER = 100;

    public const ROLE = [
        self::PEASANT => 'PEASANT',
        self::USER => 'USER',
        self::POWER_USER => 'POWER_USER',
        self::ELITE_USER => 'ELITE_USER',
        self::CRAZY_USER => 'CRAZY_USER',
        self::INSANE_USER => 'INSANE_USER',
        self::VETERAN_USER => 'VETERAN_USER',
        self::EXTREME_USER => 'EXTREME_USER',
        self::ULTIMATE_USER => 'ULTIMATE_USER',
        self::MASTER_USER => 'MASTER_USER',
        self::TEMP_VIP => 'TEMP_VIP',

        self::VIP => 'VIP',
        self::HONORARY => 'HONORARY',
        self::RETIREE => 'RETIREE',

        self::UPLOADER => 'UPLOADER',
        self::HELPER => 'HELPER',

        self::FORUM_MODERATOR => 'FORUM_MODERATOR',
        self::MODERATOR => 'MODERATOR',
        self::ADMINISTRATOR => 'ADMINISTRATOR',
        self::SYSOP => 'SYSOP',
        self::STAFFLEADER => 'STAFFLEADER'
    ];
}
