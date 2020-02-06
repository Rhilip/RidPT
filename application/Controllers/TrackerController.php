<?php
/**
 * Created by PhpStorm.
 * User: Rhili
 * Date: 2018/11/22
 * Time: 15:01
 */

namespace App\Controllers;

use App\Libraries\Constant;
use App\Entity\User\UserRole;
use App\Exceptions\TrackerException;

use Rid\Utils\IpUtils;

use Rhilip\Bencode\Bencode;
use Symfony\Component\HttpFoundation\Request;

/** @noinspection PhpUnused */
class TrackerController
{
    protected $timenow;

    /**
     * The Black List of Announce Port
     *
     * @var array
     * @see https://www.speedguide.net/port.php or other website
     */
    const portBlacklist =  [
        22,  // SSH Port
        53,  // DNS queries
        80, 81, 8080, 8081,  // Hyper Text Transfer Protocol (HTTP) - port used for web traffic
        411, 412, 413,  // 	Direct Connect Hub (unofficial)
        443,  // HTTPS / SSL - encrypted web traffic, also used for VPN tunnels over HTTPS.
        1214,  // Kazaa - peer-to-peer file sharing, some known vulnerabilities, and at least one worm (Benjamin) targeting it.
        3389,  // IANA registered for Microsoft WBT Server, used for Windows Remote Desktop and Remote Assistance connections
        4662,  // eDonkey 2000 P2P file sharing service. http://www.edonkey2000.com/
        6346, 6347,  // Gnutella (FrostWire, Limewire, Shareaza, etc.), BearShare file sharing app
        6699,  // Port used by p2p software, such as WinMX, Napster.
        6881, 6882, 6883, 6884, 6885, 6886, 6887, // BitTorrent part of full range of ports used most often (unofficial)
        //65000, 65001, 65002, 65003, 65004, 65005, 65006, 65007, 65008, 65009, 65010   // For unknown Reason 2333~
    ];

    /**
     * @return string
     */
    public function actionIndex()
    {
        // Set Response Header ( Format, HTTP Cache )
        app()->response->headers->set('Content-Type', 'text/plain; charset=utf-8');
        app()->response->headers->set('Connection', 'close');
        app()->response->headers->set('Pragma', 'no-cache');

        $userInfo = null;
        $torrentInfo = null;
        $this->timenow = time();

        try {
            // Block NON-GET requests (Though non-GET request will not match this Route )
            if (!app()->request->isMethod(Request::METHOD_GET)) {
                throw new TrackerException(110, [':method' => app()->request->getMethod()]);
            }

            if (!config('base.enable_tracker_system')) {
                throw new TrackerException(100);
            }

            $this->blockClient();

            $action = strtolower(app()->request->route('{tracker_action}'));
            $this->checkUserAgent($action == 'scrape');

            $this->checkPasskey($userInfo);

            switch ($action) {
                // Tracker Protocol Extension: Scrape - http://www.bittorrent.org/beps/bep_0048.html
                case 'scrape':
                    {
                        if (!config('tracker.enable_scrape')) {
                            throw new TrackerException(101);
                        }

                        $this->checkScrapeFields($info_hash_array);
                        $this->generateScrapeResponse($info_hash_array, $rep_dict);

                        return Bencode::encode($rep_dict);
                    }

                case 'announce':
                    {
                        if (!config('tracker.enable_announce')) {
                            throw new TrackerException(102);
                        }

                        $this->checkAnnounceFields($queries);

                        /**
                         * If nothing error we start to get and cache the the torrent_info from database,
                         * and check peer's privilege
                         */
                        $this->getTorrentInfo($queries, $userInfo, $torrentInfo);

                        $role = '';

                        /** Lock Announce Duration By Requests Uri hash to avoid **BAD** Bittorrent client */
                        if ($this->lockAnnounceDuration() == false) {
                            /** Lock Min Announce Interval */
                            $this->checkMinInterval($queries);

                            /** Get peer's Role
                             *
                             * In a P2P network , a peer's role can be describe as `seeder` or `leecher`,
                             * Which We can judge from the `$left=` params.
                             *
                             * However BEP 0021 `Extension for partial seeds` add a new role `partial seed`.
                             * A partial seed is a peer that is incomplete without downloading anything more.
                             * This happens for multi file torrents where users only download some of the files.
                             *
                             * So another `&event=paused` is need to judge if peer is `paused` or `partial seed`.
                             * However we still calculate it's duration as leech time, but only seed leecher info to him
                             *
                             * We Also Add a custom role which is empty string, Which means We needn't generate
                             * peer_list for this peer.
                             *
                             * @see http://www.bittorrent.org/beps/bep_0021.html
                             *
                             */
                            $role = ($queries['left'] == 0) ? 'yes' : 'no';
                            if ($queries['event'] == 'paused') {
                                $role = 'partial';
                            }

                            /** Check if user can open this session */
                            $this->checkSession($queries, $role, $userInfo, $torrentInfo);

                            // Send all info to task worker to quick return
                            $this->sendToTaskWorker($queries, $role, $userInfo, $torrentInfo);
                        }

                        $this->generateAnnounceResponse($queries, $role, $torrentInfo, $rep_dict);
                        return Bencode::encode($rep_dict);
                    }

                default:
                    throw new TrackerException(111, [':action' => $action]);
            }
        } catch (TrackerException $e) {
            // Record agent deny log in Table `agent_deny_log`
            if ($e->getCode() >= 124) {
                $this->logException($e, $userInfo, $torrentInfo);
            }

            return Bencode::encode([
                'failure reason' => $e->getMessage(),
                'min interval' => (int) config('tracker.min_interval')
                /**
                 * BEP 31: Failure Retry Extension
                 *
                 * However most bittorrent client don't support it, so this feature is disabled default
                 *  - libtorrent-rasterbar (e.g. qBittorrent, Deluge )
                 *    This library will obey the `min interval` key if exist or it will retry in 60s (By default `min interval`)
                 *  - libtransmission (e.g. Transmission )
                 *    This library will ignore any other key if failed
                 *
                 * @see http://www.bittorrent.org/beps/bep_0031.html
                 */
                //'retry in' => config('tracker.retry_interval')
            ]);
        }
    }

    protected function logException(\Exception $exception, $userInfo = null, $torrentInfo = null)
    {
        $req_info = app()->request->getQueryString() . "\n\n";
        $req_info .= (string) app()->request->headers;

        app()->pdo->prepare('INSERT INTO `agent_deny_log`(`tid`, `uid`, `user_agent`, `peer_id`, `req_info`,`create_at`, `msg`)
                VALUES (:tid,:uid,:ua,:peer_id,:req_info,CURRENT_TIMESTAMP,:msg)
                ON DUPLICATE KEY UPDATE `user_agent` = VALUES(`user_agent`),`peer_id` = VALUES(`peer_id`),
                                        `req_info` = VALUES(`req_info`),`msg` = VALUES(`msg`),
                                        `last_action_at` = NOW();')->bindParams([
            'tid' => $torrentInfo ? $torrentInfo['id'] : 0,
            'uid' => $userInfo ? $userInfo['id'] : 0,
            'ua' => app()->request->headers->get('user-agent', ''),
            'peer_id' => app()->request->query->get('peer_id', ''),
            'req_info' => $req_info,
            'msg' => $exception->getMessage()
        ])->execute();
    }

    /** Check Client's User-Agent, (If not pass this Check , A TrackerException will throw)
     * @throws TrackerException
     */
    private function blockClient()
    {
        // Miss Header User-Agent is not allowed.
        if (!app()->request->headers->get('user-agent')) {
            throw new TrackerException(120);
        }

        // Block Other Browser, Crawler (, May Cheater or Faker Client) by check Requests headers
        if (app()->request->headers->get('accept-language') || app()->request->headers->get('referer')
            || app()->request->headers->get('accept-charset')

            /**
             * This header check may block Non-bittorrent client `Aria2` to access tracker,
             * Because they always add this header which other clients don't have.
             *
             * @see https://blog.rhilip.info/archives/1010/ ( in Chinese )
             */
            || app()->request->headers->get('want-digest')

            /**
             * If your tracker is behind the Cloudflare or other CDN (proxy) Server,
             * Comment this line to avoid unexpected Block ,
             * Because They may add the Cookie header ,
             * Otherwise you should enabled this header check
             *
             * For example :
             *
             * The Cloudflare will add `__cfduid` Cookies to identify individual clients behind a shared IP address
             * and apply security settings on a per-client basis.
             *
             * @see https://support.cloudflare.com/hc/en-us/articles/200170156
             *
             */
            //|| app()->request->headers->get('cookie')
        ) {
            throw new TrackerException(122);
        }

        $ua = app()->request->headers->get('user-agent');

        // Should also Block those too long User-Agent. ( For Database reason
        if (strlen($ua) > 64) {
            throw new TrackerException(123);
        }

        // Block Browser by check it's User-Agent
        if (preg_match('/(Mozilla|Browser|Chrome|Safari|AppleWebKit|Opera|Links|Lynx|Bot|Unknown)/i', $ua)) {
            throw new TrackerException(121);
        }
    }

    /**
     * @param bool $onlyCheckUA
     * @throws TrackerException
     */
    private function checkUserAgent(bool $onlyCheckUA = false)
    {
        // Start Check Client by `User-Agent` and `peer_id`
        $userAgent = app()->request->headers->get('user-agent');
        $peer_id = app()->request->query->get('peer_id', '');
        $client_identity = $userAgent . ($onlyCheckUA ? '' : ':' . $peer_id);

        // if this user-agent and peer_id already checked valid or not ?
        if (app()->redis->zScore(Constant::trackerValidClientZset, $client_identity) > 0) {
            return;
        }

        // Get Client White List From Database and cache it
        if (false === $allowedFamily = app()->redis->get(Constant::trackerAllowedClientList)) {
            $allowedFamily = app()->pdo->prepare("SELECT * FROM `agent_allowed_family` WHERE `enabled` = 'yes' ORDER BY `hits` DESC")->queryAll();
            app()->redis->set(Constant::trackerAllowedClientList, $allowedFamily, 86400);
        }

        $agentAccepted = null;
        $peerIdAccepted = null;
        $acceptedAgentFamilyId = null;
        $acceptedAgentFamilyException = null;

        foreach ($allowedFamily as $allowedItem) {
            // Initialize FLAG before each loop
            $agentAccepted = false;
            $peerIdAccepted = false;
            $acceptedAgentFamilyId = 0;
            $acceptedAgentFamilyException = false;

            // Check User-Agent
            if ($allowedItem['agent_pattern'] != '') {
                if (!preg_match($allowedItem['agent_pattern'], $allowedItem['agent_start'], $agentShould)) {
                    throw new TrackerException(124, [':pattern' => "User-Agent", ':start' => $allowedItem['start_name']]);
                }

                if (preg_match($allowedItem['agent_pattern'], $userAgent, $agentMatched)) {
                    if ($allowedItem['agent_match_num'] > 0) {
                        for ($i = 0; $i < $allowedItem['agent_match_num']; $i++) {
                            $conversion_func = $allowedItem['agent_matchtype'] == 'hex' ? 'hexdec' : 'intval';
                            $agentMatched[$i + 1] = $conversion_func($agentMatched[$i + 1]);
                            $agentShould[$i + 1] = $conversion_func($agentShould[$i + 1]);

                            // Compare agent version number from high to low
                            // The high version number is already greater than the requirement, Break,
                            if ($agentMatched[$i + 1] > $agentShould[$i + 1]) {
                                $agentAccepted = true;
                                break;
                            }
                            // Below requirement
                            if ($agentMatched[$i + 1] < $agentShould[$i + 1]) {
                                throw new TrackerException(125, [":start" => $allowedItem['start_name']]);
                            }
                            // Continue to loop. Unless the last bit is equal.
                            if ($agentMatched[$i + 1] == $agentShould[$i + 1] && $i + 1 == $allowedItem['agent_match_num']) {
                                $agentAccepted = true;
                            }
                        }
                    } else {
                        $agentAccepted = true;  // No need to compare `version number`
                    }
                }
            } else {
                $agentAccepted = true;  // No need to compare `agent pattern`
            }

            if ($onlyCheckUA) {
                if ($agentAccepted) {
                    break;
                } else {
                    continue;
                }
            }

            // Check Peer_id
            if ($allowedItem['peer_id_pattern'] != '') {
                if (!preg_match($allowedItem['peer_id_pattern'], $allowedItem['peer_id_start'], $peerIdShould)) {
                    throw new TrackerException(124, [':pattern' => 'peer_id', ':start' => $allowedItem['start_name']]);
                }

                if (preg_match($allowedItem['peer_id_pattern'], $peer_id, $peerIdMatched)) {
                    if ($allowedItem['peer_id_match_num'] > 0) {
                        for ($i = 0; $i < $allowedItem['peer_id_match_num']; $i++) {
                            $conversion_func = $allowedItem['peer_id_matchtype'] == 'hex' ? 'hexdec' : 'intval';
                            $peerIdMatched[$i + 1] = $conversion_func($peerIdMatched[$i + 1]);
                            $peerIdShould[$i + 1] = $conversion_func($peerIdShould[$i + 1]);

                            // Compare agent version number from high to low
                            // The high version number is already greater than the requirement, Break,
                            if ($peerIdMatched[$i + 1] > $peerIdShould[$i + 1]) {
                                $peerIdAccepted = true;
                                break;
                            }
                            // Below requirement
                            if ($peerIdMatched[$i + 1] < $peerIdShould[$i + 1]) {
                                throw new TrackerException(114, [':start' => $allowedItem['start_name']]);
                            }
                            // Continue to loop. Unless the last bit is equal.
                            if ($peerIdMatched[$i + 1] == $peerIdShould[$i + 1] && $i + 1 == $allowedItem['agent_match_num']) {
                                $peerIdAccepted = true;
                            }
                        }
                    } else {
                        $peerIdAccepted = true;  // No need to compare `peer_id`
                    }
                }
            } else {
                $peerIdAccepted = true;  // No need to compare `Peer id pattern`
            }

            // Stop check Loop if matched once
            if ($agentAccepted && $peerIdAccepted) {
                $acceptedAgentFamilyId = $allowedItem['id'];
                $acceptedAgentFamilyException = $allowedItem['exception'] == 'yes' ? true : false;
                break;
            }
        }

        if ($onlyCheckUA) {
            if (!$agentAccepted) {
                throw new TrackerException(126, [':ua' => $userAgent]);
            }
            app()->redis->zAdd(Constant::trackerValidClientZset, time() + rand(7200, 18000), $client_identity);
            return;
        }

        if ($agentAccepted && $peerIdAccepted) {
            if ($acceptedAgentFamilyException) {
                // Get Client Exception List From Database and cache it since we need to check it
                if (false === $allowedFamilyException = app()->redis->get(Constant::trackerAllowedClientExceptionList)) {
                    $allowedFamilyException = app()->pdo->prepare('SELECT * FROM `agent_allowed_exception`')->queryAll();
                    app()->redis->set(Constant::trackerAllowedClientExceptionList, $allowedFamilyException, 86400);
                }

                foreach ($allowedFamilyException as $exceptionItem) {
                    // Throw TrackerException
                    if ($exceptionItem['family_id'] == $acceptedAgentFamilyId
                        && preg_match($exceptionItem['peer_id'], $peer_id)
                        && ($userAgent == $exceptionItem['agent'] || !$exceptionItem['agent'])
                    ) {
                        throw new TrackerException(127, [':ua' => $userAgent, ':comment' => $exceptionItem['comment']]);
                    }
                }
                app()->redis->zAdd(Constant::trackerValidClientZset, time() + rand(7200, 18000), $client_identity);
                // app()->redis->rawCommand('bf.add', [Constant::trackerValidClientZset, $client_identity]);
            }
        } else {
            throw new TrackerException(126, [':ua' => $userAgent]);
        }
    }

    /** Check Passkey Exist and Valid First, And We Get This Account Info
     * @param $userInfo
     * @throws TrackerException
     */
    private function checkPasskey(&$userInfo)
    {
        $passkey = app()->request->query->get('passkey');

        // First Check The param `passkey` is exist and valid
        if (is_null($passkey)) {
            throw new TrackerException(130, [':attribute' => 'passkey']);
        }
        if (strlen($passkey) != 32) {
            throw new TrackerException(132, [':attribute' => 'passkey', ':rule' => 32]);
        }
        if (strspn(strtolower($passkey), 'abcdef0123456789') != 32) {  // MD5 char limit
            throw new TrackerException(131, [':attribute' => 'passkey', ':reason' => 'The format of passkey isn\'t correct']);
        }

        // Get userInfo from RedisConnection Cache and then Database
        if (false === $userInfo = app()->redis->get(Constant::userBaseContentByPasskey($passkey))) {
            $userInfo = app()->pdo->prepare('SELECT `id`, `status`, `passkey`, `downloadpos`, `class`, `uploaded`, `downloaded` FROM `users` WHERE `passkey` = :passkey LIMIT 1')
                ->bindParams(['passkey' => $passkey])->queryOne() ?: [];

            // Notice: We log empty array in Redis Cache if userInfo not find in our Database
            app()->redis->set(Constant::userBaseContentByPasskey($passkey), $userInfo, 3600 + rand(0, 300));
        }

        /**
         * Throw Exception If user can't Download From our sites
         * The following situation:
         *  - The user don't register in our site or they may use the fake or old passkey which is not exist.
         *  - The user's status is not `confirmed`
         *  - The user's download Permission is disabled.
         */
        if (empty($userInfo)) {
            throw new TrackerException(140);
        }
        if ($userInfo['status'] != 'confirmed') {
            throw new TrackerException(141, [':status' => $userInfo['status']]);
        }
        if ($userInfo['downloadpos'] == 'no') {
            throw new TrackerException(142);
        }
    }

    /**
     * @param $hash
     * @param bool $scrape
     * @return array|bool return the required field of torrent info by it's info_hash , or false when this info_hash is
     *                    invalid (Not exist in our database or this status is 'deleted'
     */
    private function getTorrentInfoByHash($hash, bool $scrape = false)
    {
        $bin2hex_hash = bin2hex($hash);

        // If Cache is not exist , We will get User info from Database
        if (false === $torrentInfo = app()->redis->get(Constant::trackerTorrentContentByInfoHash($bin2hex_hash))) {
            $torrentInfo = app()->pdo->prepare('SELECT `id`, `info_hash`, `owner_id`, `status`, `incomplete`, `complete`, `downloaded`, `added_at` FROM `torrents` WHERE `info_hash` = :info LIMIT 1')
                ->bindParams(['info' => $hash])->queryOne();
            if ($torrentInfo === false || $torrentInfo['status'] == 'deleted') {  // No-exist or deleted torrent
                $torrentInfo = [];
            }

            // Notice: We log empty array in Redis Cache if torrentInfo not find in our Database or in deleted status
            app()->redis->set(Constant::trackerTorrentContentByInfoHash($bin2hex_hash), $torrentInfo, 600 + rand(0, 50));
        }

        // Return false when this info_hash is invalid (Not exist in our database or this status is 'deleted'
        if (empty($torrentInfo)) {
            return false;
        }

        // Return limit field when in scrape model
        return $scrape ? array_intersect_key($torrentInfo, array_flip(['incomplete', 'complete', 'downloaded'])) : $torrentInfo;
    }

    /**
     * @param $info_hash_array
     * @throws TrackerException
     */
    private function checkScrapeFields(&$info_hash_array)
    {
        preg_match_all('/info_hash=([^&]*)/i', urldecode(app()->request->getQueryString()), $info_hash_match);

        $info_hash_array = $info_hash_match[1];
        if (count($info_hash_array) < 1) {
            throw new TrackerException(130, [':attribute' => 'info_hash']);
        } else {
            foreach ($info_hash_array as $item) {
                if (strlen($item) != 20) {
                    throw new TrackerException(133, [':attribute' => 'info_hash', ':rule' => 20]);
                }
            }
        }
    }

    private function generateScrapeResponse($info_hash_array, &$rep_dict)
    {
        $torrent_details = [];
        foreach ($info_hash_array as $item) {
            $metadata = $this->getTorrentInfoByHash($item, true);
            if ($metadata !== false) {
                $torrent_details[$item] = $metadata;
            }  // Append it to tmp array only it exist.
        }

        $rep_dict = ['files' => $torrent_details];
    }

    /** See more: http://www.bittorrent.org/beps/bep_0003.html#trackers
     * @param array $queries
     * @throws TrackerException
     */
    private function checkAnnounceFields(&$queries = [])
    {
        // Part.1 check Announce **Need** Fields
        // Notice: param `passkey` is not require in BEP , but is required in our private torrent tracker system
        foreach (['info_hash', 'peer_id', 'port', 'uploaded', 'downloaded', 'left', 'passkey'] as $item) {
            $item_data = app()->request->query->get($item);
            if (!is_null($item_data)) {
                $queries[$item] = $item_data;
            } else {
                throw new TrackerException(130, [':attribute' => $item]);
            }
        }

        foreach (['info_hash', 'peer_id'] as $item) {
            if (strlen($queries[$item]) != 20) {
                throw new TrackerException(133, [':attribute' => $item, ':rule' => 20]);
            }
        }

        foreach (['uploaded', 'downloaded', 'left'] as $item) {
            $item_data = $queries[$item];
            if (!is_numeric($item_data) || $item_data < 0) {
                throw new TrackerException(134, [':attribute' => $item]);
            }
        }

        // Part.2 check Announce **Option** Fields
        foreach ([
                     'event' => '', 'no_peer_id' => 1, 'compact' => 0,
                     'numwant' => 50, 'corrupt' => 0, 'key' => '',
                     'ip' => '', 'ipv4' => '', 'ipv6' => '',
                 ] as $item => $value) {
            $queries[$item] = app()->request->query->get($item, $value);
        }

        foreach (['numwant', 'corrupt', 'no_peer_id', 'compact'] as $item) {
            if (!is_numeric($queries[$item]) || $queries[$item] < 0) {
                throw new TrackerException(134, [":attribute" => $item]);
            }
        }

        if (!in_array(strtolower($queries['event']), ['started', 'completed', 'stopped', 'paused', ''])) {
            throw new TrackerException(136, [":event" => strtolower($queries['event'])]);
        }

        $queries['user-agent'] = app()->request->headers->get('user-agent');

        // Part.3 check Announce *IP* Fields
        /**
         * We have `ip` , `ipv6` , `port` ,`ipv6_port` Columns in Table `peers`
         * But peer 's ip can be find in Requests Headers and param like `&ip=` , `&ipv4=` , `&ipv6=`
         * So, we should deal with those situation.
         *
         * We get `ipv6` and `ipv6_port` data from `&ipv6=` (address or endpoint) ,
         * Which is a  Native-IPv6 , not as link-local site-local loop-back Terodo 6to4
         * If fails , then fail back to $remote_ip (If it's IPv6 format) and `&port=`
         *
         * As The same reason, `ip` will get Form `&ipv4=` (address or endpoint)
         * and fail back to $remote_ip (If it's IPv4 format)
         *
         * However some bittorrent client may use `&ip=` to store peer's ipv4 or ipv6 address,
         * So the check order is : `&ipv6=`, `&ipv4=` -> `&ip=` -> $remote_ip
         *
         * After valid those ip params , we will identify peer connect type AS:
         *  1. Only IPv4  2. Only IPv6  3. Both IPv4-IPv6
         * Which is useful when generate Announce Response.
         *
         * See more: http://www.bittorrent.org/beps/bep_0007.html
         */

        $queries['remote_ip'] = $remote_ip = app()->request->getClientIp();  // IP address from Request Header (Which is NexusPHP used)
        $queries['ipv6_port'] = $queries['port'];

        // Get user ipv6 and ipv6_port data and store it in $queries['ipv6'] and $queries['ipv6_port']
        if ($queries['ipv6']) {
            if ($client = IpUtils::isEndPoint($queries['ipv6'])) {
                $queries['ipv6'] = $client['ip'];
                $queries['ipv6_port'] = $client['port'];
            }

            // Ignore all un-Native IPv6 address ( starting with FD or FC ; reserved IPv6 ) and IPv4-mapped-IPv6 address
            if (!IpUtils::isPublicIPv6($queries['ipv6']) || strpos($queries['ipv6'], '.') !== false) {
                $queries['ipv6'] = $queries['ipv6_port'] = '';
            }
        }

        // If we can't get valid IPv6 address from `&ipv6=`
        // fail back to `&ip=<IPv6>` then the IPv6 format remote_ip
        if (!$queries['ipv6']) {
            if ($queries['ip'] && IpUtils::isValidIPv6($queries['ip'])) {
                $queries['ipv6'] = $queries['ip'];
            } elseif (IpUtils::isValidIPv6($remote_ip)) {
                $queries['ipv6'] = $remote_ip;
            }
            if ($queries['ipv6']) {
                $queries['ipv6_port'] = $queries['port'];
            }
        }

        // Clean $queries['ip'] field and then store ipv4 data in it to make sure this field is IPv4-Only
        if ($queries['ip'] && !IpUtils::isValidIPv4($queries['ip'])) {
            $queries['ip'] = '';
        }

        // handle param `&ipv4=` like `&ipv6=`
        if ($queries['ipv4']) {
            if ($client = IpUtils::isEndPoint($queries['ipv4'])) {
                if (IpUtils::isValidIPv4($client['ip'])) {
                    $queries['ip'] = $client['ip'];
                    $queries['port'] = $client['port'];
                }
            } elseif (IpUtils::isValidIPv4($queries['ipv4'])) {
                $queries['ip'] = $queries['ipv4'];
            }
        }

        // Fail back to remote_ip which in IPv4-format
        if (!IpUtils::isPublicIPv4($queries['ip']) && IpUtils::isValidIPv4($remote_ip)) {
            $queries['ip'] = $remote_ip;
        }

        // Part.4 check Port Fields is Valid and Allowed
        $this->checkPortFields($queries['port']);
        if (isset($queries['ipv6_port']) && $queries['port'] != $queries['ipv6_port']) {
            $this->checkPortFields($queries['ipv6_port']);
        }
        if ($queries['port'] == 0 && strtolower($queries['event']) != 'stopped') {
            throw new TrackerException(137, [":event" => strtolower($queries['event'])]);
        }
    }

    /** Check Port
     *
     * Normally , the port must in 1 - 65535 , that is ( $port > 0 && $port < 0xffff )
     * However, in some case , When `&event=stopped` the port may set to 0.
     * @param $port
     * @throws TrackerException
     */
    private function checkPortFields($port)
    {
        if (!is_numeric($port) || $port < 0 || $port > 0xffff || in_array($port, self::portBlacklist)) {
            throw new TrackerException(135, [':port' => $port]);
        }
    }

    /**
     * @param $queries
     * @param $userInfo
     * @param $torrentInfo
     * @throws TrackerException
     */
    private function getTorrentInfo($queries, $userInfo, &$torrentInfo)
    {
        $torrentInfo = $this->getTorrentInfoByHash($queries['info_hash']);
        if ($torrentInfo === false) {
            throw new TrackerException(150);
        }

        switch ($torrentInfo['status']) {
            case 'confirmed':
                return; // Do nothing , just break torrent status check when it is a confirmed torrent
            case 'pending':
            {
                // For Pending torrent , we just allow it's owner and other user who's class great than your config set to connect
                if ($torrentInfo['owner_id'] != $userInfo['id']
                    || $userInfo['class'] < config('authority.see_pending_torrent')) {
                    throw new TrackerException(151, [':status' => $torrentInfo['status']]);
                }
                break;
            }
            case 'banned':
            {
                // For Banned Torrent , we just allow the user who's class great than your config set to connect
                if ($userInfo['class'] < config('authority.see_banned_torrent')) {
                    throw new TrackerException(151, [':status' => $torrentInfo['status']]);
                }
                break;
            }
            default:
            {
                throw new TrackerException(152, [':status' => $torrentInfo['status']]);
            }
        }
    }

    /**
     * Some Bittorrent Client May ReAnnounce When we use multi-tracker Extension,
     * At this time, Our tracker may receive same request twice or more.
     *
     * When our tracker receive the first announce request,
     * This function will hit a 60s lock in Redis zset based on request uri,
     * And if another same request hit the lock,
     * We should pass the main announce check and just return the empty peer_list in other announce request
     *
     * @return float|bool
     */
    private function lockAnnounceDuration()
    {
        $identity = md5(urldecode(app()->request->getQueryString()));
        if (false == $check = app()->redis->zScore(Constant::trackerAnnounceLockZset, $identity)) {  // this identity is not lock
            app()->redis->zAdd(Constant::trackerAnnounceLockZset, time() + 60, $identity);
        }
        return $check;
    }

    /**
     * @param $queries
     * @throws TrackerException
     */
    private function checkMinInterval($queries)
    {
        $identity = md5(implode(':', [
            // Use `passkey, info_hash, peer_id` as a unique key to check if this announce is in min interval
            $queries['passkey'], $queries['info_hash'], $queries['peer_id'],
            // We should also add `event` params to prevent peer completed announce been blocked after common announce
            $queries['event']
        ]));

        $prev_lock_expire = app()->redis->zScore(Constant::trackerAnnounceMinIntervalLockZset, $identity) ?: $this->timenow;
        if ($prev_lock_expire > $this->timenow) {
            throw new TrackerException(162, [':min' => config('tracker.min_interval')]);
        }

        $min_interval = intval(config('tracker.min_interval') * (3 / 4));
        app()->redis->zAdd(Constant::trackerAnnounceMinIntervalLockZset, $this->timenow + $min_interval, $identity);
    }

    /**
     * @param $queries
     * @param $seeder
     * @param $userInfo
     * @param $torrentInfo
     * @throws TrackerException
     */
    private function checkSession($queries, $seeder, $userInfo, $torrentInfo)
    {
        // Check if exist peer or not
        $identity = implode(':', [$torrentInfo['id'], $userInfo['id'], $queries['peer_id']]);
        if (app()->redis->zScore(Constant::trackerValidPeerZset, $identity)) {
            // this peer is already announce before , just expire cache key lifetime and return.
            app()->redis->zIncrBy(Constant::trackerValidPeerZset, config('tracker.interval') * 2, $identity);
            return;
        } elseif ($queries['event'] != 'stopped') {
            // If session is not exist and &event!=stopped, a new session should start

            // Cache may miss
            $self = app()->pdo->prepare('SELECT COUNT(`id`) FROM `peers` WHERE `user_id`=:uid AND `torrent_id`=:tid AND `peer_id`=:pid;')->bindParams([
                'uid' => $userInfo['id'], 'tid' => $torrentInfo['id'], 'pid' => $queries['peer_id']
            ])->queryScalar();
            if ($self !== 0) {  // True MISS
                app()->redis->zAdd(Constant::trackerValidPeerZset, $this->timenow + config('tracker.interval') * 2, $identity);
                return;
            }

            // First check if this peer can open this NEW session then create it
            $selfCount = app()->pdo->prepare('SELECT COUNT(*) AS `count` FROM `peers` WHERE `user_id` = :uid AND `torrent_id` = :tid;')->bindParams([
                'uid' => $userInfo['id'],
                'tid' => $torrentInfo['id']
            ])->queryScalar();

            // Ban one torrent seeding/leech at multi-location due to your site config
            if ($seeder == 'yes') { // if this peer's role is seeder
                if ($selfCount >= (config('tracker.user_max_seed'))) {
                    throw new TrackerException(160, [':count' => config('tracker.user_max_seed')]);
                }
            } else {
                if ($selfCount >= (config('tracker.user_max_leech'))) {
                    throw new TrackerException(161, [':count' => config('tracker.user_max_leech')]);
                }
            }

            if ($userInfo['class'] < UserRole::VIP) {
                $ratio = (($userInfo['downloaded'] > 0) ? ($userInfo['uploaded'] / $userInfo['downloaded']) : 1);
                $gigs = $userInfo['downloaded'] / (1024 * 1024 * 1024);

                // Wait System
                if (config('tracker.enable_waitsystem')) {
                    if ($gigs > 10) {
                        if ($ratio < 0.4) {
                            $wait = 24;
                        } elseif ($ratio < 0.5) {
                            $wait = 12;
                        } elseif ($ratio < 0.6) {
                            $wait = 6;
                        } elseif ($ratio < 0.8) {
                            $wait = 3;
                        } else {
                            $wait = 0;
                        }

                        $elapsed = time() - $torrentInfo['added_at'];
                        if ($elapsed < $wait) {
                            throw new TrackerException(163, [':sec' => $wait * 3600 - $elapsed]);
                        }
                    }
                }

                // Max SLots System
                if (config('tracker.enable_maxdlsystem')) {
                    $max = 0;
                    if ($gigs > 10) {
                        if ($ratio < 0.5) {
                            $max = 1;
                        } elseif ($ratio < 0.65) {
                            $max = 2;
                        } elseif ($ratio < 0.8) {
                            $max = 3;
                        } elseif ($ratio < 0.95) {
                            $max = 4;
                        }
                    }
                    if ($max > 0) {
                        $count = app()->pdo->prepare("SELECT COUNT(`id`) FROM `peers` WHERE `user_id` = :uid AND `seeder` = 'no';")->bindParams([
                            'uid' => $userInfo['id']
                        ])->queryScalar();
                        if ($count >= $max) {
                            throw new TrackerException(164, [':max' => $max]);
                        }
                    }
                }
            }

            // All Check Passed
            app()->redis->zAdd(Constant::trackerValidPeerZset, $this->timenow + config('tracker.interval') * 2, $identity);
        }
    }

    private function sendToTaskWorker($queries, $role, $userInfo, $torrentInfo)
    {
        /**
         * Push to Redis Queue so we can quick response
         *
         * Don't use json_{encode,decode} for the value of info_hash and peer_id will make
         * those function return FALSE
         */
        return app()->redis->lPush(Constant::trackerToDealQueue, [
            'timestamp' => $this->timenow,
            'queries' => $queries,
            'role' => $role,
            'userInfo' => $userInfo,
            'torrentInfo' => $torrentInfo
        ]);
    }

    private function generateAnnounceResponse($queries, $role, $torrentInfo, &$rep_dict)
    {
        $rep_dict = [
            'interval' => (int) config('tracker.interval') + rand(5, 20),   // random interval to avoid BOOM
            'min interval' => (int) config('tracker.min_interval') + rand(1, 10),
            'complete' => (int) $torrentInfo['complete'],
            'incomplete' => (int) $torrentInfo['incomplete'],
            'peers' => []  // By default it is a array object, only when `&compact=1` then it should be a string
        ];

        // For `stopped` event Or if peer's role not set (In AnnounceDuration lock)
        // We didn't send peers list any more, Just quick return without peer query in database~
        if ($queries['event'] == 'stopped' || $role == '') {
            return;
        }

        // Fix rep_dict format based on params `&compact=`, `&np_peer_id=`, `&numwant=` and our tracker config
        $compact = (bool) ($queries['compact'] == 1 || config('tracker.force_compact_model'));
        if ($compact) {
            $queries['no_peer_id'] = 1;  // force `no_peer_id` when `compact` mode is enable
            $rep_dict['peers'] = '';  // Change `peers` from array to string
            if ($queries['ipv6']) {   // If peer has IPv6 address , we should add packed string in `peers6`
                $rep_dict['peers6'] = '';
            }
        }

        $no_peer_id = (bool) ($queries['no_peer_id'] == 1 || config('tracker.force_no_peer_id_model'));
        $limit = (int) ($queries['numwant'] <= config('tracker.max_numwant')) ? $queries['numwant'] : config('tracker.max_numwant');

        // Query Peers in database
        $peers = app()->pdo->prepare([
            ['SELECT `port`, `ipv6_port` '],
            // Get ip and ipv6 field in binary or string depend on value of $compact
            [', `ip`, `ipv6` ', 'if' => $compact],
            [', INET6_NTOA(`ip`) as `ip`, INET6_NTOA(`ipv6`) as `ipv6` ', 'if' => !$compact],
            [', `peer_id` ', 'if' => !$no_peer_id],
            ['FROM `peers` WHERE torrent_id = :tid ', 'params' => ['tid' => $torrentInfo['id']]],
            ['AND peer_id != :pid  ', 'params' => ['pid' => $queries['peer_id']]],  // Don't select user himself
            ['AND `seeder` = \'no\' ', 'if' => $role != 'no'],  // Don't report seeders to other seeders (include partial seeders)
            ['ORDER BY RAND() LIMIT :limit', 'params' => ['limit' => $limit]]  // Random select so that everyone will return
        ])->queryAll();

        foreach ($peers as $peer) {
            $exchange_peer = [];

            if (!$no_peer_id) {
                $exchange_peer['peer_id'] = $peer['peer_id'];
            }

            if ($queries['ip'] && $peer['ip']) {
                if ($compact) {
                    // $peerList .= pack("Nn", sprintf("%d",ip2long($peer["ip"])), $peer['port']);
                    $rep_dict['peers'] .= $peer['ip'] . pack('n', $peer['port']);
                } else {
                    $exchange_peer['ip'] = $peer['ip'];
                    $exchange_peer['port'] = $peer['port'];
                    $rep_dict['peers'][] = $exchange_peer;
                }
            }

            if ($queries['ipv6'] && $peer['ipv6']) {
                if ($compact) {
                    $rep_dict['peers6'] .= $peer['ipv6'] . pack('n', $peer['ipv6_port']);
                } else {
                    // If peer don't want compact response, return ipv6-peer in `peers`
                    $exchange_peer['ip'] = $peer['ipv6'];
                    $exchange_peer['port'] = $peer['ipv6_port'];
                    $rep_dict['peers'][] = $exchange_peer;
                }
            }
        }
    }
}
