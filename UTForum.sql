DROP TABLE IF EXISTS `cms_connect`;
CREATE TABLE `cms_connect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qq_appid` bigint(20) DEFAULT NULL,
  `qq_appkey` varchar(100) DEFAULT NULL,
  `qq_reurl` varchar(200) DEFAULT NULL,
  `wb_appid` bigint(20) DEFAULT NULL,
  `wb_appkey` varchar(100) DEFAULT NULL,
  `wb_reurl` varchar(200) DEFAULT NULL,
  `wx_appid` varchar(50) DEFAULT NULL,
  `wx_appkey` varchar(100) DEFAULT NULL,
  `wx_reurl` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_pay`;
CREATE TABLE `cms_pay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ali_appid` varchar(50) DEFAULT NULL,
  `ali_private_key` text,
  `ali_public_key` text,
  `ali_notify_url` varchar(250) DEFAULT NULL,
  `ali_return_url` varchar(250) DEFAULT NULL,
  `wx_appid` varchar(100) DEFAULT NULL,
  `wx_mchid` varchar(50) DEFAULT NULL,
  `wx_key` varchar(200) DEFAULT NULL,
  `wx_secert` varchar(250) DEFAULT NULL,
  `wx_notify_url` varchar(250) DEFAULT NULL,
  `pp_mod` varchar(20) DEFAULT 'sandbox',
  `pp_clientid` varchar(250) DEFAULT NULL,
  `pp_secret` varchar(250) DEFAULT NULL,
  `pp_return_url` varchar(250) DEFAULT NULL,
  `pp_notify_url` varchar(250) DEFAULT NULL,
  `url_ok` varchar(250) DEFAULT NULL,
  `url_no` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_pay_log`;
CREATE TABLE `cms_pay_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(20) DEFAULT NULL,
  `form` int(11) DEFAULT '1',
  `state` int(11) DEFAULT '0',
  `posnum` varchar(200) DEFAULT NULL,
  `paynum` varchar(200) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `postime` datetime DEFAULT NULL,
  `paytime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_plugin`;
CREATE TABLE `cms_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `auther` varchar(50) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `ver` varchar(10) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_search`;
CREATE TABLE `cms_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hit` int(11) DEFAULT '1',
  `keyword` varchar(100) DEFAULT NULL,
  `lang` varchar(20) DEFAULT 'zh',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `cms_search_set`;
CREATE TABLE `cms_search_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dbs` varchar(50) DEFAULT NULL,
  `fields` varchar(100) DEFAULT NULL,
  `wheres` varchar(200) DEFAULT NULL,
  `pages` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `forum`;
CREATE TABLE IF NOT EXISTS `forum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bid` int(11) NOT NULL DEFAULT '0',
  `forum_name` varchar(200) NOT NULL,
  `forum_icon` varchar(250) DEFAULT NULL,
  `forum_content` text,
  `forum_number` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `forum_member`;
CREATE TABLE IF NOT EXISTS `forum_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utype` int(11) DEFAULT '0',
  `black` int(11) DEFAULT '0',
  `money` decimal(10,2) DEFAULT '0.00',
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `salts` varchar(50) NOT NULL,
  `avatar` varchar(250) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `fullname` varchar(150) DEFAULT NULL,
  `sex` int(11) DEFAULT '1',
  `telephone` varchar(30) DEFAULT NULL,
  `cardnumber` varchar(100) DEFAULT NULL,
  `openid` varchar(200) DEFAULT NULL,
  `other` text,
  `creattime` datetime NOT NULL,
  `lasttime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `forum_message`;
CREATE TABLE IF NOT EXISTS `forum_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `look` int(11) NOT NULL DEFAULT '0',
  `senduid` int(11) NOT NULL,
  `reveuid` int(11) NOT NULL,
  `content` text,
  `sendtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `forum_payment`;
CREATE TABLE IF NOT EXISTS `forum_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `types` int(11) DEFAULT '1',
  `state` int(11) NOT NULL DEFAULT '0',
  `posnum` varchar(200) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  `amount` decimal(10,2) NOT NULL,
  `addtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `forum_post`;
CREATE TABLE IF NOT EXISTS `forum_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `fid` int(11) DEFAULT '0',
  `hit` int(11) NOT NULL DEFAULT '1',
  `notice` int(11) DEFAULT '0',
  `ding` int(11) NOT NULL DEFAULT '0',
  `close` int(11) NOT NULL DEFAULT '0',
  `look` int(11) DEFAULT '0',
  `title` varchar(250) NOT NULL,
  `content` longtext NOT NULL,
  `payfiles` decimal(10,2) DEFAULT '0.00',
  `files` text,
  `downnum` int(11) NOT NULL DEFAULT '0',
  `tag` varchar(250) DEFAULT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `posttime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `forum_reply`;
CREATE TABLE IF NOT EXISTS `forum_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `postid` int(11) NOT NULL DEFAULT '0',
  `replyid` int(11) DEFAULT '0',
  `content` longtext NOT NULL,
  `files` text,
  `ip` varchar(30) DEFAULT NULL,
  `replytime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `forum_set`;
CREATE TABLE IF NOT EXISTS `forum_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rate` int(11) NOT NULL DEFAULT '10',
  `cookname` varchar(30) NOT NULL DEFAULT 'usualtool_',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
INSERT INTO `cms_connect` VALUES (NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `cms_pay` VALUES (NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'sandbox', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `cms_search_set` VALUES (1, 'forum_post', 'title,content', 'title like [keyword] or content like [keyword]', 'm=forum&p=post');
INSERT INTO `forum_member` (`id`, `utype`, `black`, `username`, `password`, `salts`, `avatar`, `email`, `fullname`, `sex`, `cardnumber`, `openid`, `other`, `creattime`) VALUES 
(1, 99999, 0, 'admin', '41635f645e9194175528278374b272f9d2ad5ba9', '2R6HY7', '/app/assets/images/noimage.png', 'test#test.com', 'Admin', 1, NULL, NULL, NULL, '2021-11-25 00:00:00');
INSERT INTO `forum_set` VALUES (1,10,'usualtool_');
