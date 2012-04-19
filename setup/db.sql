CREATE TABLE `children` (
  `pmessage` int(11) NOT NULL,
  `cmessage` int(11) NOT NULL,
  UNIQUE KEY `pmessage` (`pmessage`,`cmessage`),
  KEY `pmessage_2` (`pmessage`),
  KEY `cmessage` (`cmessage`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `text` varchar(4096) NOT NULL,
  `time` int(11) NOT NULL,
  `gparent` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `message` (`message`),
  KEY `gparent` (`gparent`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `followers` (
  `user` int(11) NOT NULL,
  `message` int(11) NOT NULL,
  UNIQUE KEY `user` (`user`,`message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` int(11) NOT NULL,
  `message` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;

CREATE TABLE `invites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `email` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `message` (`message`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;

CREATE TABLE `marked` (
  `message` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `gparent` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `members` (
  `groupid` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  `message` int(11) NOT NULL,
  `latest_notice` int(11) NOT NULL,
  UNIQUE KEY `user_2` (`user`,`message`),
  KEY `message` (`message`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `udate` int(10) unsigned NOT NULL,
  `idate` int(11) NOT NULL,
  `sender` int(11) NOT NULL,
  `tag1` int(11) NOT NULL,
  `tag2` int(11) NOT NULL,
  `tag3` int(11) NOT NULL,
  `tag4` int(11) NOT NULL,
  `tag5` int(11) NOT NULL,
  `subject` varchar(256) NOT NULL,
  `plain` varchar(8192) NOT NULL,
  `html` varchar(8192) NOT NULL,
  `files` smallint(6) NOT NULL,
  `images` smallint(6) NOT NULL,
  `source` varchar(32) NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  `assigned` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `msgid` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `root` int(11) NOT NULL,
  `childnum` int(11) NOT NULL,
  `gparent` int(11) NOT NULL,
  `toplevel` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `public` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gparent` (`gparent`),
  KEY `sender` (`sender`,`assigned`),
  KEY `assigned` (`assigned`),
  FULLTEXT KEY `subject` (`subject`,`plain`,`html`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` int(10) unsigned NOT NULL,
  `token` varchar(128) NOT NULL,
  `userid` int(11) NOT NULL,
  `ip_address` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `date` int(11) NOT NULL,
  `creator` int(11) NOT NULL,
  `private` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `topic` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `email` varchar(256) NOT NULL,
  `verified` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `received` int(11) NOT NULL,
  `pwdkey` varchar(128) NOT NULL,
  `email1` varchar(256) NOT NULL,
  `email2` varchar(256) NOT NULL,
  `email3` varchar(256) NOT NULL,
  `pwdreset` int(11) NOT NULL,
  `twitter_name` varchar(128) NOT NULL,
  `twitter_id` int(11) NOT NULL,
  `twitter_auth` varchar(128) NOT NULL,
  `avatar` int(1) NOT NULL,
  `project` int(11) NOT NULL,
  `notify` tinyint(1) NOT NULL,
  `groupid` int(11) NOT NULL,
  `fullname` varchar(128) NOT NULL,
  `root` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  `latest_notice` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `names` (`name`,`email`,`fullname`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;


