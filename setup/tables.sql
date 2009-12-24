-- phpMyAdmin SQL Dump
-- version 3.1.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 24, 2009 at 11:10 PM
-- Server version: 5.0.75
-- PHP Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `osimo_v2`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL auto_increment,
  `is_announcement` int(1) NOT NULL default '0',
  `is_warning` int(1) NOT NULL default '0',
  `user_id` int(8) NOT NULL default '0',
  `title` varchar(32) NOT NULL default '',
  `message` text NOT NULL,
  `link` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `alerts`
--


-- --------------------------------------------------------

--
-- Table structure for table `bans`
--

CREATE TABLE `bans` (
  `id` mediumint(9) NOT NULL auto_increment,
  `user_id` mediumint(8) unsigned NOT NULL default '0',
  `ip_address` varchar(16) NOT NULL default '0',
  `ban_expire` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`ban_expire`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bans`
--


-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(64) NOT NULL default '',
  `parent_forum` int(8) NOT NULL default '-1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `category`
--


-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id`, `name`, `value`) VALUES
(1, 'thread_num_per_page', '10'),
(2, 'post_num_per_page', '10'),
(3, 'current_theme', 'default'),
(4, 'site_title', 'Osimo Community Forums'),
(5, 'site_description', 'Discussion, support and development'),
(6, 'admin_email', 'meltingice8917@gmail.com'),
(7, 'version', '2.0-alpha'),
(8, 'registration', 'true'),
(9, 'email_new_user', 'false'),
(10, 'current_smilies', 'default-smilies'),
(11, 'server_time_zone', '-5.0');

-- --------------------------------------------------------

--
-- Table structure for table `forums`
--

CREATE TABLE `forums` (
  `id` int(11) NOT NULL auto_increment,
  `category` int(11) NOT NULL default '0',
  `title` varchar(64) NOT NULL default '',
  `description` varchar(120) NOT NULL,
  `views` int(11) NOT NULL default '0',
  `threads` int(8) NOT NULL default '0',
  `posts` int(8) NOT NULL default '0',
  `last_poster` varchar(24) NOT NULL default '',
  `last_poster_id` int(8) NOT NULL default '0',
  `last_post_time` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `forums`
--


-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(48) NOT NULL default '',
  `description` text NOT NULL,
  `date_created` datetime NOT NULL,
  `username_style` varchar(72) NOT NULL default '',
  `username_color` varchar(6) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups`
--


-- --------------------------------------------------------

--
-- Table structure for table `group_permissions`
--

CREATE TABLE `group_permissions` (
  `id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `forum_id` int(11) NOT NULL default '0',
  `forum_view` tinyint(1) NOT NULL,
  `thread_create` tinyint(1) NOT NULL,
  `thread_view` tinyint(1) NOT NULL,
  `thread_post` tinyint(1) NOT NULL,
  `post_edit` tinyint(1) NOT NULL,
  `post_links` tinyint(1) NOT NULL,
  `post_images` tinyint(1) NOT NULL,
  `poll_create` tinyint(1) NOT NULL,
  `poll_vote` tinyint(1) NOT NULL,
  `moderate` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `group_permissions`
--


-- --------------------------------------------------------

--
-- Table structure for table `mod_reports`
--

CREATE TABLE `mod_reports` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `type` enum('warning','ban','p_del','t_move','t_del','t_sticky','t_lock','general') NOT NULL,
  `title` varchar(120) NOT NULL,
  `report` text NOT NULL,
  `filed_by_id` bigint(20) unsigned NOT NULL,
  `filed_by` varchar(120) NOT NULL,
  `filed_against_id` bigint(20) unsigned NOT NULL,
  `filed_against` varchar(120) NOT NULL,
  `concerning_id` bigint(20) unsigned NOT NULL default '0',
  `date_filed` bigint(20) unsigned NOT NULL,
  `last_edit_by` mediumint(8) unsigned NOT NULL,
  `last_edit_time` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `type` (`type`,`date_filed`),
  KEY `filed_against` (`filed_against`),
  KEY `concerning_post` (`concerning_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `mod_reports`
--


-- --------------------------------------------------------

--
-- Table structure for table `mod_report_templates`
--

CREATE TABLE `mod_report_templates` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `type` enum('warning','ban','p_del','t_move','t_del','t_sticky','t_lock','general') NOT NULL,
  `title` varchar(120) NOT NULL,
  `report` text NOT NULL,
  `created_by` mediumint(8) unsigned NOT NULL,
  `date_created` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `mod_report_templates`
--


-- --------------------------------------------------------

--
-- Table structure for table `pm_posts`
--

CREATE TABLE `pm_posts` (
  `id` bigint(20) NOT NULL auto_increment,
  `pm_thread` mediumint(8) unsigned NOT NULL,
  `content` text NOT NULL,
  `poster_id` mediumint(9) NOT NULL,
  `poster_username` varchar(24) NOT NULL default '',
  `post_time` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pm_posts`
--


-- --------------------------------------------------------

--
-- Table structure for table `pm_threads`
--

CREATE TABLE `pm_threads` (
  `id` mediumint(9) NOT NULL auto_increment,
  `title` varchar(32) NOT NULL default '',
  `views` int(11) NOT NULL default '0',
  `posts` int(8) NOT NULL default '0',
  `time_created` int(11) NOT NULL default '0',
  `last_poster` varchar(24) NOT NULL default '',
  `last_poster_id` int(8) NOT NULL default '0',
  `last_post_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pm_threads`
--


-- --------------------------------------------------------

--
-- Table structure for table `pm_users`
--

CREATE TABLE `pm_users` (
  `user_id` mediumint(8) unsigned NOT NULL,
  `pm_thread` bigint(20) unsigned NOT NULL,
  `has_read` tinyint(1) NOT NULL,
  KEY `user_id` (`user_id`,`pm_thread`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pm_users`
--


-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL auto_increment,
  `thread` int(11) NOT NULL default '0',
  `content` text NOT NULL,
  `poster_id` int(8) NOT NULL default '0',
  `poster_username` varchar(24) NOT NULL default '',
  `post_time` datetime NOT NULL,
  `last_edit_user_id` int(8) NOT NULL default '0',
  `last_edit_username` varchar(120) NOT NULL default '',
  `last_edit_time` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `posts`
--


-- --------------------------------------------------------

--
-- Table structure for table `ranks`
--

CREATE TABLE `ranks` (
  `id` int(8) NOT NULL auto_increment,
  `image` varchar(96) NOT NULL default '',
  `level` int(6) NOT NULL default '0',
  `status` varchar(48) NOT NULL default '',
  `username_style` varchar(72) NOT NULL default '',
  `username_color` varchar(6) NOT NULL default '',
  `required_posts` int(8) NOT NULL default '0',
  `special_rank` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ranks`
--


-- --------------------------------------------------------

--
-- Table structure for table `smilies`
--

CREATE TABLE `smilies` (
  `id` int(11) NOT NULL auto_increment,
  `smileySet` varchar(120) NOT NULL default '',
  `code` varchar(32) NOT NULL default '',
  `image` varchar(72) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `smilies`
--


-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE `stats` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `forumID` mediumint(8) unsigned NOT NULL default '0',
  `date` date NOT NULL,
  `type` varchar(7) NOT NULL default '0',
  `count` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `stats`
--


-- --------------------------------------------------------

--
-- Table structure for table `threads`
--

CREATE TABLE `threads` (
  `id` int(11) NOT NULL auto_increment,
  `forum` int(11) NOT NULL default '0',
  `title` varchar(64) NOT NULL default '',
  `description` text NOT NULL,
  `views` int(11) NOT NULL default '0',
  `posts` int(8) NOT NULL default '0',
  `original_poster` varchar(24) NOT NULL default '',
  `original_poster_id` int(8) NOT NULL default '0',
  `original_post_time` datetime NOT NULL,
  `last_poster` varchar(24) NOT NULL default '',
  `last_poster_id` int(8) NOT NULL default '0',
  `last_post_time` datetime NOT NULL,
  `sticky` tinyint(1) NOT NULL default '0',
  `locked` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `threads`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(8) NOT NULL auto_increment,
  `username` varchar(24) NOT NULL default '',
  `display_name` varchar(24) NOT NULL,
  `email` varchar(48) NOT NULL default '',
  `password` varchar(120) NOT NULL default '',
  `ip_address` varchar(16) NOT NULL default '',
  `birthday` date NOT NULL,
  `signature` text NOT NULL,
  `group_default` int(8) NOT NULL default '0',
  `posts` int(8) NOT NULL default '0',
  `is_confirmed` int(1) NOT NULL default '0',
  `is_admin` int(1) NOT NULL default '0',
  `time_joined` int(11) NOT NULL default '0',
  `time_last_visit` int(11) NOT NULL default '0',
  `time_last_post` int(11) NOT NULL default '0',
  `last_page` varchar(200) NOT NULL default '',
  `last_page_type` enum('forum','thread','other','logoff') NOT NULL default 'forum',
  `last_page_id` mediumint(8) unsigned NOT NULL default '0',
  `reset_code` varchar(120) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--


-- --------------------------------------------------------

--
-- Table structure for table `user_options`
--

CREATE TABLE `user_options` (
  `user_id` mediumint(8) unsigned NOT NULL,
  `user_age` smallint(6) NOT NULL,
  `user_sex` tinyint(1) NOT NULL,
  `user_location` varchar(72) NOT NULL,
  `chat_aim` varchar(72) NOT NULL,
  `chat_gtalk` varchar(72) NOT NULL,
  `chat_msn` varchar(72) NOT NULL,
  `chat_yim` varchar(72) NOT NULL,
  `chat_skype` varchar(72) NOT NULL,
  `user_website` varchar(200) NOT NULL,
  `user_about` varchar(140) NOT NULL,
  `user_interests` text NOT NULL,
  `user_biography` text NOT NULL,
  `is_visible` tinyint(1) NOT NULL,
  `enable_pms` tinyint(1) NOT NULL,
  `enable_alerts` tinyint(1) NOT NULL,
  `time_zone` decimal(5,2) NOT NULL,
  `time_format` varchar(30) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_options`
--


-- --------------------------------------------------------

--
-- Table structure for table `warning`
--

CREATE TABLE `warning` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(8) NOT NULL default '0',
  `post_id` int(11) NOT NULL default '0',
  `warning_time` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `warning`
--

