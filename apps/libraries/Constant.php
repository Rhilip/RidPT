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

    const mapUsernameToId = 'Map:user_username_to_user_id:hash';
    const mapUserSessionToId = 'Map:user_session_to_user_id:zset';
    const mapUserPasskeyToId = 'Map:user_passkey_to_user_id:zset';

    // invalid Zset
    const invalidUserIdZset = 'Site:invalid_user_id:zset';
    const invalidUserSessionZset = 'Session:invalid_user_session:zset';
    const invalidUserPasskeyZset = 'Tracker:invalid_user_passkey:zset';

    // Tracker Use
    const trackerInvalidInfoHashZset = 'Tracker:invalid_torrent_info_hash:zset';
    const trackerAllowedClientList = 'Tracker:allowed_client_list:string';
    const trackerAllowedClientExceptionList = 'Tracker:allowed_client_exception_list:string';
    const trackerValidClientZset = 'Tracker:valid_clients:zset';
    const trackerAnnounceLockZset = 'Tracker:announce_flood_lock:zset';
    const trackerAnnounceMinIntervalLockZset = 'Tracker:announce_min_interval_lock:zset';
    const trackerValidPeerZset = 'Tracker:valid_peers:zset';
    const trackerToDealQueue = 'Tracker:to_deal_queue:list';
    const trackerBackupQueue = 'Tracker:backup_queue:list';

    // Site Status
    const siteSubtitleSize = 'Site:subtitle_size:string';

    public static function userContent($uid)
    {
        return 'User:user_' . $uid . '_content:hash';
    }

    public static function torrentContent($tid)
    {
        return 'Torrent:torrent_' . $tid . '_content:hash';
    }

    // Tracker User
    public static function trackerUserContentByPasskey($passkey)
    {
        return 'Tracker:user_passkey_' . $passkey . '_content:string'; // Used string to store hash
    }

    public static function trackerTorrentContentByInfoHash($bin2hex_hash)
    {
        return 'Tracker:torrent_infohash_' . $bin2hex_hash . '_content:hash';
    }

    public static function rateLimitPool($pool, $action)
    {
        return 'Rate:pool_' . $pool . '_action_' . $action . '_limit:zset';
    }
}
