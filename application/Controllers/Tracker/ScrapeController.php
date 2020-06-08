<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/11/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Controllers\Tracker;

use App\Exceptions\TrackerException;
use App\Libraries\Constant;

use Rid\Utils\Arr;
use Rid\Http\Message\Request;

use Rhilip\Bencode\Bencode;

/**
 * Class ScrapeController
 * @package App\Controllers\Tracker
 * @see http://www.bittorrent.org/beps/bep_0048.html Tracker Protocol Extension: Scrape
 */
class ScrapeController
{

    public function index(Request $request)
    {
        try {
            $this->trackerPreCheck('scrape', $request);
            $this->checkPasskey($request);

            $info_hash_array = $this->checkScrapeFields($request);
            $rep_dict = $this->generateScrapeResponse($info_hash_array);
        } catch (\Throwable $e) {
            $rep_dict = $this->generateTrackerFailResponseDict($e);
        } finally {
            return $this->generateTrackerResponse($rep_dict);
        }
    }

    /**
     * @param string $extension
     * @param Request $request
     * @throws TrackerException
     */
    protected function trackerPreCheck($extension, Request $request)
    {
        $this->isTrackerSystemOpen($extension);
        $this->blockClient($request);
        $this->checkUserAgent($request, $extension == 'scrape');
    }

    protected function generateTrackerResponse($rep_dict)
    {
        // Set Response Header ( Format, HTTP Cache )
        container()->get('response')->headers->set('Content-Type', 'text/plain; charset=utf-8');
        container()->get('response')->headers->set('Connection', 'close');
        container()->get('response')->headers->set('Pragma', 'no-cache');

        return Bencode::encode($rep_dict);
    }

    protected function generateTrackerFailResponseDict(\Throwable $exception)
    {
        if ($exception instanceof TrackerException) {
            $reason = $exception->getMessage();
        } else {
            $reason = 'Internal Error';
        }

        return [
            'failure reason' => $reason,
            'min interval' => (int)config('tracker.min_interval')
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
        ];
    }

    /**
     * @param string $extension
     * @throws TrackerException
     */
    protected function isTrackerSystemOpen(string $extension = 'scrape')
    {
        if (!config('base.enable_tracker_system')) {
            throw new TrackerException(100);
        }
        if (!config('tracker.enable_' . $extension)) {
            throw new TrackerException(101, ['extension' => ucfirst($extension)]);
        }
    }

    /**
     * @param Request $request
     * @throws TrackerException
     */
    protected function blockClient(Request $request)
    {
        // Miss Header User-Agent is not allowed.
        if (!$request->headers->get('user-agent')) {
            throw new TrackerException(120);
        }

        // Block Other Browser, Crawler (, May Cheater or Faker Client) by check Requests headers
        if ($request->headers->get('accept-language') || $request->headers->get('referer')
            || $request->headers->get('accept-charset')

            /**
             * This header check may block Non-bittorrent client `Aria2` to access tracker,
             * Because they always add this header which other clients don't have.
             *
             * @see https://blog.rhilip.info/archives/1010/ ( in Chinese )
             */
            || $request->headers->get('want-digest')

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
            //|| $request->headers->get('cookie')
        ) {
            throw new TrackerException(122);
        }

        $user_agent = $request->headers->get('user-agent');

        // Should also Block those too long User-Agent. ( For Database reason
        if (strlen($user_agent) > 64) {
            throw new TrackerException(123);
        }

        // Block Browser by check it's User-Agent
        if (preg_match('/(Mozilla|Browser|Chrome|Safari|AppleWebKit|Opera|Links|Lynx|Bot|Unknown)/i', $user_agent)) {
            throw new TrackerException(121);
        }
    }

    /**
     * @param Request $request
     * @param bool $onlyCheckUA
     * @throws TrackerException
     */
    protected function checkUserAgent(Request $request, bool $onlyCheckUA = false)
    {
        // Start Check Client by `User-Agent` and `peer_id`
        $userAgent = $request->headers->get('user-agent');
        $peer_id = $request->query->get('peer_id', '');
        $client_identity = $userAgent . ($onlyCheckUA ? '' : ':' . $peer_id);

        // if this user-agent and peer_id already checked valid or not ?
        if (container()->get('redis')->zScore(Constant::trackerValidClientZset, $client_identity) > 0) {
            return;
        }

        // Get Client White List From Database and cache it
        if (false === $allowedFamily = container()->get('redis')->get(Constant::trackerAllowedClientList)) {
            $allowedFamily = container()->get('dbal')->prepare("SELECT * FROM `agent_allowed_family` WHERE `enabled` = 'yes' ORDER BY `hits` DESC")->fetchAll();
            container()->get('redis')->set(Constant::trackerAllowedClientList, $allowedFamily, 86400);
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
                                throw new TrackerException(125, [':start' => $allowedItem['start_name']]);
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
                $acceptedAgentFamilyException = $allowedItem['exception'] == 'yes';
                break;
            }
        }

        if ($onlyCheckUA) {
            if (!$agentAccepted) {
                throw new TrackerException(126, [':ua' => $userAgent]);
            }
            container()->get('redis')->zAdd(Constant::trackerValidClientZset, time() + rand(7200, 18000), $client_identity);
            return;
        }

        if ($agentAccepted && $peerIdAccepted) {
            if ($acceptedAgentFamilyException) {
                // Get Client Exception List From Database and cache it since we need to check it
                if (false === $allowedFamilyException = container()->get('redis')->get(Constant::trackerAllowedClientExceptionList)) {
                    $allowedFamilyException = container()->get('dbal')->prepare('SELECT * FROM `agent_allowed_exception`')->fetchAll();
                    container()->get('redis')->set(Constant::trackerAllowedClientExceptionList, $allowedFamilyException, 86400);
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
                container()->get('redis')->zAdd(Constant::trackerValidClientZset, time() + rand(7200, 18000), $client_identity);
                // container()->get('redis')->rawCommand('bf.add', [Constant::trackerValidClientZset, $client_identity]);
            }
        } else {
            throw new TrackerException(126, [':ua' => $userAgent]);
        }
    }

    /** Check Passkey Exist and Valid First, And We Get This Account Info
     * @param Request $request
     * @return array
     * @throws TrackerException
     */
    protected function checkPasskey(Request $request): array
    {
        // First Check The param `passkey` is exist and valid
        if (!$request->query->has('passkey')) {
            throw new TrackerException(130, [':attribute' => 'passkey']);
        }

        $passkey = $request->query->get('passkey');
        if (strlen($passkey) != 32) {
            throw new TrackerException(132, [':attribute' => 'passkey', ':rule' => 32]);
        }
        if (strspn(strtolower($passkey), 'abcdef0123456789') != 32) {  // MD5 char limit
            throw new TrackerException(131, [':attribute' => 'passkey', ':reason' => 'The format of passkey isn\'t correct']);
        }

        // Get userInfo from RedisConnection Cache and then Database
        if (false === $userInfo = container()->get('redis')->get(Constant::userBaseContentByPasskey($passkey))) {
            $userInfo = container()->get('dbal')->prepare('SELECT `id`, `status`, `passkey`, `downloadpos`, `class`, `uploaded`, `downloaded` FROM `users` WHERE `passkey` = :passkey LIMIT 1')
                ->bindParams(['passkey' => $passkey])->fetchOne() ?: [];

            // Notice: We log empty array in Redis Cache if userInfo not find in our Database
            container()->get('redis')->set(Constant::userBaseContentByPasskey($passkey), $userInfo, 3600 + rand(0, 300));
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
        return $userInfo;
    }

    /**
     * @param $hash
     * @param bool $scrape
     * @return array|bool return the required field of torrent info by it's info_hash , or false when this info_hash is
     *                    invalid (Not exist in our database or this status is 'deleted'
     */
    protected function getTorrentInfoByHash($hash, bool $scrape = false)
    {
        $bin2hex_hash = bin2hex($hash);

        // If Cache is not exist , We will get User info from Database
        if (false === $torrentInfo = container()->get('redis')->get(Constant::trackerTorrentContentByInfoHash($bin2hex_hash))) {
            $torrentInfo = container()->get('dbal')->prepare('SELECT `id`, `info_hash`, `owner_id`, `status`, `incomplete`, `complete`, `downloaded`, `added_at` FROM `torrents` WHERE `info_hash` = :info LIMIT 1')
                ->bindParams(['info' => $hash])->fetchOne();
            if ($torrentInfo === false || $torrentInfo['status'] == 'deleted') {  // No-exist or deleted torrent
                $torrentInfo = [];
            }

            // Notice: We log empty array in Redis Cache if torrentInfo not find in our Database or in deleted status
            container()->get('redis')->set(Constant::trackerTorrentContentByInfoHash($bin2hex_hash), $torrentInfo, 600 + rand(0, 50));
        }

        // Return false when this info_hash is invalid (Not exist in our database or this status is 'deleted'
        if (empty($torrentInfo)) {
            return false;
        }

        // Return limit field when in scrape model
        return $scrape ? Arr::only($torrentInfo, ['incomplete', 'complete', 'downloaded']) : $torrentInfo;
    }

    /**
     * @param Request $request
     * @return array
     * @throws TrackerException
     */
    private function checkScrapeFields(Request $request): array
    {
        preg_match_all('/info_hash=([^&]*)/i', urldecode($request->getQueryString()), $info_hash_match);

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
        return $info_hash_array;
    }

    private function generateScrapeResponse($info_hash_array)
    {
        $torrent_details = [];
        foreach ($info_hash_array as $item) {
            $metadata = $this->getTorrentInfoByHash($item, true);
            if ($metadata !== false) {
                $torrent_details[$item] = $metadata;
            }  // Append it to tmp array only it exist.
        }

        return ['files' => $torrent_details];
    }
}
