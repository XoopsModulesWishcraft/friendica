CREATE TABLE `friendica_b8_wordlist` (
  `token` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `count` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY  (`token`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

INSERT INTO `friendica_b8_wordlist` VALUES ('bayes*dbversion', '2');
INSERT INTO `friendica_b8_wordlist` VALUES ('bayes*texts.ham', '0');
INSERT INTO `friendica_b8_wordlist` VALUES ('bayes*texts.spam', '0');

CREATE TABLE `friendica_challenge` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `challenge` CHAR(255) NOT NULL,
  `dfrn-id` CHAR(255) NOT NULL,
  `expire` INT(11) NOT NULL,
  `type` CHAR(255) NOT NULL,
  `last_update` CHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_config` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cat` CHAR(255) NOT NULL,
  `k` CHAR(255) NOT NULL,
  `v` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_contact` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `uid` INT(11) NOT NULL COMMENT 'owner uid',
  `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `self` TINYINT(1) NOT NULL DEFAULT '0',
  `remote_self` TINYINT(1) NOT NULL DEFAULT '0',
  `rel` TINYINT(1) NOT NULL DEFAULT '0',
  `duplex` TINYINT(1) NOT NULL DEFAULT '0',
  `network` CHAR(255) NOT NULL,
  `name` CHAR(255) NOT NULL,
  `nick` CHAR(255) NOT NULL,
  `attag` CHAR(255) NOT NULL,
  `photo` TEXT NOT NULL, 
  `thumb` TEXT NOT NULL,
  `micro` TEXT NOT NULL,
  `site-pubkey` TEXT NOT NULL,
  `issued-id` CHAR(255) NOT NULL,
  `dfrn-id` CHAR(255) NOT NULL,
  `url` CHAR(255) NOT NULL,
  `nurl` CHAR(255) NOT NULL,
  `addr` CHAR(255) NOT NULL,
  `alias` CHAR(255) NOT NULL,
  `pubkey` TEXT NOT NULL,
  `prvkey` TEXT NOT NULL,
  `batch` CHAR(255) NOT NULL,
  `request` TEXT NOT NULL,
  `notify` TEXT NOT NULL,
  `poll` TEXT NOT NULL,
  `confirm` TEXT NOT NULL,
  `poco` TEXT NOT NULL,
  `aes_allow` TINYINT(1) NOT NULL DEFAULT '0',
  `ret-aes` TINYINT(1) NOT NULL DEFAULT '0',
  `usehub` TINYINT(1) NOT NULL DEFAULT '0',
  `subhub` TINYINT(1) NOT NULL DEFAULT '0',
  `hub-verify` CHAR(255) NOT NULL,
  `last-update` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `success_update` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name-date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `uri-date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `avatar-date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `term-date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `priority` TINYINT(3) NOT NULL,
  `blocked` TINYINT(1) NOT NULL DEFAULT '1',
  `readonly` TINYINT(1) NOT NULL DEFAULT '0',
  `writable` TINYINT(1) NOT NULL DEFAULT '0',
  `hidden` TINYINT(1) NOT NULL DEFAULT '0',
  `pending` TINYINT(1) NOT NULL DEFAULT '1',
  `rating` TINYINT(1) NOT NULL DEFAULT '0',
  `reason` TEXT NOT NULL,
  `closeness` TINYINT(2) NOT NULL DEFAULT '99',
  `info` MEDIUMTEXT NOT NULL,
  `profile-id` INT(11) NOT NULL DEFAULT '0',
  `bdyear` CHAR( 4 ) NOT NULL COMMENT 'birthday notify flag',
  `bd` DATE NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `self` (`self`),
  KEY `network` (`network`),
  KEY `name` (`name`),
  KEY `nick` (`nick`),
  KEY `attag` (`attag`),
  KEY `url` (`url`),
  KEY `nurl` (`nurl`),
  KEY `addr` (`addr`),
  KEY `batch` (`batch`),
  KEY `issued-id` (`issued-id`),
  KEY `dfrn-id` (`dfrn-id`),
  KEY `blocked` (`blocked`),
  KEY `readonly` (`readonly`),
  KEY `hidden` (`hidden`),
  KEY `pending` (`pending`),
  KEY `closeness` (`closeness`)  
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_group` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` INT(10) UNSIGNED NOT NULL,
  `visible` TINYINT(1) NOT NULL DEFAULT '0',
  `deleted` TINYINT(1) NOT NULL DEFAULT '0',
  `name` CHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_group_member` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` INT(10) UNSIGNED NOT NULL,
  `gid` INT(10) UNSIGNED NOT NULL,
  `contact-id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_intro` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` INT(10) UNSIGNED NOT NULL,
  `fid` INT(11) NOT NULL DEFAULT '0',
  `contact-id` INT(11) NOT NULL,
  `knowyou` TINYINT(1) NOT NULL,
  `duplex` TINYINT(1) NOT NULL DEFAULT '0',
  `note` TEXT NOT NULL,
  `hash` CHAR(255) NOT NULL,
  `datetime` DATETIME NOT NULL,
  `blocked` TINYINT(1) NOT NULL DEFAULT '1',
  `ignore` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_item` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `guid` CHAR(64) NOT NULL,
  `uri` CHAR(255) NOT NULL,
  `uid` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `contact-id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `type` CHAR(255) NOT NULL,
  `wall` TINYINT(1) NOT NULL DEFAULT '0',
  `gravity` TINYINT(1) NOT NULL DEFAULT '0',
  `parent` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `parent-uri` CHAR(255) NOT NULL,
  `extid` CHAR(255) NOT NULL,
  `thr-parent` CHAR(255) NOT NULL,
  `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edited` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `commented` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `received` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `changed` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `owner-name` CHAR(255) NOT NULL,
  `owner-link` CHAR(255) NOT NULL,
  `owner-avatar` CHAR(255) NOT NULL,
  `author-name` CHAR(255) NOT NULL,
  `author-link` CHAR(255) NOT NULL,
  `author-avatar` CHAR(255) NOT NULL,
  `title` CHAR(255) NOT NULL,
  `body` MEDIUMTEXT NOT NULL,
  `app` CHAR(255) NOT NULL,
  `verb` CHAR(255) NOT NULL,
  `object-type` CHAR(255) NOT NULL,
  `object` TEXT NOT NULL,
  `target-type` CHAR(255) NOT NULL,
  `target` TEXT NOT NULL,
  `postopts` TEXT NOT NULL,
  `plink` CHAR(255) NOT NULL, 
  `resource-id` CHAR(255) NOT NULL,
  `event-id` INT(10) UNSIGNED NOT NULL,
  `tag` MEDIUMTEXT NOT NULL,
  `attach` MEDIUMTEXT NOT NULL,
  `inform` MEDIUMTEXT NOT NULL,
  `file` MEDIUMTEXT NOT NULL,
  `location` CHAR(255) NOT NULL,
  `coord` CHAR(255) NOT NULL,
  `allow_cid` MEDIUMTEXT NOT NULL,
  `allow_gid` MEDIUMTEXT NOT NULL,
  `deny_cid` MEDIUMTEXT NOT NULL,
  `deny_gid` MEDIUMTEXT NOT NULL,
  `private` TINYINT(1) NOT NULL DEFAULT '0',
  `pubmail` TINYINT(1) NOT NULL DEFAULT '0',
  `moderated` TINYINT(1) NOT NULL DEFAULT '0',
  `visible` TINYINT(1) NOT NULL DEFAULT '0',
  `spam` TINYINT(1) NOT NULL DEFAULT '0',
  `starred` TINYINT(1) NOT NULL DEFAULT '0',
  `bookmark` TINYINT(1) NOT NULL DEFAULT '0',
  `unseen` TINYINT(1) NOT NULL DEFAULT '1',
  `deleted` TINYINT(1) NOT NULL DEFAULT '0',
  `origin` TINYINT(1) NOT NULL DEFAULT '0',
  `forum_mode` TINYINT(1) NOT NULL DEFAULT '0',
  `last-child` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `guid` (`guid`),
  KEY `uri` (`uri`),
  KEY `uid` (`uid`),
  KEY `contact-id` (`contact-id`),
  KEY `type` (`type`),
  KEY `parent` (`parent`),
  KEY `parent-uri` (`parent-uri`),
  KEY `extid` (`extid`),
  KEY `created` (`created`),
  KEY `edited` (`edited`),
  KEY `received` (`received`),
  KEY `moderated` (`moderated`),
  KEY `visible` (`visible`),
  KEY `spam` (`spam`),
  KEY `starred` (`starred`),
  KEY `bookmark` (`bookmark`),
  KEY `deleted` (`deleted`),
  KEY `origin`  (`origin`),
  KEY `forum_mode` (`forum_mode`),
  KEY `last-child` (`last-child`),
  KEY `unseen` (`unseen`),
  KEY `wall` (`wall`),
  KEY `author-name` (`author-name`),
  KEY `author-link` (`author-link`),
  KEY `title` (`title`(35)),
  KEY `body` (`body`(35)),
  KEY `tag` (`tag`(35)),
  KEY `file` (`file`(35)),
  KEY `allow_cid` (`allow_cid`(35)),
  KEY `allow_gid` (`allow_gid`(35)),
  KEY `deny_cid` (`deny_cid`(35)),
  KEY `deny_gid` (`deny_gid`(35))
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_mail` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` INT(10) UNSIGNED NOT NULL,
  `guid` CHAR(64) NOT NULL,
  `from-name` CHAR(255) NOT NULL,
  `from-photo` CHAR(255) NOT NULL,
  `from-url` CHAR(255) NOT NULL,
  `contact-id` CHAR(255) NOT NULL,
  `convid` INT(10) UNSIGNED NOT NULL,
  `title` CHAR(255) NOT NULL,
  `body` MEDIUMTEXT NOT NULL,
  `seen` TINYINT(1) NOT NULL,
  `reply` TINYINT(1) NOT NULL DEFAULT '0',
  `replied` TINYINT(1) NOT NULL,
  `uri` CHAR(255) NOT NULL,
  `parent-uri` CHAR(255) NOT NULL,
  `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `guid` (`guid`),
  KEY `convid` (`convid`),
  KEY `reply` (`reply`),
  KEY `uri` (`uri`),
  KEY `parent-uri` (`parent-uri`),
  KEY `created` (`created`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_photo` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` INT(10) UNSIGNED NOT NULL,
  `contact-id` INT(10) UNSIGNED NOT NULL,
  `guid` CHAR(64) NOT NULL, 
  `resource-id` CHAR(255) NOT NULL,
  `created` DATETIME NOT NULL,
  `edited` DATETIME NOT NULL,
  `title` CHAR(255) NOT NULL,
  `desc` TEXT NOT NULL,
  `album` CHAR(255) NOT NULL,
  `filename` CHAR(255) NOT NULL,
  `height` SMALLINT(6) NOT NULL,
  `width` SMALLINT(6) NOT NULL,
  `data` MEDIUMBLOB NOT NULL,
  `scale` TINYINT(3) NOT NULL,
  `profile` TINYINT(1) NOT NULL DEFAULT '0',
  `allow_cid` MEDIUMTEXT NOT NULL,
  `allow_gid` MEDIUMTEXT NOT NULL,
  `deny_cid` MEDIUMTEXT NOT NULL,
  `deny_gid` MEDIUMTEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `resource-id` (`resource-id`),
  KEY `album` (`album`),
  KEY `scale` (`scale`),
  KEY `profile` (`profile`),
  KEY `guid` (`guid`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_profile` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `uid` INT(11) NOT NULL,
  `profile-name` CHAR(255) NOT NULL,
  `is-default` TINYINT(1) NOT NULL DEFAULT '0',
  `hide-friends` TINYINT(1) NOT NULL DEFAULT '0',
  `name` CHAR(255) NOT NULL,
  `pdesc` CHAR(255) NOT NULL,
  `dob` CHAR(32) NOT NULL DEFAULT '0000-00-00',
  `address` CHAR(255) NOT NULL,
  `locality` CHAR(255) NOT NULL,
  `region` CHAR(255) NOT NULL,
  `postal-code` CHAR(32) NOT NULL,
  `country-name` CHAR(255) NOT NULL,
  `gender` CHAR(32) NOT NULL,
  `marital` CHAR(255) NOT NULL,
  `showwith` TINYINT(1) NOT NULL DEFAULT '0',
  `with` TEXT NOT NULL,
  `sexual` CHAR(255) NOT NULL,
  `politic` CHAR(255) NOT NULL,
  `religion` CHAR(255) NOT NULL,
  `pub_keywords` TEXT NOT NULL,
  `prv_keywords` TEXT NOT NULL,
  `about` TEXT NOT NULL,
  `summary` CHAR(255) NOT NULL,
  `music` TEXT NOT NULL,
  `book` TEXT NOT NULL,
  `tv` TEXT NOT NULL,
  `film` TEXT NOT NULL,
  `interest` TEXT NOT NULL,
  `romance` TEXT NOT NULL,
  `work` TEXT NOT NULL,
  `education` TEXT NOT NULL,
  `contact` TEXT NOT NULL,
  `homepage` CHAR(255) NOT NULL,
  `photo` CHAR(255) NOT NULL,
  `thumb` CHAR(255) NOT NULL,
  `publish` TINYINT(1) NOT NULL DEFAULT '0',
  `net-publish` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pub_keywords` (`pub_keywords`(100)),
  KEY `prv_keywords` (`prv_keywords`(100))
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_profile_check` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` INT(10) UNSIGNED NOT NULL,
  `cid` INT(10) UNSIGNED NOT NULL,
  `dfrn_id` CHAR(255) NOT NULL,
  `sec` CHAR(255) NOT NULL,
  `expire` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_session` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sid` CHAR(255) NOT NULL,
  `data` TEXT NOT NULL,
  `expire` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`),
  KEY `expire` (`expire`)
) ENGINE=INNODB  DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_user` (
  `uid` INT(11) NOT NULL AUTO_INCREMENT,
  `guid` CHAR(16) NOT NULL,
  `username` CHAR(255) NOT NULL,
  `password` CHAR(255) NOT NULL,
  `nickname` CHAR(255) NOT NULL,
  `email` CHAR(255) NOT NULL,
  `openid` CHAR(255) NOT NULL,
  `timezone` CHAR(128) NOT NULL,
  `language` CHAR(32) NOT NULL DEFAULT 'en',
  `register_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `login_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `default-location` CHAR(255) NOT NULL,
  `allow_location` TINYINT(1) NOT NULL DEFAULT '0',
  `theme` CHAR(255) NOT NULL,
  `pubkey` TEXT NOT NULL,
  `prvkey` TEXT NOT NULL,
  `spubkey` TEXT NOT NULL,
  `sprvkey` TEXT NOT NULL,
  `verified` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0', 
  `blocked` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0', 
  `blockwall` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `hidewall` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `blocktags` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `notify-flags` INT(11) UNSIGNED NOT NULL DEFAULT '65535', 
  `page-flags` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `prvnets` TINYINT(1) NOT NULL DEFAULT '0',
  `pwdreset` CHAR(255) NOT NULL,
  `maxreq` INT(11) NOT NULL DEFAULT '10',
  `expire` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `account_expired` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `account_expires_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expire_notification_sent` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `allow_cid` MEDIUMTEXT NOT NULL, 
  `allow_gid` MEDIUMTEXT NOT NULL,
  `deny_cid` MEDIUMTEXT NOT NULL, 
  `deny_gid` MEDIUMTEXT NOT NULL,
  `openidserver` TEXT NOT NULL,
  PRIMARY KEY (`uid`), 
  KEY `nickname` (`nickname`),
  KEY `account_expired` (`account_expired`),
  KEY `hidewall` (`hidewall`),
  KEY `blockwall` (`blockwall`),
  KEY `blocked` (`blocked`),
  KEY `verified` (`verified`),
  KEY `login_date` (`login_date`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;


CREATE TABLE `friendica_register` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `hash` CHAR( 255 ) NOT NULL ,
  `created` DATETIME NOT NULL ,
  `uid` INT(11) UNSIGNED NOT NULL,
  `password` CHAR(255) NOT NULL,
  `language` CHAR(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET=utf8;


CREATE TABLE `friendica_clients` (
`client_id` VARCHAR( 20 ) NOT NULL ,
`pw` VARCHAR( 20 ) NOT NULL ,
`redirect_uri` VARCHAR( 200 ) NOT NULL ,
`name` VARCHAR( 128 ) NULL DEFAULT NULL,
`icon` VARCHAR( 255 ) NULL DEFAULT NULL,
`uid` INT NOT NULL DEFAULT 0,
PRIMARY KEY ( `client_id` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_tokens` (
`id` VARCHAR( 40 ) NOT NULL ,
`secret` VARCHAR( 40 ) NOT NULL ,
`client_id` VARCHAR( 20 ) NOT NULL ,
`expires` INT NOT NULL ,
`scope` VARCHAR( 200 ) NOT NULL ,
`uid` INT NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_auth_codes` (
`id` VARCHAR( 40 ) NOT NULL ,
`client_id` VARCHAR( 20 ) NOT NULL ,
`redirect_uri` VARCHAR( 200 ) NOT NULL ,
`expires` INT NOT NULL ,
`scope` VARCHAR( 250 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_queue` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`cid` INT NOT NULL ,
`network` CHAR( 32 ) NOT NULL,
`created` DATETIME NOT NULL ,
`last` DATETIME NOT NULL ,
`content` MEDIUMTEXT NOT NULL,
`batch` TINYINT( 1 ) NOT NULL DEFAULT '0',
INDEX ( `cid` ),
INDEX ( `created` ),
INDEX ( `last` ),
INDEX ( `network` ),
INDEX ( `batch` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_pconfig` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL DEFAULT '0',
`cat` CHAR( 255 ) NOT NULL ,
`k` CHAR( 255 ) NOT NULL ,
`v` MEDIUMTEXT NOT NULL
) ENGINE = INNODB DEFAULT CHARSET=utf8;


CREATE TABLE `friendica_hook` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`hook` CHAR( 255 ) NOT NULL ,
`file` CHAR( 255 ) NOT NULL ,
`function` CHAR( 255 ) NOT NULL
) ENGINE = INNODB DEFAULT CHARSET=utf8;


CREATE TABLE `friendica_addon` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` CHAR( 255 ) NOT NULL ,
`version` CHAR( 255 ) NOT NULL ,
`installed` TINYINT( 1 ) NOT NULL DEFAULT '0' ,
`timestamp` BIGINT NOT NULL DEFAULT '0' ,
`plugin_admin` TINYINT( 1 ) NOT NULL DEFAULT '0'
) ENGINE = INNODB DEFAULT CHARSET=utf8;


CREATE TABLE `friendica_event` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL ,
`cid` INT NOT NULL ,
`uri` CHAR( 255 ) NOT NULL,
`created` DATETIME NOT NULL ,
`edited` DATETIME NOT NULL ,
`start` DATETIME NOT NULL ,
`finish` DATETIME NOT NULL ,
`desc` TEXT NOT NULL ,
`location` TEXT NOT NULL ,
`type` CHAR( 255 ) NOT NULL ,
`nofinish` TINYINT( 1 ) NOT NULL DEFAULT '0',
`adjust` TINYINT( 1 ) NOT NULL DEFAULT '1',
`allow_cid` MEDIUMTEXT NOT NULL ,
`allow_gid` MEDIUMTEXT NOT NULL ,
`deny_cid` MEDIUMTEXT NOT NULL ,
`deny_gid` MEDIUMTEXT NOT NULL
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_cache` (
 `k` CHAR( 255 ) NOT NULL PRIMARY KEY ,
 `v` TEXT NOT NULL,
 `updated` DATETIME NOT NULL
) ENGINE = INNODB DEFAULT CHARSET=utf8;


CREATE TABLE `friendica_fcontact` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`url` CHAR( 255 ) NOT NULL ,
`name` CHAR( 255 ) NOT NULL ,
`photo` CHAR( 255 ) NOT NULL ,
`request` CHAR( 255 ) NOT NULL,
`nick` CHAR( 255 ) NOT NULL ,
`addr` CHAR( 255 ) NOT NULL ,
`batch` CHAR( 255) NOT NULL,
`notify` CHAR( 255 ) NOT NULL ,
`poll` CHAR( 255 ) NOT NULL ,
`confirm` CHAR( 255 ) NOT NULL ,
`priority` TINYINT( 1 ) NOT NULL ,
`network` CHAR( 32 ) NOT NULL ,
`alias` CHAR( 255 ) NOT NULL ,
`pubkey` TEXT NOT NULL ,
`updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
INDEX ( `addr` ),
INDEX ( `network` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_ffinder` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT UNSIGNED NOT NULL ,
`cid` INT UNSIGNED NOT NULL ,
`fid` INT UNSIGNED NOT NULL
) ENGINE = INNODB DEFAULT CHARSET=utf8;


CREATE TABLE `friendica_fsuggest` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL ,
`cid` INT NOT NULL ,
`name` CHAR( 255 ) NOT NULL ,
`url` CHAR( 255 ) NOT NULL ,
`request` CHAR( 255 ) NOT NULL,
`photo` CHAR( 255 ) NOT NULL ,
`note` TEXT NOT NULL ,
`created` DATETIME NOT NULL
) ENGINE = INNODB DEFAULT CHARSET=utf8;
 

CREATE TABLE `friendica_mailacct` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL,
`server` CHAR( 255 ) NOT NULL ,
`port` INT NOT NULL,
`ssltype` CHAR( 16 ) NOT NULL,
`mailbox` CHAR( 255 ) NOT NULL,
`user` CHAR( 255 ) NOT NULL ,
`pass` TEXT NOT NULL ,
`action` INT NOT NULL ,
`movetofolder` CHAR(255) NOT NULL ,
`pubmail` TINYINT(1) NOT NULL DEFAULT '0',
`last_check` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_attach` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL ,
`hash` CHAR(64) NOT NULL,
`filename` CHAR(255) NOT NULL,
`filetype` CHAR( 64 ) NOT NULL ,
`filesize` INT NOT NULL ,
`data` LONGBLOB NOT NULL ,
`created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
`edited` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
`allow_cid` MEDIUMTEXT NOT NULL ,
`allow_gid` MEDIUMTEXT NOT NULL ,
`deny_cid` MEDIUMTEXT NOT NULL ,
`deny_gid` MEDIUMTEXT NOT NULL
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_guid` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`guid` CHAR( 64 ) NOT NULL ,
INDEX ( `guid` )
) ENGINE = INNODB  DEFAULT CHARSET=utf8;


CREATE TABLE `friendica_sign` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`iid` INT UNSIGNED NOT NULL ,
`signed_text` MEDIUMTEXT NOT NULL ,
`signature` TEXT NOT NULL ,
`signer` CHAR( 255 ) NOT NULL ,
INDEX ( `iid` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;


CREATE TABLE `friendica_deliverq` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`cmd` CHAR( 32 ) NOT NULL ,
`item` INT NOT NULL ,
`contact` INT NOT NULL
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_search` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL ,
`term` CHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
INDEX ( `uid` ),
INDEX ( `term` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_fserver` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`server` CHAR( 255 ) NOT NULL ,
`posturl` CHAR( 255 ) NOT NULL ,
`key` TEXT NOT NULL,
INDEX ( `server` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_gcontact` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` CHAR( 255 ) NOT NULL ,
`url` CHAR( 255 ) NOT NULL ,
`nurl` CHAR( 255 ) NOT NULL ,
`photo` CHAR( 255 ) NOT NULL,
`connect` CHAR( 255 ) NOT NULL,
INDEX ( `nurl` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_glink` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`cid` INT NOT NULL ,
`uid` INT NOT NULL ,
`gcid` INT NOT NULL,
`updated` DATETIME NOT NULL,
INDEX ( `cid` ),
INDEX ( `uid` ),
INDEX ( `gcid` ),
INDEX ( `updated` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_gcign` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL ,
`gcid` INT NOT NULL,
INDEX ( `uid` ),
INDEX ( `gcid` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;


CREATE TABLE `friendica_conv` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `guid` CHAR( 64 ) NOT NULL ,
  `recips` MEDIUMTEXT NOT NULL ,
  `uid` INT NOT NULL,
  `creator` CHAR( 255 ) NOT NULL ,
  `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `subject` MEDIUMTEXT NOT NULL,
  INDEX ( `created` ),
  INDEX ( `updated` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;


CREATE TABLE `friendica_notify` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`hash` CHAR( 64 ) NOT NULL,
`type` INT( 11 ) NOT NULL ,
`name` CHAR( 255 ) NOT NULL ,
`url` CHAR( 255 ) NOT NULL ,
`photo` CHAR( 255 ) NOT NULL ,
`date` DATETIME NOT NULL ,
`msg` MEDIUMTEXT NOT NULL ,
`uid` INT NOT NULL ,
`link` CHAR( 255 ) NOT NULL ,
`parent` INT( 11 ) NOT NULL,
`seen` TINYINT( 1 ) NOT NULL DEFAULT '0',
`verb` CHAR( 255 ) NOT NULL,
`otype` CHAR( 16 ) NOT NULL,
INDEX ( `hash` ),
INDEX ( `type` ),
INDEX ( `uid` ),
INDEX ( `link` ),
INDEX ( `parent` ),
INDEX ( `seen` ),
INDEX ( `date` ),
INDEX ( `otype` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_item_id` (
`iid` INT NOT NULL ,
`uid` INT NOT NULL ,
`face` CHAR( 255 ) NOT NULL ,
`dspr` CHAR( 255 ) NOT NULL ,
`twit` CHAR( 255 ) NOT NULL ,
`stat` CHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `iid` ),
INDEX ( `uid` ),
INDEX ( `face` ),
INDEX ( `dspr` ),
INDEX ( `twit` ),
INDEX ( `stat` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_manage` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL ,
`mid` INT NOT NULL,
INDEX ( `uid` ),
INDEX ( `mid` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_poll_result` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`poll_id` INT NOT NULL ,
`choice` INT NOT NULL ,
INDEX ( `poll_id` ),
INDEX ( `choice` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;


CREATE TABLE `friendica_poll` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL ,
`q0` MEDIUMTEXT NOT NULL ,
`q1` MEDIUMTEXT NOT NULL ,
`q2` MEDIUMTEXT NOT NULL ,
`q3` MEDIUMTEXT NOT NULL ,
`q4` MEDIUMTEXT NOT NULL ,
`q5` MEDIUMTEXT NOT NULL ,
`q6` MEDIUMTEXT NOT NULL ,
`q7` MEDIUMTEXT NOT NULL ,
`q8` MEDIUMTEXT NOT NULL ,
`q9` MEDIUMTEXT NOT NULL ,
INDEX ( `uid` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_notify_threads` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`notify-id` INT NOT NULL,
`master-parent-item` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`parent-item` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`receiver-uid` INT NOT NULL,
INDEX ( `master-parent-item` ),
INDEX ( `receiver-uid` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_spam` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL,
`spam` INT NOT NULL DEFAULT '0',
`ham` INT NOT NULL DEFAULT '0',
`term` CHAR(255) NOT NULL,
`date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
INDEX ( `uid` ),
INDEX ( `spam` ),
INDEX ( `ham` ),
INDEX ( `term` )
) ENGINE = INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `friendica_cache_data` (
	`id` TEXT CHARACTER SET utf8 NOT NULL,
	`items` SMALLINT NOT NULL DEFAULT 0,
	`data` BLOB NOT NULL,
	`mtime` INT UNSIGNED NOT NULL,
	UNIQUE (
		`id`(125)
	)
);

CREATE TABLE `friendica_items` (
	`feed_id` TEXT CHARACTER SET utf8 NOT NULL,
	`id` TEXT CHARACTER SET utf8 NOT NULL,
	`data` TEXT CHARACTER SET utf8 NOT NULL,
	`posted` INT UNSIGNED NOT NULL,
	INDEX `feed_id` (
		`feed_id`(125)
	)
);