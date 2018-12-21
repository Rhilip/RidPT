<?php

namespace apps\httpd\models;

use mix\Facades\PDO;

class User
{
    // User class
    public const ROLE_PEASANT = 0;
    public const ROLE_USER = 1;
    public const ROLE_POWER_USER = 2;
    public const ROLE_ELITE_USER = 3;
    public const ROLE_CRAZY_USER = 4;
    public const ROLE_INSANE_USER = 5;
    public const ROLE_VETERAN_USER = 6;
    public const ROLE_EXTREME_USER = 7;
    public const ROLE_ULTIMATE_USER = 8;
    public const ROLE_MASTER_USER = 9;   # The max level that user can reached if they reached the level setting
    public const ROLE_TEMP_VIP = 10;    # The max level that user can reached via bonus exchange

    // Contributor class
    public const ROLE_VIP = 20;
    public const ROLE_RETIREE = 30;

    // Uploader class
    public const ROLE_UPLOADER = 40;
    public const ROLE_HELPER = 50;

    // Administrator class
    public const ROLE_FORUM_MODERATOR = 60;
    public const ROLE_MODERATOR = 70;
    public const ROLE_ADMINISTRATOR = 80;
    public const ROLE_SYSOP = 90;
    public const ROLE_STAFFLEADER = 100;

    protected static $role = [
        self::ROLE_PEASANT => 'PEASANT',
        self::ROLE_USER => 'USER',
        self::ROLE_POWER_USER => 'POWER_USER',
        self::ROLE_ELITE_USER => 'ELITE_USER',
        self::ROLE_CRAZY_USER => 'CRAZY_USER',
        self::ROLE_INSANE_USER => 'INSANE_USER',
        self::ROLE_VETERAN_USER => 'VETERAN_USER',
        self::ROLE_EXTREME_USER => 'EXTREME_USER',
        self::ROLE_ULTIMATE_USER => 'ULTIMATE_USER',
        self::ROLE_MASTER_USER => 'MASTER_USER',
        self::ROLE_TEMP_VIP => 'TEMP_VIP',

        self::ROLE_VIP => 'VIP',
        self::ROLE_RETIREE => 'RETIREE',

        self::ROLE_UPLOADER => 'UPLOADER',
        self::ROLE_HELPER => 'HELPER',

        self::ROLE_FORUM_MODERATOR => 'FORUM_MODERATOR',
        self::ROLE_MODERATOR => 'MODERATOR',
        self::ROLE_ADMINISTRATOR => 'ADMINISTRATOR',
        self::ROLE_SYSOP => 'SYSOP',
        self::ROLE_STAFFLEADER => 'STAFFLEADER'
    ];

    public const STATUS_BANNED = 'banned';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PARKED = 'parked';
    public const STATUS_CONFIRMED = 'confirmed';

    public $id;
    public $username;

    public static function validUsername($username)
    {
        if ($username == "") return "Empty User name";
        if (strlen($username) > 12) return "User name is too log.";

        // The following characters are allowed in user names
        if (strspn(strtolower($username), "abcdefghijklmnopqrstuvwxyz0123456789_") != strlen($username))
            return "Invalid characters in user names";

        $count = PDO::createCommand("SELECT COUNT(`id`) FROM `users` WHERE `username` = :username")->bindParams([
            "username" => $username
        ])->queryScalar();
        if ($count > 0) return "The user name is already used";

        return "";
    }

    public static function fetchUserCount()
    {
        return PDO::createCommand("SELECT COUNT(`id`) FROM `users`")->queryScalar();
    }
}
