-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2019 at 03:00 PM
-- Server version: 8.0.16
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `ridpt`
--
CREATE DATABASE IF NOT EXISTS `ridpt` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `ridpt`;

-- --------------------------------------------------------

--
-- Table structure for table `agent_allowed_exception`
--

DROP TABLE IF EXISTS `agent_allowed_exception`;
CREATE TABLE IF NOT EXISTS `agent_allowed_exception` (
  `family_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `peer_id` varchar(20) NOT NULL,
  `agent` varchar(100) NOT NULL,
  `comment` varchar(200) NOT NULL DEFAULT '',
  KEY `family_id` (`family_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `agent_allowed_exception`:
--

--
-- Truncate table before insert `agent_allowed_exception`
--

TRUNCATE TABLE `agent_allowed_exception`;
-- --------------------------------------------------------

--
-- Table structure for table `agent_allowed_family`
--

DROP TABLE IF EXISTS `agent_allowed_family`;
CREATE TABLE IF NOT EXISTS `agent_allowed_family` (
  `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `family` varchar(50) NOT NULL DEFAULT '',
  `start_name` varchar(100) NOT NULL DEFAULT '',
  `peer_id_pattern` varchar(200) NOT NULL,
  `peer_id_match_num` tinyint(3) UNSIGNED NOT NULL,
  `peer_id_matchtype` enum('dec','hex') NOT NULL DEFAULT 'dec',
  `peer_id_start` varchar(20) NOT NULL,
  `agent_pattern` varchar(200) NOT NULL,
  `agent_match_num` tinyint(3) UNSIGNED NOT NULL,
  `agent_matchtype` enum('dec','hex') NOT NULL DEFAULT 'dec',
  `agent_start` varchar(100) NOT NULL,
  `exception` enum('yes','no') NOT NULL DEFAULT 'no',
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `comment` varchar(200) NOT NULL DEFAULT '',
  `hits` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `agent_allowed_family`:
--

--
-- Truncate table before insert `agent_allowed_family`
--

TRUNCATE TABLE `agent_allowed_family`;
--
-- Dumping data for table `agent_allowed_family`
--

INSERT INTO `agent_allowed_family` (`id`, `family`, `start_name`, `peer_id_pattern`, `peer_id_match_num`, `peer_id_matchtype`, `peer_id_start`, `agent_pattern`, `agent_match_num`, `agent_matchtype`, `agent_start`, `exception`, `enabled`, `comment`, `hits`) VALUES
(1, 'uTorrent 1.6.1', 'uTorrent 1.6.1', '/^-UT1610-/', 0, 'dec', '-UT1610-', '/^uTorrent\\/1610/', 0, 'dec', 'uTorrent/1610', 'no', 'yes', '', 0),
(2, 'uTorrent 1.7.x', 'uTorrent 1.7.5', '/^-UT17([0-9])([0-9])-/', 2, 'dec', '-UT1750-', '/^uTorrent\\/17([0-9])([0-9])/', 2, 'dec', 'uTorrent/1750', 'no', 'yes', '', 0),
(3, 'uTorrent 1.8.x', 'uTorrent 1.8.0', '/^-UT18([0-9])([0-9])-/', 2, 'dec', '-UT1800-', '/^uTorrent\\/18([0-9])([0-9])/', 2, 'dec', 'uTorrent/1800', 'no', 'yes', '', 0),
(4, 'uTorrent 2.x.x', 'uTorrent 2.0', '/^-UT2([0-9])([0-9])([0-9])-/', 3, 'dec', '-UT2000-', '/^uTorrent\\/2([0-9])([0-9])([0-9])/', 3, 'dec', 'uTorrent/2000', 'no', 'yes', '', 0),
(5, 'uTorrent 3.0', 'uTorrent 3.0.0.0', '/^-UT3([0-9])([0-9])([0-9])-/', 3, 'dec', '-UT3000-', '/^uTorrent\\/3([0-9])([0-9])/', 2, 'dec', 'uTorrent/3000', 'no', 'yes', '', 0),
(6, 'uTorrent 3.4.x', 'uTorrent 3.4.0', '/^-UT34([0-9])-/', 1, 'dec', '-UT340-', '/^uTorrent\\/34([0-9])/', 1, 'dec', 'uTorrent/3400', 'no', 'yes', '', 0),
(7, 'uTorrent 3.5', 'uTorrent 3.5.0', '/^-UT35([0-9])([0-9A-Za-z])-/', 2, 'dec', '-UT3500-', '/^uTorrent\\/35([0-9])/', 1, 'dec', 'uTorrent/350', 'no', 'yes', '', 0),
(8, 'uTorrentMac 1.0', 'uTorrentMac 1.0.0.0', '/^-UM1([0-9])([0-9])([0-9B])-/', 3, 'dec', '-UM1000-', '/^uTorrentMac\\/1([0-9])([0-9])([0-9B])/', 3, 'dec', 'uTorrentMac/1000', 'no', 'yes', '', 0),
(9, 'qBittorrent 3.x', 'qBittorrent 3.0.0', '/^-qB3([0-3])([0-9A-G])0-/', 2, 'hex', '-qB30A0-', '/^qBittorrent(\\/| v)3\\.([0-3])\\.([0-9]|[1-2][0-9])/', 3, 'dec', 'qBittorrent/3.0.0', 'no', 'yes', '', 0),
(10, 'qBittorrent/4.x', 'qBittorrent/4.0.2', '/^-qB4([0-9])([0-9A-G])([0-9])-/', 3, 'hex', '-qB4020-', '/^qBittorrent\\/4\\.([0-9])\\.([0-9])/', 2, 'dec', 'qBittorrent/4.0.2', 'no', 'yes', '', 0),
(11, 'Transmission 1.x', 'Transmission 1.06 (build 5136)', '/^-TR1([0-9])([0-9])([0-9])-/', 3, 'dec', '-TR1060-', '/^Transmission\\/1\\.([0-9])([0-9])/', 2, 'dec', 'Transmission/1.06', 'no', 'yes', '', 0),
(12, 'Transmission 2.x', 'Transmission 2.0.0', '/^-TR2([0-9])([0-9])([0-9])-/', 3, 'dec', '-TR2000-', '/^Transmission\\/2\\.([0-9])([0-9])/', 2, 'dec', 'Transmission/2.00', 'no', 'yes', '', 0),
(13, 'Deluge 1.3.x', 'Deluge 1.3.0', '/^-DE1([3-9])([0-F])([0-F])-/', 3, 'dec', '-DE13F0-', '/^Deluge 1\\.([0-9])\\.([0-9])/', 2, 'dec', 'Deluge 1.3.0', 'no', 'yes', '', 0),
(14, 'rTorrent 0.x(with libtorrent 0.x)', 'rTorrent 0.8.0 (with libtorrent 0.12.0)', '/^-lt([0-9A-E])([0-9A-E])([0-9A-E])([0-9A-E])-/', 4, 'hex', '-lt0C00-', '/^rtorrent\\/0\\.([0-9])\\.([0-9])\\/0\\.([1-9][0-9]*)\\.(0|[1-9][0-9]*)/', 4, 'dec', 'rtorrent/0.8.0/0.12.0', 'no', 'yes', '', 0),
(15, 'Azureus 4.x', 'Azureus 4.0.0.0', '/^-AZ4([0-9])([0-9])([0-9])-/', 3, 'dec', '-AZ4000-', '/^Azureus 4\\.([0-9])\\.([0-9])\\.([0-9])/', 3, 'dec', 'Azureus 4.0.0.0', 'no', 'yes', '', 0),
(16, 'Azureus 5.x', 'Azureus 5.0.0.0', '/^-AZ5([0-9])([0-9])([0-9])-/', 3, 'dec', '-AZ5000-', '/^Azureus 5\\.([0-9])\\.([0-9])\\.([0-9])/', 3, 'dec', 'Azureus 5.0.0.0', 'no', 'yes', '', 0),
(17, 'Bittorrent 6.x', 'Bittorrent 6.0.1', '/^M6-([0-9])-([0-9])--/', 2, 'dec', 'M6-0-1--', '/^BitTorrent\\/6([0-9])([0-9])([0-9])/', 3, 'dec', 'BitTorrent/6010', 'no', 'yes', '', 0),
(18, 'Bittorrent 7.x', 'Bittorrent 7.0.1', '/^-BT7([0-9])([0-9])([0-9])-/', 3, 'dec', '-BT7000-', '/^BitTorrent\\/7([0-9])([0-9])/', 2, 'dec', 'BitTorrent/7010', 'no', 'yes', '', 0),
(19, 'BittorrentMac 7.x', 'BittorrentMac 7.0.1', '/^M7-([0-9])-([0-9])--/', 2, 'dec', 'M7-0-1--', '/^BitTorrentMac\\/7([0-9])([0-9])([0-9])/', 3, 'dec', 'BitTorrentMac/7010', 'no', 'yes', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `agent_deny_log`
--

DROP TABLE IF EXISTS `agent_deny_log`;
CREATE TABLE IF NOT EXISTS `agent_deny_log` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tid` int(10) UNSIGNED NOT NULL,
  `uid` int(10) UNSIGNED NOT NULL,
  `user_agent` varchar(64) NOT NULL,
  `peer_id` varbinary(20) NOT NULL,
  `req_info` text NOT NULL,
  `msg` varchar(255) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_action_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `one_peer` (`tid`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `agent_deny_log`:
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

DROP TABLE IF EXISTS `bookmarks`;
CREATE TABLE IF NOT EXISTS `bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL,
  `tid` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UN_bookmarks_uid_tid` (`tid`,`uid`),
  KEY `IN_bookmarks_users_id` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `bookmarks`:
--   `tid`
--       `torrents` -> `id`
--   `uid`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `cheaters`
--

DROP TABLE IF EXISTS `cheaters`;
CREATE TABLE IF NOT EXISTS `cheaters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userid` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `torrentid` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `uploaded` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `downloaded` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `anctime` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `seeders` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `leechers` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `hit` smallint(3) UNSIGNED NOT NULL DEFAULT '0',
  `commit` varchar(255) NOT NULL DEFAULT '',
  `reviewed` tinyint(1) NOT NULL DEFAULT '0',
  `reviewed_by` mediumint(8) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_user_torrent_id` (`userid`,`torrentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `cheaters`:
--

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
CREATE TABLE IF NOT EXISTS `files` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `torrent_id` int(10) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `size` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `torrent_id` (`torrent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `files`:
--   `torrent_id`
--       `torrents` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `invite`
--

DROP TABLE IF EXISTS `invite`;
CREATE TABLE IF NOT EXISTS `invite` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `inviter_id` int(11) UNSIGNED NOT NULL,
  `hash` varchar(32) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expire_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `FK_invite_inviter_id` (`inviter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `invite`:
--   `inviter_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `ip_bans`
--

DROP TABLE IF EXISTS `ip_bans`;
CREATE TABLE IF NOT EXISTS `ip_bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) NOT NULL,
  `add_by` int(10) UNSIGNED NOT NULL,
  `add_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `commit` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `FK_ip_ban_operator` (`add_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `ip_bans`:
--   `add_by`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `receiver` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `add_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subject` varchar(128) NOT NULL DEFAULT '',
  `msg` text NOT NULL,
  `unread` enum('yes','no') NOT NULL DEFAULT 'yes',
  `location` smallint(6) NOT NULL DEFAULT '1',
  `saved` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `sender` (`sender`),
  KEY `receiver_read_status` (`receiver`,`unread`),
  KEY `receiver` (`receiver`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `messages`:
--

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edit_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `notify` tinyint(1) NOT NULL DEFAULT '1',
  `force_read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `create_at` (`create_at`),
  KEY `FK_news_users_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=COMPACT;

--
-- RELATIONSHIPS FOR TABLE `news`:
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `peers`
--

DROP TABLE IF EXISTS `peers`;
CREATE TABLE IF NOT EXISTS `peers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `torrent_id` int(11) NOT NULL,
  `peer_id` varbinary(20) NOT NULL,
  `ip` varbinary(16) DEFAULT NULL,
  `port` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `ipv6` varbinary(16) DEFAULT NULL,
  `ipv6_port` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `seeder` enum('yes','partial','no') NOT NULL DEFAULT 'no',
  `uploaded` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `downloaded` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `to_go` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `finished` tinyint(1) NOT NULL DEFAULT '0',
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_action_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finish_at` timestamp NULL DEFAULT NULL,
  `agent` varchar(64) NOT NULL,
  `corrupt` tinyint(1) NOT NULL DEFAULT '0',
  `key` varchar(8) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_peer` (`user_id`,`torrent_id`,`peer_id`),
  KEY `role` (`seeder`),
  KEY `user_id` (`user_id`) USING HASH,
  KEY `torrent_id` (`torrent_id`),
  KEY `peer_id` (`peer_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `peers`:
--

-- --------------------------------------------------------

--
-- Table structure for table `site_config`
--

DROP TABLE IF EXISTS `site_config`;
CREATE TABLE IF NOT EXISTS `site_config` (
  `name` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='The site Config Table';

--
-- RELATIONSHIPS FOR TABLE `site_config`:
--

--
-- Truncate table before insert `site_config`
--

TRUNCATE TABLE `site_config`;
--
-- Dumping data for table `site_config`
--

INSERT INTO `site_config` (`name`, `value`) VALUES
('authority.manage_news', '80'),
('authority.pass_tracker_upspeed_check', '60'),
('authority.route_admin_index', '60'),
('authority.route_admin_service', '90'),
('authority.see_anonymous_uploader', '40'),
('authority.see_banned_torrent', '40'),
('authority.see_extend_debug_log', '90'),
('authority.see_pending_torrent', '40'),
('authority.upload_anonymous', '5'),
('base.enable_extend_debug', '1'),
('base.enable_register_system', '1'),
('base.enable_tracker_system', '1'),
('base.max_news_sum', '5'),
('base.max_per_user_session', '10'),
('base.max_user', '5000'),
('base.site_author', 'Rhilip'),
('base.site_copyright', 'RidPT Group'),
('base.site_css_update_date', '201903100001'),
('base.site_description', 'A Private Tracker Site Demo powered by RidPT'),
('base.site_email', 'admin@ridpt.top'),
('base.site_generator', 'RidPT'),
('base.site_keywords', 'RidPT,Private Tracker'),
('base.site_muti_tracker_url', ''),
('base.site_name', 'RidPT'),
('base.site_tracker_url', 'ridpt.rhilip.info/tracker'),
('base.site_url', 'ridpt.rhilip.info'),
('buff.enable_large', '1'),
('buff.enable_magic', '1'),
('buff.enable_mod', '1'),
('buff.enable_random', '1'),
('buff.large_size', '107374182400'),
('buff.large_type', 'Free'),
('buff.random_percent_2x', '2'),
('buff.random_percent_2x50%', '0'),
('buff.random_percent_2xfree', '1'),
('buff.random_percent_30%', '0'),
('buff.random_percent_50%', '5'),
('buff.random_percent_free', '2'),
('register.by_green', '0'),
('register.by_invite', '1'),
('register.by_open', '1'),
('register.check_email_blacklist', '1'),
('register.check_email_whitelist', '1'),
('register.check_max_ip', '1'),
('register.check_max_user', '1'),
('register.email_black_list', '@test.com'),
('register.email_white_list', '@gmail.com'),
('register.per_ip_user', '5'),
('register.user_confirm_way', 'auto'),
('register.user_default_bonus', '0'),
('register.user_default_class', '1'),
('register.user_default_downloaded', '0'),
('register.user_default_downloadpos', '1'),
('register.user_default_leechtime', '0'),
('register.user_default_seedtime', '0'),
('register.user_default_status', 'pending'),
('register.user_default_uploaded', '0'),
('register.user_default_uploadpos', '1'),
('security.max_login_attempts', '10'),
('torrent.max_file_size', '3145728'),
('torrent.max_nfo_size', '65535'),
('tracker.cheater_check', '1'),
('tracker.enable_announce', '1'),
('tracker.enable_maxdlsystem', '1'),
('tracker.enable_scrape', '1'),
('tracker.enable_upspeed_check', '1'),
('tracker.enable_waitsystem', '0'),
('tracker.interval', '450'),
('tracker.min_interval', '60'),
('tracker.user_max_leech', '1'),
('tracker.user_max_seed', '3');

-- --------------------------------------------------------

--
-- Table structure for table `site_crontab`
--

DROP TABLE IF EXISTS `site_crontab`;
CREATE TABLE IF NOT EXISTS `site_crontab` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `job` varchar(64) NOT NULL,
  `priority` int(10) UNSIGNED NOT NULL DEFAULT '100' COMMENT '0 - disable this crontab work, else the lower number job have higher priority, by default 100',
  `job_interval` int(11) NOT NULL,
  `last_run_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `next_run_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IN_site_crontab_priority_next_run_at` (`priority`,`next_run_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `site_crontab`:
--

--
-- Truncate table before insert `site_crontab`
--

TRUNCATE TABLE `site_crontab`;
--
-- Dumping data for table `site_crontab`
--

INSERT INTO `site_crontab` (`id`, `job`, `priority`, `job_interval`) VALUES
(1, 'clean_dead_peer', 1, 600);

-- --------------------------------------------------------

--
-- Table structure for table `site_log`
--

DROP TABLE IF EXISTS `site_log`;
CREATE TABLE IF NOT EXISTS `site_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `msg` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `level` enum('normal','mod','sysop','leader') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `site_log`:
--

-- --------------------------------------------------------

--
-- Table structure for table `snatched`
--

DROP TABLE IF EXISTS `snatched`;
CREATE TABLE IF NOT EXISTS `snatched` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `torrent_id` int(11) UNSIGNED NOT NULL,
  `agent` varchar(60) NOT NULL,
  `port` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `true_uploaded` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `true_downloaded` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `this_uploaded` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `this_download` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `to_go` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `seed_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `leech_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `finished` enum('yes','no') NOT NULL DEFAULT 'no',
  `finish_ip` varchar(40) DEFAULT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_action_at` timestamp NULL DEFAULT NULL,
  `finish_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `one_snatched` (`user_id`,`torrent_id`) USING BTREE,
  KEY `FK_snatched_torrentid` (`torrent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `snatched`:
--   `torrent_id`
--       `torrents` -> `id`
--   `user_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `torrents`
--

DROP TABLE IF EXISTS `torrents`;
CREATE TABLE IF NOT EXISTS `torrents` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) UNSIGNED NOT NULL,
  `info_hash` varbinary(20) NOT NULL,
  `status` enum('deleted','banned','pending','confirmed') NOT NULL DEFAULT 'confirmed',
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `complete` int(11) NOT NULL DEFAULT '0' COMMENT 'The number of active peers that have completed downloading.',
  `incomplete` int(11) NOT NULL DEFAULT '0' COMMENT 'The number of active peers that have not completed downloading.',
  `downloaded` int(11) NOT NULL DEFAULT '0' COMMENT 'The number of peers that have ever completed downloading.',
  `title` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `category` mediumint(5) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `torrent_name` varchar(255) NOT NULL DEFAULT '',
  `torrent_type` enum('single','multi') NOT NULL DEFAULT 'multi',
  `torrent_size` bigint(20) NOT NULL DEFAULT '0',
  `torrent_structure` json NOT NULL,
  `descr` text,
  `uplver` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  UNIQUE KEY `info_hash` (`info_hash`),
  KEY `FK_torrent_categories` (`category`),
  KEY `FK_torrent_owner` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=COMPACT;

--
-- RELATIONSHIPS FOR TABLE `torrents`:
--   `category`
--       `torrents_categories` -> `id`
--   `owner_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `torrents_buff`
--

DROP TABLE IF EXISTS `torrents_buff`;
CREATE TABLE IF NOT EXISTS `torrents_buff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrent_id` int(11) UNSIGNED NOT NULL COMMENT '`0` means all torrent',
  `operator_id` mediumint(8) UNSIGNED NOT NULL COMMENT '`0` means system',
  `beneficiary_id` mediumint(8) UNSIGNED NOT NULL COMMENT '`0` means all users',
  `buff_type` enum('random','large','mod','magic') NOT NULL DEFAULT 'magic',
  `ratio_type` enum('Normal','Free','2X','2X Free','50%','2X 50%','30%','Other') NOT NULL DEFAULT 'Normal',
  `upload_ratio` decimal(4,2) NOT NULL DEFAULT '1.00',
  `download_ratio` decimal(4,2) NOT NULL DEFAULT '1.00',
  `add_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `start_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expired_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `t_buff_index` (`beneficiary_id`,`torrent_id`,`start_at`,`expired_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `torrents_buff`:
--

-- --------------------------------------------------------

--
-- Table structure for table `torrents_categories`
--

DROP TABLE IF EXISTS `torrents_categories`;
CREATE TABLE IF NOT EXISTS `torrents_categories` (
  `id` mediumint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `torrents_categories`:
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(16) NOT NULL,
  `password` varchar(60) NOT NULL,
  `opt` varchar(40) DEFAULT NULL,
  `email` varchar(80) NOT NULL,
  `status` enum('banned','pending','parked','confirmed') NOT NULL DEFAULT 'pending',
  `class` smallint(6) UNSIGNED NOT NULL DEFAULT '1',
  `passkey` varchar(32) NOT NULL,
  `invite_by` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `register_ip` varbinary(16) NOT NULL,
  `uploadpos` tinyint(1) NOT NULL DEFAULT '1',
  `downloadpos` tinyint(1) NOT NULL DEFAULT '1',
  `uploaded` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `downloaded` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `seedtime` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `leechtime` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `last_login_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_access_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_upload_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_download_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_connect_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_login_ip` varbinary(16) DEFAULT NULL,
  `last_access_ip` varbinary(16) DEFAULT NULL,
  `last_tracker_ip` varbinary(16) DEFAULT NULL,
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `bonus_seeding` decimal(20,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `bonus_invite` decimal(20,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `bonus_other` decimal(20,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `lang` varchar(10) NOT NULL DEFAULT 'en',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `passkey` (`passkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `users`:
--

-- --------------------------------------------------------

--
-- Table structure for table `users_confirm`
--

DROP TABLE IF EXISTS `users_confirm`;
CREATE TABLE IF NOT EXISTS `users_confirm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) UNSIGNED NOT NULL,
  `serect` varchar(64) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `serect` (`serect`),
  KEY `FK_confirm_user_id` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `users_confirm`:
--   `uid`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `users_session_log`
--

DROP TABLE IF EXISTS `users_session_log`;
CREATE TABLE IF NOT EXISTS `users_session_log` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(10) UNSIGNED NOT NULL,
  `sid` varchar(64) NOT NULL,
  `login_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `login_ip` varbinary(16) NOT NULL,
  `user_agent` varchar(512) NOT NULL DEFAULT '',
  `last_access_at` timestamp NOT NULL,
  `expired` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sid` (`sid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `users_session_log`:
--   `uid`
--       `users` -> `id`
--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `site_log`
--
ALTER TABLE `site_log` ADD FULLTEXT KEY `msg` (`msg`);

--
-- Indexes for table `torrents`
--
ALTER TABLE `torrents` ADD FULLTEXT KEY `descr` (`descr`);
ALTER TABLE `torrents` ADD FULLTEXT KEY `name` (`title`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `FK_bookmarks_torrents_id` FOREIGN KEY (`tid`) REFERENCES `torrents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_bookmarks_users_id` FOREIGN KEY (`uid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `FK_files_torrents_id` FOREIGN KEY (`torrent_id`) REFERENCES `torrents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `invite`
--
ALTER TABLE `invite`
  ADD CONSTRAINT `FK_invite_inviter_id` FOREIGN KEY (`inviter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ip_bans`
--
ALTER TABLE `ip_bans`
  ADD CONSTRAINT `FK_ip_ban_operator` FOREIGN KEY (`add_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `FK_news_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `snatched`
--
ALTER TABLE `snatched`
  ADD CONSTRAINT `FK_snatched_torrentid` FOREIGN KEY (`torrent_id`) REFERENCES `torrents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_snatched_userid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `torrents`
--
ALTER TABLE `torrents`
  ADD CONSTRAINT `FK_torrent_categories` FOREIGN KEY (`category`) REFERENCES `torrents_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_torrent_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users_confirm`
--
ALTER TABLE `users_confirm`
  ADD CONSTRAINT `FK_confirm_user_id` FOREIGN KEY (`uid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users_session_log`
--
ALTER TABLE `users_session_log`
  ADD CONSTRAINT `FK_session_user_id` FOREIGN KEY (`uid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
