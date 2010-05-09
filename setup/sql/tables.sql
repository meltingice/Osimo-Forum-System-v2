SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `alerts` (
  `id` int(11) NOT NULL auto_increment,
  `is_announcement` int(1) NOT NULL default '0',
  `is_warning` int(1) NOT NULL default '0',
  `user_id` int(8) NOT NULL default '0',
  `title` varchar(32) NOT NULL default '',
  `message` text NOT NULL,
  `link` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `bans` (
  `id` mediumint(9) NOT NULL auto_increment,
  `user_id` mediumint(8) unsigned NOT NULL default '0',
  `ip_address` varchar(16) NOT NULL default '0',
  `ban_expire` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`ban_expire`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(64) NOT NULL default '',
  `parent_forum` int(8) NOT NULL default '-1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `forums` (
  `id` int(11) NOT NULL auto_increment,
  `category` int(11) NOT NULL default '0',
  `parent_forum` int(11) NOT NULL default '0',
  `title` varchar(64) NOT NULL default '',
  `description` text NOT NULL,
  `views` int(11) NOT NULL default '0',
  `threads` int(8) NOT NULL default '0',
  `posts` int(8) NOT NULL default '0',
  `last_thread_id` mediumint(8) unsigned NOT NULL,
  `last_thread_title` varchar(200) NOT NULL,
  `last_poster` varchar(24) NOT NULL default '',
  `last_poster_id` int(8) NOT NULL default '0',
  `last_post_time` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `forum_permissions` (
  `id` int(11) NOT NULL auto_increment,
  `group` int(11) NOT NULL default '0',
  `forum` int(11) NOT NULL default '0',
  `perm_can_moderate_forum` int(1) NOT NULL default '0',
  `perm_can_view_forum` int(1) NOT NULL default '0',
  `perm_can_view_thread` int(1) NOT NULL default '0',
  `perm_can_post_thread` int(1) NOT NULL default '0',
  `perm_can_post_reply` int(1) NOT NULL default '0',
  `perm_can_post_links` int(1) NOT NULL default '0',
  `perm_can_edit` int(1) NOT NULL default '0',
  `perm_can_create_poll` int(1) NOT NULL default '0',
  `perm_can_vote` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(48) NOT NULL default '',
  `description` text NOT NULL,
  `username_style` varchar(72) NOT NULL default '',
  `username_color` varchar(6) NOT NULL default '',
  `perm_can_view_forum` int(1) NOT NULL default '0',
  `perm_can_view_thread` int(1) NOT NULL default '0',
  `perm_can_post_thread` int(1) NOT NULL default '0',
  `perm_can_post_reply` int(1) NOT NULL default '0',
  `perm_can_post_links` int(1) NOT NULL default '0',
  `perm_can_edit` int(1) NOT NULL default '0',
  `perm_can_create_poll` int(1) NOT NULL default '0',
  `perm_can_vote` int(1) NOT NULL default '0',
  `perm_can_send_pm` int(1) NOT NULL default '0',
  `perm_can_receive_pm` int(1) NOT NULL default '0',
  `perm_can_receive_alert` int(1) NOT NULL default '0',
  `perm_can_view_profile` int(1) NOT NULL default '0',
  `perm_can_edit_profile` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mod_reports` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `mod_report_templates` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `type` enum('warning','ban','p_del','t_move','t_del','t_sticky','t_lock','general') NOT NULL,
  `title` varchar(120) NOT NULL,
  `report` text NOT NULL,
  `created_by` mediumint(8) unsigned NOT NULL,
  `date_created` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL auto_increment,
  `thread` int(11) NOT NULL default '0',
  `body` text NOT NULL,
  `poster_id` int(8) NOT NULL default '0',
  `post_time` datetime NOT NULL,
  `last_edit_user_id` int(8) NOT NULL default '0',
  `last_edit_time` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `private_message_post` (
  `id` int(11) NOT NULL auto_increment,
  `private_message_thread` int(11) NOT NULL default '0',
  `body` text NOT NULL,
  `poster_id` int(8) NOT NULL default '0',
  `poster_username` varchar(24) NOT NULL default '',
  `post_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `private_message_thread` (
  `id` int(11) NOT NULL auto_increment,
  `user_sent` int(8) NOT NULL default '0',
  `user_received` int(8) NOT NULL default '0',
  `title` varchar(32) NOT NULL default '',
  `views` int(11) NOT NULL default '0',
  `posts` int(8) NOT NULL default '0',
  `time_created` int(11) NOT NULL default '0',
  `last_poster` varchar(24) NOT NULL default '',
  `last_poster_id` int(8) NOT NULL default '0',
  `last_post_time` int(11) NOT NULL default '0',
  `read_status` enum('read','unread') NOT NULL default 'unread',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `ranks` (
  `id` int(8) NOT NULL auto_increment,
  `image` varchar(96) NOT NULL default '',
  `level` int(6) NOT NULL default '0',
  `status` varchar(48) NOT NULL default '',
  `username_style` varchar(72) NOT NULL default '',
  `username_color` varchar(6) NOT NULL default '',
  `required_posts` int(8) NOT NULL default '0',
  `special_rank` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `smilies` (
  `id` int(11) NOT NULL auto_increment,
  `smileySet` varchar(120) NOT NULL default '',
  `code` varchar(32) NOT NULL default '',
  `image` varchar(72) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `stats` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `forumID` mediumint(8) unsigned NOT NULL default '0',
  `date` date NOT NULL,
  `type` varchar(7) NOT NULL default '0',
  `count` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `threads` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(8) NOT NULL auto_increment,
  `username` varchar(24) NOT NULL default '',
  `username_clean` varchar(24) NOT NULL default '',
  `email` varchar(48) NOT NULL default '',
  `password` varchar(120) NOT NULL default '',
  `ip_address` varchar(16) NOT NULL default '',
  `birthday` date NOT NULL,
  `signature` text NOT NULL,
  `username_color` varchar(6) NOT NULL default '',
  `posts` int(8) NOT NULL default '0',
  `field_age` int(2) NOT NULL default '0',
  `field_sex` varchar(6) NOT NULL default '',
  `field_location` varchar(72) NOT NULL default '',
  `field_aim` varchar(72) NOT NULL default '',
  `field_jabber` varchar(72) NOT NULL default '',
  `field_msn` varchar(72) NOT NULL default '',
  `field_yim` varchar(72) NOT NULL default '',
  `field_skype` varchar(72) NOT NULL,
  `field_website` varchar(200) NOT NULL default '',
  `field_about` text NOT NULL,
  `field_interests` text NOT NULL,
  `field_biography` text NOT NULL,
  `is_confirmed` int(1) NOT NULL default '0',
  `is_admin` int(1) NOT NULL default '0',
  `is_global_mod` int(1) NOT NULL default '0',
  `is_mailing_list` int(1) NOT NULL default '0',
  `time_joined` datetime NOT NULL,
  `time_last_visit` datetime NOT NULL,
  `time_last_post` int(11) NOT NULL default '0',
  `last_page` varchar(200) NOT NULL default '',
  `last_page_type` enum('forum','thread','other','logoff') NOT NULL default 'forum',
  `last_page_id` mediumint(8) unsigned NOT NULL default '0',
  `time_zone` decimal(5,2) NOT NULL default '0.00',
  `time_format` varchar(30) NOT NULL default 'M j, Y',
  `reset_code` varchar(120) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `warning` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(8) NOT NULL default '0',
  `post_id` int(11) NOT NULL default '0',
  `warning_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
