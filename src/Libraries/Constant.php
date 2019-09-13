<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/4
 * Time: 23:12
 */

namespace App\Libraries;


class Constant
{
    const cookie_name = 'rid';

    const mapUsernameToId = 'Map:hash:user_username_to_user_id';
    const mapUserPasskeyToId = 'Map:zset:user_passkey_to_user_id';  // (double) 0 means invalid
    const mapUserSessionToId = 'Map:zset:user_session_to_user_id';  // (double) 0 means invalid

    // --- invalid Zset  ---
    const invalidUserIdZset = 'Site:zset:invalid_user_id';

    // Tracker Use
    const trackerInvalidInfoHashZset = 'Tracker:invalid_torrent_info_hash';  // FIXME use set instead
    const trackerAllowedClientList = 'Tracker:allowed_client_list';
    const trackerAllowedClientExceptionList = 'Tracker:allowed_client_exception_list';
    const trackerValidClientZset = 'Tracker:valid_clients';
    const trackerAnnounceLockZset = 'Tracker:lock:announce_flood';
    const trackerAnnounceMinIntervalLockZset = 'Tracker:lock:announce_min_interval';
    const trackerValidPeerZset = 'Tracker:valid_peers';
    const trackerToDealQueue = 'Tracker:queue:to_deal';
    const trackerBackupQueue = 'Tracker:queue:backup';

    // Site Status
    const siteSubtitleSize = 'Site:subtitle_size';  // TODO move to app()->config
    const siteBannedEmailSet = 'Site:set:banned_list:email';
    const siteBannedUsernameSet = 'Site:set:banned_list:username';

    public static function userContent(int $uid)
    {
        return 'User:user_content:' . $uid;  // Hash
    }

    public static function torrentContent(int $tid)
    {
        return 'Torrent:torrent_content:' . $tid;  // Hash
    }

    // Tracker User
    public static function trackerUserContentByPasskey(string $passkey)
    {
        return 'Tracker:user_passkey_content:' . $passkey; // String
    }

    public static function trackerTorrentContentByInfoHash(string $bin2hex_hash)
    {
        return 'Tracker:torrent_infohash_content:' . $bin2hex_hash;  // Hash
    }

    public static function rateLimitPool($pool, $action)
    {
        return 'RateLimit:' . $pool . ':action_' . $action;  // Zset
    }

    public static function getTorrentFileLoc($tid)
    {
        return app()->getPrivatePath('torrents') . DIRECTORY_SEPARATOR . $tid . '.torrent';
    }
}
