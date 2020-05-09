<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/28/2019
 * Time: 5:04 PM
 */

namespace App\Components;

use App\Entity;
use App\Libraries\Constant;

use Rid\Helpers\ContainerHelper;
use Rid\Utils\Traits\ClassValueCache;

class Site
{
    use ClassValueCache;

    protected Entity\User\UserFactory $user_factory;
    protected Entity\Torrent\TorrentFactory $torrent_factory;

    public function __construct(Entity\User\UserFactory $user_factory, Entity\Torrent\TorrentFactory $torrent_factory)
    {
        $this->user_factory = $user_factory;
        $this->torrent_factory = $torrent_factory;
    }

    /**
     * @return Entity\User\UserFactory
     */
    public function getUserFactory(): Entity\User\UserFactory
    {
        return $this->user_factory;
    }

    /**
     * @return Entity\Torrent\TorrentFactory
     */
    public function getTorrentFactory(): Entity\Torrent\TorrentFactory
    {
        return $this->torrent_factory;
    }

    protected function getCacheNameSpace(): string
    {
        return 'Site:hash:runtime_value';
    }

    public function getTorrent($tid)
    {
        return $this->torrent_factory->getTorrentById($tid);
    }

    /**
     * @param int $uid
     * @return Entity\User\User|bool return False means this user is not exist
     */
    public function getUser($uid)
    {
        return $this->user_factory->getUserById($uid);
    }

    public function writeLog($msg, $level = Entity\Site\LogLevel::LOG_LEVEL_NORMAL)
    {
        \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('INSERT INTO `site_log`(`create_at`,`msg`, `level`) VALUES (CURRENT_TIMESTAMP, :msg, :level)')->bindParams([
            'msg' => $msg, 'level' => $level
        ])->execute();
    }

    public function sendPM($sender, $receiver, $subject, $msg, $save = 'no', $location = 1)
    {
        \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('INSERT INTO `messages` (`sender`,`receiver`,`add_at`, `subject`, `msg`, `saved`, `location`) VALUES (:sender,:receiver,`CURRENT_TIMESTAMP`,:subject,:msg,:save,:location)')->bindParams([
            'sender' => $sender, 'receiver' => $receiver,
            'subject' => $subject, 'msg' => $msg,
            'save' => $save, 'location' => $location
        ])->execute();

        \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->hDel(Constant::userContent($receiver), 'unread_message_count', 'inbox_count');
        if ($sender != 0) {
            \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->hDel(Constant::userContent($sender), 'outbox_count');
        }
    }

    public function sendEmail($receivers, $subject, $template, $data = [])
    {
        $container = ContainerHelper::getContainer();
        $mail_body = $container->get('view')->render($template, $data);
        $mail_sender = $container->get('mailer');
        $mail_sender->send($receivers, $subject, $mail_body);
    }

    public function addBonus(int $user_id, float $point = 0, string $operators = '+', string $type = 'other')
    {
        if ($point > 0 && in_array($operators, ['+', '-']) /* Limit operator */) {
            $col = ($type == 'seeding') ? 'bonus_seeding' : 'bonus_other';

            \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare("UPDATE users SET $col = $col $operators :bonus WHERE id = :uid")->bindParams([
                'bonus' => $point, 'uid' => $user_id
            ])->execute();
        }
    }

    public function getQualityTableList()
    {
        return [
            'audio' => 'Audio Codec',  // TODO i18n title
            'codec' => 'Codec',
            'medium' => 'Medium',
            'resolution' => 'Resolution'
        ];
    }

    public function ruleCategory(): array
    {
        if (false === $cats = config('runtime.enabled_torrent_category')) {
            $cats = [];
            $cats_raw = \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('SELECT * FROM `categories` WHERE `id` > 0 ORDER BY `full_path`')->queryAll();

            foreach ($cats_raw as $cat_raw) {
                $cats[$cat_raw['id']] = $cat_raw;
            }
            \Rid\Helpers\ContainerHelper::getContainer()->get('config')->set('runtime.enabled_torrent_category', $cats, 'json');
        }

        return $cats ?: [];
    }

    public function CategoryDetail($cat_id): array
    {
        return $this->ruleCategory()[$cat_id];
    }

    public function ruleCanUsedCategory(): array
    {
        return array_filter($this->ruleCategory(), function ($cat) {
            return $cat['enabled'] = 1;
        });
    }

    public function ruleQuality($quality): array
    {
        if (!in_array($quality, array_keys($this->getQualityTableList()))) {
            throw new \RuntimeException('Unregister quality : ' . $quality);
        }
        if (false === $data = config('runtime.enabled_quality_' . $quality)) {
            $data = [];

            /** @noinspection SqlResolve */
            $data_raws = \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare("SELECT * FROM `quality_$quality` WHERE `id` > 0 AND `enabled` = 1 ORDER BY `sort_index`,`id`")->queryAll();
            foreach ($data_raws as $data_raw) {
                $data[$data_raw['id']] = $data_raw;
            }
            \Rid\Helpers\ContainerHelper::getContainer()->get('config')->set('runtime.enabled_quality_' . $quality, $data, 'json');
        }
        return $data ?: [];
    }

    public function ruleTeam(): array
    {
        if (false === $data = config('runtime.enabled_teams')) {
            $data = [];
            $data_raws = \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('SELECT * FROM `teams` WHERE `id` > 0 AND `enabled` = 1 ORDER BY `sort_index`,`id`')->queryAll();
            foreach ($data_raws as $data_raw) {
                $data[$data_raw['id']] = $data_raw;
            }
            \Rid\Helpers\ContainerHelper::getContainer()->get('config')->set('runtime.enabled_teams', $data, 'json');
        }

        return $data ?: [];
    }

    /**
     * @return array like [<tag1> => <tag1_class_name>, <tag2> => <tag2_class_name>]
     */
    public function rulePinnedTags(): array
    {
        if (false === $data = config('runtime.pinned_tags')) {
            $raw = \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('SELECT `tag`, `class_name` FROM `tags` WHERE `pinned` = 1;')->queryAll();
            $data = array_column($raw, 'class_name', 'tag');
            \Rid\Helpers\ContainerHelper::getContainer()->get('config')->set('runtime.pinned_tags', $data, 'json');
        }

        return $data;
    }

    public function getBanIpsList(): array
    {
        if (false === $ban_ips = config('runtime.ban_ips_list')) {
            $ban_ips = \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('SELECT `ip` FROM `ban_ips`')->queryColumn() ?: [];
            \Rid\Helpers\ContainerHelper::getContainer()->get('config')->set('runtime.ban_ips_list', $ban_ips, 'json');
        }

        return $ban_ips;
    }

    public function banIp($ip, $persistence = false, $commit = null)
    {
        // Get old ban_ips_list
        $banips = $this->getBanIpsList();

        // Add ip if not exist
        if (in_array($ip, $banips)) {
            return;
        }

        // Rewrite config
        $banips[] = $ip;
        \Rid\Helpers\ContainerHelper::getContainer()->get('config')->set('runtime.ban_ips_list', $banips, 'json');

        if ($persistence === true) {  // Save it in table `ban_ips`
            $add_by = app()->auth->getCurUser() ? app()->auth->getCurUser()->getId() : 0;  // 0 - system
            $commit = $commit ?? ($add_by == 0 ? 'Banned By System automatically' : '');
            \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('INSERT INTO `ban_ips`(`ip`, `add_by`, `add_at`, `commit`) VALUES (:ip, :add_by, NOW(), :commit)')->bindParams([
                'ip' => $ip, 'add_by' => $add_by, 'commit' => $commit
            ])->execute();
        }
    }

    public function unbanIp($ip, $persistence = false) // TODO Move to manager Form
    {
        // Get old ban_ips_list
        $banips = $this->getBanIpsList();

        // unban ip if exist
        if (in_array($ip, $banips)) {
            unset($banips[$ip]);
            \Rid\Helpers\ContainerHelper::getContainer()->get('config')->set('runtime.ban_ips_list', $banips, 'json');

            if ($persistence === true) {  // delete it from table `ban_ips`
                \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('DELETE FROM `ban_ips` WHERE `ip` = :ip')->bindParams(['ip' => $ip])->execute();
            }
        }
    }

    public function fetchUserCount(): int
    {
        return \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('SELECT COUNT(`id`) FROM `users`')->queryScalar();
    }
}
