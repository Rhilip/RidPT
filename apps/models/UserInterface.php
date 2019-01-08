<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/31
 * Time: 11:39
 */

namespace apps\models;


interface UserInterface
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

    public const STATUS_BANNED = 'banned';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PARKED = 'parked';
    public const STATUS_CONFIRMED = 'confirmed';
}
