<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/11/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Controllers\Tracker;

use App\Entity\User\UserRole;
use App\Exceptions\TrackerException;
use App\Helper\SwitchHelper;
use App\Libraries\Constant;
use App\Tasks\Tracker\Announce;

use Rid\Utils;
use Rid\Swoole\Task;
use Rid\Http\Message\Request;

class AnnounceController extends ScrapeController
{

    /**
     * The Black List of Announce Port
     *
     * @var array
     * @see https://www.speedguide.net/port.php or other website
     */
    const BLACK_PORTS = [
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

    public function index(Request $request)
    {
        try {
            $this->trackerPreCheck('announce', $request);
            $userInfo = $this->checkPasskey($request);

            /**
             * Check and then get Announce queries.
             */
            $queries = $this->checkAnnounceFields($request);

            /**
             * Get Torrent Info Array from queries and judge if user can reach it.
             */
            $torrentInfo = $this->getTorrentInfo($queries, $userInfo);

            /**
             * If nothing error we start to get and cache the the torrent_info from database,
             * and check peer's privilege
             */
            $role = '';

            /** Check if this announce request is a re-announce. */
            if ($this->isReAnnounce($queries, $request) === false) {
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

            $rep_dict = $this->generateAnnounceResponse($queries, $role, $torrentInfo);
        } catch (TrackerException $e) {
            // Record agent deny log in Table `agent_deny_log`
            if ($e->getCode() >= 124 && isset($userInfo) && isset($torrentInfo)) {
                $this->logException($e, $request, $userInfo['id'], $torrentInfo['id']);
            }
            $rep_dict = $this->generateTrackerFailResponseDict($e);
        } finally {
            return $this->generateTrackerResponse($rep_dict);
        }
    }

    protected function logException(\Exception $exception, Request $request, $uid, $tid)
    {
        $req_info = $request->getQueryString() . "\n\n";
        $req_info .= (string)$request->headers;

        $this->pdo->prepare('INSERT INTO `agent_deny_log`(`tid`, `uid`, `user_agent`, `peer_id`, `req_info`,`create_at`, `msg`)
                VALUES (:tid,:uid,:ua,:peer_id,:req_info,CURRENT_TIMESTAMP,:msg)
                ON DUPLICATE KEY UPDATE `user_agent` = VALUES(`user_agent`),`peer_id` = VALUES(`peer_id`),
                                        `req_info` = VALUES(`req_info`),`msg` = VALUES(`msg`),
                                        `last_action_at` = NOW();')->bindParams([
            'tid' => $tid, 'uid' => $uid,
            'ua' => $request->headers->get('user-agent', ''),
            'peer_id' => $request->query->get('peer_id', ''),
            'req_info' => $req_info,
            'msg' => $exception->getMessage()
        ])->execute();
    }

    /** See more: http://www.bittorrent.org/beps/bep_0003.html#trackers
     * @param Request $request
     * @throws TrackerException
     */
    private function checkAnnounceFields(Request $request)
    {
        $queries = [
            'timestamp' => $request->server->get('REQUEST_TIME_FLOAT')
        ];
        // Part.1 check Announce **Need** Fields
        // Notice: param `passkey` is not require in BEP , but is required in our private torrent tracker system
        foreach (['info_hash', 'peer_id', 'port', 'uploaded', 'downloaded', 'left', 'passkey'] as $item) {
            $item_data = $request->query->get($item);
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
                 ] as $item => $value) {
            $queries[$item] = $request->query->get($item, $value);
        }

        foreach (['numwant', 'corrupt', 'no_peer_id', 'compact'] as $item) {
            if (!is_numeric($queries[$item]) || $queries[$item] < 0) {
                throw new TrackerException(134, [":attribute" => $item]);
            }
        }

        if (!in_array(strtolower($queries['event']), ['started', 'completed', 'stopped', 'paused', ''])) {
            throw new TrackerException(136, [":event" => strtolower($queries['event'])]);
        }

        $queries['user-agent'] = $request->headers->get('user-agent');

        /**
         * Part.3 check Announce *IP* Fields
         *
         * We have `ip`, `port`, `endpoints` Columns in Table `peers`
         * But peer 's ip can be find in Requests Headers and param like `&ip=` , `&ipv4=` , `&ipv6=`
         * So, we should deal with those situation.
         *
         * We record `ip` from $remote_ip, `port` from `&port=`,
         * And Any other ip:port from `&ip=` , `&ipv4=` , `&ipv6=` in Array $endpoints [$ip => $port, ...]
         *
         * See more: http://www.bittorrent.org/beps/bep_0007.html
         */
        $endpoints = [];

        // insert remote_ip:port
        $queries['ip'] = $remote_ip = $request->getClientIp();  // IP address from Request Header (Which is NexusPHP used)
        $endpoints[$remote_ip] = (int)$queries['port'];

        // insert ip:port from `&ipv6=`, `&ipv4=`, `&ip=`
        foreach (['ip', 'ipv6', 'ipv4'] as $ip_type) {
            if ($new_ip = $request->query->get($ip_type)) {
                $new_port = (int)$queries['port'];

                // Deal with ip in endpoint format
                if ($client = Utils\Ip::isEndPoint($new_ip)) {
                    $new_ip = $client['ip'];
                    $new_port = (int)$client['port'];
                }

                // make sure every k-v is unique and Ignore all un-Native address
                if (!array_key_exists($new_ip, $endpoints) && Utils\Ip::isPublicIp($new_ip)) {
                    $endpoints[$new_ip] = $new_port;
                }
            }
        }
        $queries['endpoints'] = $endpoints;

        [$ips, $ports] = Utils\Arr::divide($endpoints);

        /**
         * Part.4 Determine peer connect type by its announce ips
         *
         * After get valid endpoints, we will identify peer connect type AS:
         *  0. No Connect      - 0b00 (0)
         *  1. Only IPv4       - 0b01 (1)
         *  2. Only IPv6       - 0b10 (2)
         *  3. Both IPv4-IPv6  - 0b11 (3)
         * Which is useful when generate Announce Response.
         */
        $connect_type = 0b00;
        foreach ($ips as $ip) {
            $connect_type |= Utils\Ip::isValidIPv6($ip) ? 0b10 : 0b01;
        }
        $queries['connect_type'] = $connect_type;

        // Part.5 check Port Fields is Valid and Allowed
        foreach (array_unique($ports) as $port) {
            /**
             * Normally , the port must in 1 - 65535 , that is ( $port > 0 && $port < 0xffff )
             * However, in some case , When `&event=stopped` the port may set to 0.
             */
            if ($port == 0 && strtolower($queries['event']) != 'stopped') {
                throw new TrackerException(137, [':event' => strtolower($queries['event'])]);
            } elseif (!is_numeric($port) || $port < 0 || $port > 0xffff || in_array($port, self::BLACK_PORTS)) {
                throw new TrackerException(135, [':port' => $port]);
            }
        }
        return $queries;
    }

    /**
     * @param $queries
     * @param $userInfo
     * @return array
     * @throws TrackerException
     */
    private function getTorrentInfo($queries, $userInfo): array
    {
        $torrentInfo = $this->getTorrentInfoByHash($queries['info_hash']);
        if ($torrentInfo === false) {
            throw new TrackerException(150);
        }

        if ($torrentInfo['status'] == 'pending') {
            // For Pending torrent , we just allow it's owner and other user who's class great than your config set to connect
            if ($torrentInfo['owner_id'] != $userInfo['id']
                || $userInfo['class'] < config('authority.see_pending_torrent')) {
                throw new TrackerException(151, [':status' => $torrentInfo['status']]);
            }
        } elseif ($torrentInfo['status'] == 'banned') {
            // For Banned Torrent , we just allow the user who's class great than your config set to connect
            if ($userInfo['class'] < config('authority.see_banned_torrent')) {
                throw new TrackerException(151, [':status' => $torrentInfo['status']]);
            }
        }

        // For Confirmed Torrent
        return $torrentInfo;   // Do nothing , just break torrent status check when it is a confirmed torrent
    }

    /**
     * If Our Tracker in those situation:
     *    - Using multi-tracker Extension (BEP 0012), All query_string are the same
     *    - Tracker Url can resolve to multiple IP addresses (BEP 0007 Tracker Hostname Resolution)，
     *      All element except `key` in query_string  are the same
     *
     * Some Bittorrent Client May ReAnnounce many times,
     * Depending on Client Behaviour, Tracker/Tier Number, Resolved IP of each Tracker
     * Add we SHOULD only process the first announce requests which we received,
     * and just return the empty peer_list in other announce request.
     *
     * @param $queries
     * @param Request $request
     * @return bool
     */
    private function isReAnnounce($queries, Request $request)
    {
        $query_string = urldecode($request->getQueryString());
        $identity = md5(str_replace($queries['key'], '', $query_string));

        $prev_lock_expire_at = $this->redis->zScore(Constant::trackerAnnounceLockZset, $identity) ?: $queries['timestamp'];
        if ($queries['timestamp'] >= $prev_lock_expire_at) {  // this identity is not lock
            $this->redis->zAdd(Constant::trackerAnnounceLockZset, $queries['timestamp'] + 30, $identity);
            return false;
        }

        return true;
    }

    /**
     * @param $queries
     * @throws TrackerException
     */
    private function checkMinInterval($queries)
    {
        $identity = md5(implode(':', [
            // Use `passkey, info_hash, peer_id, key` as a unique key to check if this announce is in min interval
            $queries['passkey'],  // Identify User
            $queries['info_hash'],  // Identify Torrent
            $queries['peer_id'], $queries['key'],  // Identify Peer (peer_id + key)
            // We should also add `event` params to prevent peer completed announce been blocked after common announce
            $queries['event']
        ]));

        $prev_lock_expire = $this->redis->zScore(Constant::trackerAnnounceMinIntervalLockZset, $identity) ?: $queries['timestamp'];
        if ($prev_lock_expire > $queries['timestamp']) {
            throw new TrackerException(162, [':min' => config('tracker.min_interval')]);
        }

        $min_interval = (int)(config('tracker.min_interval') * (3 / 4));
        $this->redis->zAdd(Constant::trackerAnnounceMinIntervalLockZset, $queries['timestamp'] + $min_interval, $identity);
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
        if ($this->redis->zScore(Constant::trackerValidPeerZset, $identity)) {
            // this peer is already announce before , just expire cache key lifetime and return.
            $this->redis->zIncrBy(Constant::trackerValidPeerZset, config('tracker.interval') * 2, $identity);
            return;
        } elseif ($queries['event'] != 'stopped') {
            // If session is not exist and &event!=stopped, a new session should start

            // Cache may miss
            $self = $this->pdo->prepare('SELECT COUNT(`id`) FROM `peers` WHERE `user_id`=:uid AND `torrent_id`=:tid AND `peer_id`=:pid;')->bindParams([
                'uid' => $userInfo['id'], 'tid' => $torrentInfo['id'], 'pid' => $queries['peer_id']
            ])->queryScalar();
            if ($self !== 0) {  // True MISS
                $this->redis->zAdd(Constant::trackerValidPeerZset, $queries['timestamp'] + config('tracker.interval') * 2, $identity);
                return;
            }

            // First check if this peer can open this NEW session then create it
            $selfCount = $this->pdo->prepare('SELECT COUNT(*) AS `count` FROM `peers` WHERE `user_id` = :uid AND `torrent_id` = :tid;')->bindParams([
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
                if ($gigs > 10) {
                    // Wait System
                    if (config('tracker.enable_waitsystem')) {
                        $wait = SwitchHelper::selectRoundOneFromMap($ratio, ['0.4' => 24, '0.5' => 12, '0.6' => 6, '0.8' => 3], 0);
                        $elapsed = time() - $torrentInfo['added_at'];
                        if ($elapsed < $wait) {
                            throw new TrackerException(163, [':sec' => $wait * 3600 - $elapsed]);
                        }
                    }

                    // Max SLots System
                    if (config('tracker.enable_maxdlsystem')) {
                        $max = SwitchHelper::selectRoundOneFromMap($ratio, ['0.5' => 1, '0.65' => 2, '0.8' => 3, '0.95' => 4], 0);
                        if ($max > 0) {
                            $count = $this->pdo->prepare("SELECT COUNT(`id`) FROM `peers` WHERE `user_id` = :uid AND `seeder` = 'no';")->bindParams([
                                'uid' => $userInfo['id']
                            ])->queryScalar();
                            if ($count >= $max) {
                                throw new TrackerException(164, [':max' => $max]);
                            }
                        }
                    }
                }
            }

            // All Check Passed
            $this->redis->zAdd(Constant::trackerValidPeerZset, $queries['timestamp'] + config('tracker.interval') * 2, $identity);
        }
    }

    private function sendToTaskWorker($queries, $role, $userInfo, $torrentInfo)
    {
        // Push to Task Worker so we can quick response
        return Task\TaskManager::post(new Task\TaskInfo(Announce::class, [
            'timestamp' => $queries['timestamp'],
            'queries' => $queries,
            'role' => $role,
            'userInfo' => $userInfo,
            'torrentInfo' => $torrentInfo
        ]));
    }

    private function generateAnnounceResponse($queries, $role, $torrentInfo): array
    {
        $rep_dict = [
            'interval' => (int)(config('tracker.interval') + rand(5, 20)),   // random interval to avoid BOOM
            'min interval' => (int)(config('tracker.min_interval') + rand(1, 10)),
            'complete' => (int)$torrentInfo['complete'],
            'incomplete' => (int)$torrentInfo['incomplete'],
            'peers' => []  // By default it is a array object, only when `&compact=1` then it should be a string
        ];

        /**
         * For non `stopped` event Or if peer's role set (Not In AnnounceDuration lock)
         * We query peer from database and send peerlist, otherwise just quick return
         */
        if ($queries['event'] != 'stopped' || $role != '') {
            // Fix rep_dict format based on params `&compact=`, `&np_peer_id=`, `&numwant=` and our tracker config
            $compact = (bool)($queries['compact'] == 1 || config('tracker.force_compact_model'));
            if ($compact) {
                $queries['no_peer_id'] = 1;  // force `no_peer_id` when `compact` mode is enable
                $rep_dict['peers'] = '';  // Change `peers` from array to string
                $rep_dict['peers6'] = ''; // we should add Packed IPv6:port in `peers6`
            }

            $no_peer_id = (bool)($queries['no_peer_id'] == 1 || config('tracker.force_no_peer_id_model'));
            $limit = (int)($queries['numwant'] <= config('tracker.max_numwant')) ? $queries['numwant'] : config('tracker.max_numwant');

            // Query Peers in database
            $peers = $this->pdo->prepare([
                ['SELECT `endpoints`'],
                [', `peer_id` ', 'if' => !$no_peer_id],
                ['FROM `peers` WHERE torrent_id = :tid ', 'params' => ['tid' => $torrentInfo['id']]],
                ['AND `seeder` = \'no\' ', 'if' => $role != 'no'],  // Don't report seeders to other seeders (include partial seeders)
                ['AND peer_id != :pid ', 'params' => ['pid' => $queries['peer_id']]],  // Don't select user himself
                ['ORDER BY RAND() LIMIT :limit',
                    'if' => ($torrentInfo['complete'] + $torrentInfo['incomplete']) > $limit,  // LIMIT AND SORT only total peer plus numwant
                    'params' => ['limit' => $limit]
                ]  // Random select so that everyone will return
            ])->queryAll();

            foreach ($peers as $peer) {
                $exchange_peer = [];

                if (!$no_peer_id) {
                    $exchange_peer['peer_id'] = $peer['peer_id'];
                }

                $endpoints = json_decode($peer['endpoints']);
                foreach ($endpoints as $ip => $port) {
                    if ($compact == 1) {
                        $peer_insert_field = Utils\Ip::isValidIPv6($ip) ? 'peers6' : 'peers';
                        $rep_dict[$peer_insert_field] .= inet_pton($ip) . pack('n', $port);
                    } else {
                        $exchange_peer['ip'] = $ip;
                        $exchange_peer['port'] = $port;
                        $rep_dict['peers'][] = $exchange_peer;
                    }
                }
            }
        }

        return $rep_dict;
    }
}
