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

    const mapUsernameToId = 'Map:hash:user_username_to_user_id';
    const mapUserPasskeyToId = 'Map:zset:user_passkey_to_user_id';  // (double) 0 means invalid
    const mapUserSessionToId = 'Map:zset:user_session_to_user_id';  // (double) 0 means invalid

    // --- invalid Zset  ---
    const invalidUserIdZset = 'Site:zset:invalid_user_id';

    // Tracker Use
    const trackerInvalidInfoHashZset = 'Tracker:zset:invalid_torrent_info_hash';  // FIXME use set instead
    const trackerAllowedClientList = 'Tracker:string:allowed_client_list';
    const trackerAllowedClientExceptionList = 'Tracker:string:allowed_client_exception_list';
    const trackerValidClientZset = 'Tracker:zset:valid_clients';
    const trackerAnnounceLockZset = 'Tracker:zset:lock:announce_flood';
    const trackerAnnounceMinIntervalLockZset = 'Tracker:zset:lock:announce_min_interval';
    const trackerValidPeerZset = 'Tracker:zset:valid_peers';
    const trackerToDealQueue = 'Tracker:list:to_deal_queue';
    const trackerBackupQueue = 'Tracker:list:backup_queue';

    // Site Status
    const siteSubtitleSize = 'Site:string:subtitle_size';

    public static function userContent($uid)
    {
        return 'User:hash:user_' . $uid . '_content';
    }

    public static function torrentContent($tid)
    {
        return 'Torrent:hash:torrent_' . $tid . '_content';
    }

    // Tracker User
    public static function trackerUserContentByPasskey($passkey)
    {
        return 'Tracker:string:user_passkey_' . $passkey . '_content'; // Used string to store hash, Because we will get all value in it
    }

    public static function trackerTorrentContentByInfoHash($bin2hex_hash)
    {
        return 'Tracker:hash:torrent_infohash_' . $bin2hex_hash . '_content';
    }

    public static function rateLimitPool($pool, $action)
    {
        return 'Rate:zset:pool_' . $pool . '_action_' . $action . '_limit';
    }
}
