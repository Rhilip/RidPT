<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/4
 * Time: 23:12
 */

namespace apps\libraries;


class Constant
{
    const cookie_name = 'rid';

    // Tracker Use
    const trackerInvalidPasskeyZset = 'Tracker:invalid_passkey:zset';
    const trackerInvalidInfoHashZset = 'Tracker:invalid_info_hash:zset';
    const trackerAllowedClientList = 'Tracker:allowed_client_list:string';
    const trackerAllowedClientExceptionList = 'Tracker:allowed_client_exception_list:string';
    const trackerValidClientZset = 'Tracker:valid_clients:zset';
    const trackerAnnounceLockZset = 'Tracker:announce_flood_lock:zset';
    const trackerAnnounceMinIntervalLockZset = 'Tracker:announce_min_interval_lock:zset';
    const trackerValidPeerZset = 'Tracker:valid_peers:zset';
    const trackerToDealQueue = 'Tracker:to_deal_queue:list';

    public static function trackerUserContentByPasskey($passkey)
    {
        return 'Tracker:user_passkey_' . $passkey . '_content:string'; // Used string to store hash
    }

    public static function trackerTorrentContentByInfoHash($bin2hex_hash){
        return 'Tracker:torrent_infohash_' . $bin2hex_hash . '_content:hash';
    }

}
