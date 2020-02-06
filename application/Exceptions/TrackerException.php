<?php
/**
 * Created by PhpStorm.
 * User: Rhili
 * Date: 2018/11/22
 * Time: 21:16
 */

namespace App\Exceptions;

use Throwable;

class TrackerException extends \Exception
{
    protected $error_msg = [
        // Error message of base Tracker system
        100 => 'This Tracker is not open now.',
        101 => 'The Scrape extension for this tracker is not enabled.',
        102 => 'The Announce extension for this tracker is not enabled.',

        // Error message about Requests ( Part.1 HTTP Method and Route )
        110 => 'Invalid request type: client request (:method) was not a HTTP GET.',
        111 => 'Invalid Action type `:action`.',

        // Error message about User Agent ( Bittorrent Client )
        120 => 'Invalid user-agent !',
        121 => 'Browser, Crawler or Cheater is not Allowed.',
        122 => 'Abnormal access blocked !',
        123 => 'The User-Agent of this client is too long!',
        124 => ':pattern REGEX error for :start, please ask sysop to fix this.',
        125 => 'Your client is too old. please update it after :start .',
        126 => 'Client :ua is not acceptable! Please check our FAQ.',
        127 => 'Client :ua banned due to: :comment .',

        // Error message about Requests ( Part.2 request params )
        130 => 'key: :attribute is Missing !',
        131 => 'Invalid :attribute ! :reason',  // Normal Invalid, Use below instead.
        132 => 'Invalid :attribute ! the length of :attribute must be :rule',
        133 => 'Invalid :attribute ! :attribute is not :rule bytes long',
        134 => 'Invalid :attribute ! :attribute Must be a number greater than or equal to 0',
        135 => 'Illegal port :port . Port should between 6881-64999',
        136 => 'Unsupported Event type :event .',
        137 => 'Illegal port 0 under Event type :event .',

        // Error message about User Account
        140 => 'Not Exist Passkey! Please Re-download the .torrent',
        141 => 'Your account is not enabled! ( Current `:status` )',
        142 => 'Your downloading privileges have been disabled! (Read the rules)',

        // Error message about Torrent
        150 => 'Torrent not registered with this tracker.',
        151 => 'You do not have permission to download a :status torrent.',
        152 => 'The torrent status :status is not valid.',

        // Error message about Download Session
        160 => 'You cannot seed the same torrent from more than :count locations.',
        161 => 'You are already downloading the same torrent. You can only leech from :count location at a time!',
        162 => 'There is a minimum announce lock of :min seconds, please wait.',
        163 => 'Your ratio is too low! You need to wait :sec seconds to start.',
        164 => "Your slot limit is reached! You may at most download :max torrents at the same time",

        // Error message from Anti-Cheater System
        170 => "We believe you're trying to cheat. And your account is disabled.",

        // Test Message
        998 => 'Internal server error :msg',
        999 => ':test',
    ];

    public function __construct(int $code = 999, array $replace = null, Throwable $previous = null)
    {
        $message = $this->error_msg[$code];
        if ($replace) {
            foreach ($replace as $key => $value) {
                $message = str_replace($key, $value, $message);
            }
        }

        parent::__construct($message, $code, $previous);
    }
}
