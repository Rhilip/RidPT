-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 10, 2020 at 10:28 PM
-- Server version: 8.0.17
-- PHP Version: 7.3.7

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
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_action_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `one_peer` (`tid`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `agent_deny_log`:
--

-- --------------------------------------------------------

--
-- Table structure for table `ban_emails`
--

DROP TABLE IF EXISTS `ban_emails`;
CREATE TABLE IF NOT EXISTS `ban_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `ban_emails`:
--

-- --------------------------------------------------------

--
-- Table structure for table `ban_ips`
--

DROP TABLE IF EXISTS `ban_ips`;
CREATE TABLE IF NOT EXISTS `ban_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) NOT NULL,
  `add_by` int(10) UNSIGNED NOT NULL,
  `add_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `commit` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `ban_ips`:
--

-- --------------------------------------------------------

--
-- Table structure for table `ban_usernames`
--

DROP TABLE IF EXISTS `ban_usernames`;
CREATE TABLE IF NOT EXISTS `ban_usernames` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(16) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `ban_usernames`:
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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` mediumint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` mediumint(5) NOT NULL DEFAULT '0',
  `name` varchar(30) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `image` varchar(255) NOT NULL DEFAULT '',
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `level` smallint(6) NOT NULL DEFAULT '0',
  `full_path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `full_path` (`full_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `categories`:
--

--
-- Truncate table before insert `categories`
--

TRUNCATE TABLE `categories`;
--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `enabled`, `image`, `class_name`, `level`, `full_path`) VALUES
(0, 0, 'root', 0, '', '', -1, ''),
(1, 0, 'Movies', 1, '', 'category-movie', 0, 'Movies'),
(2, 0, 'TV', 1, '', 'category-tv', 0, 'TV'),
(3, 0, 'Documentary', 1, '', 'category-documentary', 0, 'Documentary'),
(4, 0, 'Animation', 1, '', 'category-animation', 0, 'Animation'),
(5, 0, 'Sports', 1, '', 'category-sports', 0, 'Sports'),
(6, 0, 'Music', 1, '', 'category-music', 0, 'Music');

-- --------------------------------------------------------

--
-- Table structure for table `cheaters`
--

DROP TABLE IF EXISTS `cheaters`;
CREATE TABLE IF NOT EXISTS `cheaters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `added_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
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
-- Table structure for table `external_info`
--

DROP TABLE IF EXISTS `external_info`;
CREATE TABLE IF NOT EXISTS `external_info` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source` enum('douban','bangumi','imdb','steam','indienova','epic') NOT NULL,
  `sid` varchar(64) NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` json NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UN_links_source_sid` (`source`,`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `external_info`:
--

-- --------------------------------------------------------

--
-- Table structure for table `file_defender`
--

DROP TABLE IF EXISTS `file_defender`;
CREATE TABLE IF NOT EXISTS `file_defender` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` mediumint(5) NOT NULL,
  `rules` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `file_defender`:
--

--
-- Truncate table before insert `file_defender`
--

TRUNCATE TABLE `file_defender`;
--
-- Dumping data for table `file_defender`
--

INSERT INTO `file_defender` (`id`, `category_id`, `rules`) VALUES
(1, 0, '\\.torrent$'),
(2, 0, '\\.xv$'),
(3, 0, '\\.bhd$'),
(4, 0, '\\.q[sl]v$'),
(5, 0, '\\.ifox$'),
(6, 0, '\\.kux$'),
(7, 0, '\\.!ut$'),
(8, 0, '\\.url$'),
(9, 0, '\\.qdl2$'),
(10, 0, '\\.baiduyun.*downloading'),
(11, 0, '\\.BaiduPCS-Go-downloading'),
(12, 0, '\\.!bn$'),
(13, 0, '.*uTorrentPartFile'),
(14, 0, '^_+?padding_file_\\d+_'),
(15, 0, '^\\..*'),
(16, 0, '^~\\$'),
(17, 0, 'Thumbs\\.db$'),
(18, 0, 'desktop\\.ini$'),
(19, 0, 'RARBG\\.txt$'),
(20, 0, '\\.kz$'),
(21, 0, '\\.bt\\.[xl]?td$'),
(22, 0, '\\.DS_Store$');

-- --------------------------------------------------------

--
-- Table structure for table `invite`
--

DROP TABLE IF EXISTS `invite`;
CREATE TABLE IF NOT EXISTS `invite` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `inviter_id` int(11) UNSIGNED NOT NULL,
  `username` varchar(16) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `invite_type` enum('temporarily','permanent') NOT NULL DEFAULT 'temporarily',
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expire_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `used` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - wait to use ; 1 - used ; -1 - expired; -2 - recycled',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  UNIQUE KEY `username` (`username`),
  KEY `FK_invite_inviter_id` (`inviter_id`),
  KEY `used` (`used`),
  KEY `expire_at` (`expire_at`),
  KEY `IN_invite_id_create` (`id`,`create_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `invite`:
--   `inviter_id`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
CREATE TABLE IF NOT EXISTS `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `status` enum('pending','enabled','disabled') NOT NULL DEFAULT 'pending',
  `administrator` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(80) NOT NULL DEFAULT '',
  `reason` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `links`:
--

-- --------------------------------------------------------

--
-- Table structure for table `map_torrents_externalinfo`
--

DROP TABLE IF EXISTS `map_torrents_externalinfo`;
CREATE TABLE IF NOT EXISTS `map_torrents_externalinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrent_id` int(11) UNSIGNED NOT NULL,
  `info_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_map_tl_to_external_info` (`info_id`),
  KEY `FK_map_tl_to_torrents` (`torrent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `map_torrents_externalinfo`:
--   `info_id`
--       `external_info` -> `id`
--   `torrent_id`
--       `torrents` -> `id`
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
  `add_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
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
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edit_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `notify` tinyint(1) NOT NULL DEFAULT '1',
  `force_read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `create_at` (`create_at`),
  KEY `FK_news_users_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `corrupt` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `to_go` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `finished` tinyint(1) NOT NULL DEFAULT '0',
  `started_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_action_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `finish_at` timestamp NULL DEFAULT NULL,
  `agent` varchar(64) NOT NULL,
  `key` varchar(8) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_peer` (`user_id`,`torrent_id`,`peer_id`),
  KEY `role` (`seeder`),
  KEY `user_id` (`user_id`),
  KEY `torrent_id` (`torrent_id`),
  KEY `peer_id` (`peer_id`),
  KEY `last_action_at` (`last_action_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `peers`:
--

-- --------------------------------------------------------

--
-- Table structure for table `quality_audio`
--

DROP TABLE IF EXISTS `quality_audio`;
CREATE TABLE IF NOT EXISTS `quality_audio` (
  `id` mediumint(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `sort_index` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`,`sort_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `quality_audio`:
--

--
-- Truncate table before insert `quality_audio`
--

TRUNCATE TABLE `quality_audio`;
--
-- Dumping data for table `quality_audio`
--

INSERT INTO `quality_audio` (`id`, `name`, `enabled`, `sort_index`) VALUES
(0, 'None', 1, 0),
(1, 'Other', 1, 100),
(2, 'Atomos', 1, 0),
(3, 'DTS X', 1, 0),
(4, 'DTS-HDMA', 1, 0),
(5, 'TrueHD', 1, 0),
(6, 'DTS', 1, 0),
(7, 'LPCM', 1, 0),
(8, 'FLAC', 1, 0),
(9, 'APE', 1, 0),
(10, 'AAC', 1, 0),
(11, 'AC3', 1, 0),
(12, 'WAV', 1, 0),
(13, 'MPEG', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `quality_codec`
--

DROP TABLE IF EXISTS `quality_codec`;
CREATE TABLE IF NOT EXISTS `quality_codec` (
  `id` mediumint(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `sort_index` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`,`sort_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `quality_codec`:
--

--
-- Truncate table before insert `quality_codec`
--

TRUNCATE TABLE `quality_codec`;
--
-- Dumping data for table `quality_codec`
--

INSERT INTO `quality_codec` (`id`, `name`, `enabled`, `sort_index`) VALUES
(0, 'None', 1, 0),
(1, 'Other', 1, 100),
(2, 'H.264', 1, 0),
(3, 'HEVC', 1, 0),
(4, 'MPEG-2', 1, 0),
(5, 'VC-1', 1, 0),
(6, 'Xvid', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `quality_medium`
--

DROP TABLE IF EXISTS `quality_medium`;
CREATE TABLE IF NOT EXISTS `quality_medium` (
  `id` mediumint(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `sort_index` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`,`sort_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `quality_medium`:
--

--
-- Truncate table before insert `quality_medium`
--

TRUNCATE TABLE `quality_medium`;
--
-- Dumping data for table `quality_medium`
--

INSERT INTO `quality_medium` (`id`, `name`, `enabled`, `sort_index`) VALUES
(0, 'None', 1, 0),
(1, 'Other', 1, 100),
(2, 'UHD Blu-ray', 1, 0),
(3, 'FHD Blu-ray', 1, 0),
(4, 'Remux', 1, 0),
(5, 'Encode', 1, 0),
(6, 'WEB-DL', 1, 0),
(7, 'HDTV', 1, 0),
(8, 'DVD', 1, 0),
(9, 'CD', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `quality_resolution`
--

DROP TABLE IF EXISTS `quality_resolution`;
CREATE TABLE IF NOT EXISTS `quality_resolution` (
  `id` mediumint(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `sort_index` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`,`sort_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `quality_resolution`:
--

--
-- Truncate table before insert `quality_resolution`
--

TRUNCATE TABLE `quality_resolution`;
--
-- Dumping data for table `quality_resolution`
--

INSERT INTO `quality_resolution` (`id`, `name`, `enabled`, `sort_index`) VALUES
(0, 'None', 1, 0),
(1, 'Other', 1, 100),
(2, 'SD', 1, 0),
(3, '720p', 1, 0),
(4, '1080i', 1, 0),
(5, '1080p', 1, 0),
(6, '2160p', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(10) UNSIGNED NOT NULL,
  `session` varchar(64) NOT NULL,
  `login_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `login_ip` varbinary(16) NOT NULL,
  `expired` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sid` (`session`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `sessions`:
--   `uid`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `session_log`
--

DROP TABLE IF EXISTS `session_log`;
CREATE TABLE IF NOT EXISTS `session_log` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sid` int(10) UNSIGNED NOT NULL,
  `access_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `access_ip` varbinary(16) NOT NULL,
  `user_agent` varchar(512) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `FK_session_id` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `session_log`:
--   `sid`
--       `sessions` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `site_config`
--

DROP TABLE IF EXISTS `site_config`;
CREATE TABLE IF NOT EXISTS `site_config` (
  `name` varchar(255) NOT NULL,
  `type` enum('bool','int','double','string','json') NOT NULL DEFAULT 'string',
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

INSERT INTO `site_config` (`name`, `type`, `value`) VALUES
('authority.apply_for_links', 'int', '5'),
('authority.bypass_maintenance', 'int', '90'),
('authority.invite_manual_confirm', 'int', '70'),
('authority.invite_recycle_other_pending', 'int', '90'),
('authority.invite_recycle_self_pending', 'int', '70'),
('authority.manage_links', 'int', '80'),
('authority.manage_news', 'int', '80'),
('authority.manage_subtitles', 'int', '80'),
('authority.manage_torrents', 'int', '80'),
('authority.pass_invite_interval_check', 'int', '60'),
('authority.pass_tracker_upspeed_check', 'int', '60'),
('authority.see_anonymous_info', 'int', '60'),
('authority.see_banned_torrent', 'int', '40'),
('authority.see_extend_debug_log', 'int', '90'),
('authority.see_pending_torrent', 'int', '40'),
('authority.see_site_log_leader', 'int', '90'),
('authority.see_site_log_mod', 'int', '60'),
('authority.upload_flag_anonymous', 'int', '5'),
('authority.upload_flag_hr', 'int', '40'),
('authority.upload_nfo_file', 'int', '5'),
('base.enable_extend_debug', 'bool', '1'),
('base.enable_invite_system', 'bool', '1'),
('base.enable_register_system', 'bool', '1'),
('base.enable_tracker_system', 'bool', '1'),
('base.maintenance', 'bool', '0'),
('base.max_news_sum', 'int', '5'),
('base.max_per_user_session', 'int', '10'),
('base.max_user', 'int', '5000'),
('base.prevent_anonymous', 'bool', '0'),
('base.site_author', 'string', 'Rhilip'),
('base.site_copyright', 'string', 'RidPT Group'),
('base.site_css_update_date', 'string', '201903100001'),
('base.site_description', 'string', 'A Private Tracker Site Demo powered by RidPT'),
('base.site_email', 'string', 'admin@ridpt.top'),
('base.site_generator', 'string', 'RidPT'),
('base.site_keywords', 'string', 'RidPT,Private Tracker'),
('base.site_multi_tracker_behaviour', 'string', 'union'),
('base.site_multi_tracker_url', 'json', '[]'),
('base.site_name', 'string', 'RidPT'),
('base.site_tracker_url', 'string', 'ridpt.top/tracker'),
('base.site_url', 'string', 'ridpt.top'),
('buff.enable_large', 'bool', '1'),
('buff.enable_magic', 'bool', '1'),
('buff.enable_mod', 'bool', '1'),
('buff.enable_random', 'bool', '1'),
('buff.large_size', 'int', '107374182400'),
('buff.large_type', 'string', 'Free'),
('buff.random_percent_2x', 'int', '2'),
('buff.random_percent_2x50%', 'int', '0'),
('buff.random_percent_2xfree', 'int', '1'),
('buff.random_percent_30%', 'int', '0'),
('buff.random_percent_50%', 'int', '5'),
('buff.random_percent_free', 'int', '2'),
('gravatar.base_url', 'string', 'https://www.gravatar.com/avatar/'),
('gravatar.default_fallback', 'string', 'identicon'),
('gravatar.maximum_rating', 'string', 'g'),
('invite.force_interval', 'bool', '1'),
('invite.interval', 'int', '7200'),
('invite.recycle_invite_lifetime', 'int', '86400'),
('invite.recycle_return_invite', 'int', '1'),
('invite.timeout', 'int', '259200'),
('register.by_green', 'bool', '0'),
('register.by_invite', 'bool', '1'),
('register.by_open', 'bool', '1'),
('register.check_email_blacklist', 'bool', '1'),
('register.check_email_whitelist', 'bool', '1'),
('register.check_max_ip', 'bool', '1'),
('register.check_max_user', 'bool', '1'),
('register.email_black_list', 'json', '[\"@test.com\"]'),
('register.email_white_list', 'json', '[\"@gmail.com\"]'),
('register.per_ip_user', 'int', '5'),
('register.user_confirm_way', 'string', 'auto'),
('register.user_default_bonus', 'int', '0'),
('register.user_default_class', 'int', '1'),
('register.user_default_downloaded', 'int', '0'),
('register.user_default_downloadpos', 'int', '1'),
('register.user_default_invites', 'int', '0'),
('register.user_default_leechtime', 'int', '0'),
('register.user_default_seedtime', 'int', '0'),
('register.user_default_status', 'string', 'pending'),
('register.user_default_uploaded', 'int', '0'),
('register.user_default_uploadpos', 'int', '1'),
('route.admin_index', 'string', '60'),
('route.admin_service', 'string', '90'),
('security.auto_logout', 'int', '1'),
('security.max_login_attempts', 'int', '10'),
('security.secure_login', 'int', '0'),
('security.ssl_login', 'int', '2'),
('torrent_upload.allow_new_custom_tags', 'bool', '0'),
('torrent_upload.enable_anonymous', 'bool', '1'),
('torrent_upload.enable_hr', 'bool', '1'),
('torrent_upload.enable_quality_audio', 'bool', '1'),
('torrent_upload.enable_quality_codec', 'bool', '1'),
('torrent_upload.enable_quality_medium', 'bool', '1'),
('torrent_upload.enable_quality_resolution', 'bool', '1'),
('torrent_upload.enable_subtitle', 'bool', '1'),
('torrent_upload.enable_tags', 'bool', '1'),
('torrent_upload.enable_teams', 'bool', '1'),
('torrent_upload.enable_upload_nfo', 'bool', '1'),
('torrent_upload.rewrite_commit_to', 'string', ''),
('torrent_upload.rewrite_createdby_to', 'string', ''),
('torrent_upload.rewrite_source_to', 'string', ''),
('tracker.cheater_check', 'int', '1'),
('tracker.enable_announce', 'int', '1'),
('tracker.enable_maxdlsystem', 'int', '1'),
('tracker.enable_scrape', 'int', '1'),
('tracker.enable_upspeed_check', 'int', '1'),
('tracker.enable_waitsystem', 'int', '0'),
('tracker.force_compact_model', 'int', '0'),
('tracker.force_no_peer_id_model', 'int', '0'),
('tracker.interval', 'int', '450'),
('tracker.max_numwant', 'int', '50'),
('tracker.min_interval', 'int', '60'),
('tracker.retry_interval', 'int', '120'),
('tracker.user_max_leech', 'int', '1'),
('tracker.user_max_seed', 'int', '3'),
('upload.max_nfo_file_size', 'int', '65535'),
('upload.max_subtitle_file_size', 'int', '10485760'),
('upload.max_torrent_file_size', 'int', '3145728'),
('user.avatar_provider', 'string', 'gravatar');

-- --------------------------------------------------------

--
-- Table structure for table `site_crontab`
--

DROP TABLE IF EXISTS `site_crontab`;
CREATE TABLE IF NOT EXISTS `site_crontab` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `job` varchar(64) NOT NULL,
  `priority` int(10) UNSIGNED NOT NULL DEFAULT '100' COMMENT '0 - disable this crontab work, else the lower number job have higher priority, by default 100',
  `job_interval` int(11) UNSIGNED NOT NULL DEFAULT '600',
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
(1, 'clean_expired_zset_cache', 1, 60),
(2, 'clean_dead_peer', 1, 600),
(3, 'clean_expired_items_database', 3, 3600),
(4, 'calculate_seeding_bonus', 2, 900),
(5, 'sync_torrents_status', 4, 3600),
(6, 'update_expired_external_link_info', 100, 1200),
(7, 'sync_ban_list', 100, 86400);

-- --------------------------------------------------------

--
-- Table structure for table `site_log`
--

DROP TABLE IF EXISTS `site_log`;
CREATE TABLE IF NOT EXISTS `site_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `msg` text NOT NULL,
  `level` enum('normal','mod','leader') NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `ip` varbinary(16) DEFAULT NULL,
  `port` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `true_uploaded` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `true_downloaded` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `this_uploaded` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `this_download` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `to_go` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `seed_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `leech_time` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `finished` enum('yes','no') NOT NULL DEFAULT 'no',
  `finish_ip` varbinary(16) DEFAULT NULL,
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
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
-- Table structure for table `subtitles`
--

DROP TABLE IF EXISTS `subtitles`;
CREATE TABLE IF NOT EXISTS `subtitles` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `torrent_id` int(11) UNSIGNED NOT NULL,
  `hashs` varchar(32) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `added_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `size` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  `uppd_by` int(11) UNSIGNED NOT NULL,
  `anonymous` tinyint(1) NOT NULL DEFAULT '0',
  `hits` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `ext` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hashs` (`hashs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `subtitles`:
--

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tag` varchar(64) NOT NULL DEFAULT '',
  `class_name` varchar(64) NOT NULL DEFAULT '',
  `pinned` tinyint(1) NOT NULL DEFAULT '0',
  `count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`tag`),
  KEY `pinned` (`pinned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `tags`:
--

--
-- Truncate table before insert `tags`
--

TRUNCATE TABLE `tags`;
--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `tag`, `class_name`, `pinned`, `count`) VALUES
(1, 'Internal', 'label-primary', 1, 0),
(2, 'DIY', 'label-primary', 1, 0),
(3, 'Premiere', 'label-primary', 1, 0),
(4, 'Exclusive', 'label-primary', 1, 0),
(5, 'Request', 'label-primary', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
CREATE TABLE IF NOT EXISTS `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `class_require` smallint(6) UNSIGNED NOT NULL DEFAULT '40',
  `sort_index` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`,`sort_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `teams`:
--

--
-- Truncate table before insert `teams`
--

TRUNCATE TABLE `teams`;
--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `enabled`, `class_require`, `sort_index`) VALUES
(0, 'None', 1, 1, 0),
(1, 'Ohter', 1, 1, 100);

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
  `added_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `complete` int(11) NOT NULL DEFAULT '0' COMMENT 'The number of active peers that have completed downloading.',
  `incomplete` int(11) NOT NULL DEFAULT '0' COMMENT 'The number of active peers that have not completed downloading.',
  `downloaded` int(11) NOT NULL DEFAULT '0' COMMENT 'The number of peers that have ever completed downloading.',
  `comments` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `category` mediumint(5) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `torrent_name` varchar(255) NOT NULL DEFAULT '',
  `torrent_type` enum('single','multi') NOT NULL DEFAULT 'multi',
  `torrent_size` bigint(20) NOT NULL DEFAULT '0',
  `torrent_structure` json NOT NULL,
  `team` int(11) NOT NULL DEFAULT '0',
  `quality_audio` mediumint(3) NOT NULL DEFAULT '0',
  `quality_codec` mediumint(3) NOT NULL DEFAULT '0',
  `quality_medium` mediumint(3) NOT NULL DEFAULT '0',
  `quality_resolution` mediumint(3) NOT NULL DEFAULT '0',
  `descr` text,
  `tags` json NOT NULL,
  `nfo` blob NOT NULL,
  `uplver` tinyint(1) NOT NULL DEFAULT '0',
  `hr` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `info_hash` (`info_hash`),
  KEY `FK_torrent_categories` (`category`),
  KEY `FK_torrent_owner` (`owner_id`),
  KEY `FK_torrent_team` (`team`),
  KEY `FK_torrent_quality_audio` (`quality_audio`),
  KEY `FK_torrent_quality_codec` (`quality_codec`),
  KEY `FK_torrent_quality_medium` (`quality_medium`),
  KEY `FK_torrent_quality_resolution` (`quality_resolution`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `torrents`:
--   `category`
--       `categories` -> `id`
--   `owner_id`
--       `users` -> `id`
--   `quality_audio`
--       `quality_audio` -> `id`
--   `quality_codec`
--       `quality_codec` -> `id`
--   `quality_medium`
--       `quality_medium` -> `id`
--   `quality_resolution`
--       `quality_resolution` -> `id`
--   `team`
--       `teams` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `torrent_buffs`
--

DROP TABLE IF EXISTS `torrent_buffs`;
CREATE TABLE IF NOT EXISTS `torrent_buffs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrent_id` int(11) UNSIGNED NOT NULL COMMENT '`0` means all torrent',
  `operator_id` mediumint(8) UNSIGNED NOT NULL COMMENT '`0` means system',
  `beneficiary_id` mediumint(8) UNSIGNED NOT NULL COMMENT '`0` means all users',
  `buff_type` enum('random','large','mod','magic') NOT NULL DEFAULT 'magic',
  `ratio_type` enum('Normal','Free','2X','2X Free','50%','2X 50%','30%','Other') NOT NULL DEFAULT 'Normal',
  `upload_ratio` decimal(4,2) NOT NULL DEFAULT '1.00',
  `download_ratio` decimal(4,2) NOT NULL DEFAULT '1.00',
  `add_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expired_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `t_buff_index` (`beneficiary_id`,`torrent_id`,`start_at`,`expired_at`),
  KEY `torrent_id` (`torrent_id`),
  KEY `operator_id` (`operator_id`),
  KEY `beneficiary_id` (`beneficiary_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `torrent_buffs`:
--

-- --------------------------------------------------------

--
-- Table structure for table `torrent_comments`
--

DROP TABLE IF EXISTS `torrent_comments`;
CREATE TABLE IF NOT EXISTS `torrent_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `torrent_id` int(10) UNSIGNED NOT NULL,
  `owner_id` int(10) UNSIGNED NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edit_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_torrents_commits_tid` (`torrent_id`),
  KEY `FK_torrents_commits_uid` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `torrent_comments`:
--   `torrent_id`
--       `torrents` -> `id`
--   `owner_id`
--       `users` -> `id`
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
  `status` enum('disabled','pending','parked','confirmed') NOT NULL DEFAULT 'pending',
  `class` smallint(6) UNSIGNED NOT NULL DEFAULT '1',
  `passkey` varchar(32) NOT NULL,
  `invite_by` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
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
  `bonus_other` decimal(20,2) NOT NULL DEFAULT '0.00',
  `lang` varchar(10) NOT NULL DEFAULT 'en',
  `invites` smallint(5) NOT NULL DEFAULT '0' COMMENT 'The invites which never expire',
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
-- Table structure for table `user_confirm`
--

DROP TABLE IF EXISTS `user_confirm`;
CREATE TABLE IF NOT EXISTS `user_confirm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` enum('register','recover') NOT NULL,
  `uid` int(11) UNSIGNED NOT NULL,
  `secret` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `used` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `secret` (`secret`) USING BTREE,
  KEY `FK_confirm_user_id` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- RELATIONSHIPS FOR TABLE `user_confirm`:
--   `uid`
--       `users` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `user_invitations`
--

DROP TABLE IF EXISTS `user_invitations`;
CREATE TABLE IF NOT EXISTS `user_invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `total` smallint(5) NOT NULL DEFAULT '0',
  `used` smallint(5) NOT NULL DEFAULT '0',
  `create_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expire_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expire_at` (`expire_at`),
  KEY `FK_invitation_users_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='The invite which is temporary';

--
-- RELATIONSHIPS FOR TABLE `user_invitations`:
--   `user_id`
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
ALTER TABLE `torrents` ADD FULLTEXT KEY `title` (`title`,`subtitle`);

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
-- Constraints for table `invite`
--
ALTER TABLE `invite`
  ADD CONSTRAINT `FK_invite_inviter_id` FOREIGN KEY (`inviter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `map_torrents_externalinfo`
--
ALTER TABLE `map_torrents_externalinfo`
  ADD CONSTRAINT `FK_map_tl_to_external_info` FOREIGN KEY (`info_id`) REFERENCES `external_info` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_map_tl_to_torrents` FOREIGN KEY (`torrent_id`) REFERENCES `torrents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `FK_news_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `FK_session_user_id` FOREIGN KEY (`uid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `session_log`
--
ALTER TABLE `session_log`
  ADD CONSTRAINT `FK_session_id` FOREIGN KEY (`sid`) REFERENCES `sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `FK_torrent_categories` FOREIGN KEY (`category`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_torrent_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_torrent_quality_audio` FOREIGN KEY (`quality_audio`) REFERENCES `quality_audio` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_torrent_quality_codec` FOREIGN KEY (`quality_codec`) REFERENCES `quality_codec` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_torrent_quality_medium` FOREIGN KEY (`quality_medium`) REFERENCES `quality_medium` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_torrent_quality_resolution` FOREIGN KEY (`quality_resolution`) REFERENCES `quality_resolution` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_torrent_team` FOREIGN KEY (`team`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `torrent_comments`
--
ALTER TABLE `torrent_comments`
  ADD CONSTRAINT `FK_torrents_commits_tid` FOREIGN KEY (`torrent_id`) REFERENCES `torrents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_torrents_commits_uid` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_confirm`
--
ALTER TABLE `user_confirm`
  ADD CONSTRAINT `FK_confirm_user_id` FOREIGN KEY (`uid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_invitations`
--
ALTER TABLE `user_invitations`
  ADD CONSTRAINT `FK_invitations_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
