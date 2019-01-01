<?php

namespace apps\httpd\models;

class User implements UserInterface
{

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

    private $id;
    private $username;
    private $email;
    private $status;

    private $passkey;

    public function __construct($id = null)
    {

    }
}
