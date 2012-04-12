<?php

define( 'UPDATE_VERSION' , 1131 );

/**
 *
 * update.php - automatic system update
 *
 * Automatically update database schemas AND any other development changes such that
 * copying the latest files FROM the source code repository will always perform a clean
 * AND painless upgrade.
 *
 * Each function in this file is named update_nnnn() WHERE nnnn is an increasing number 
 * which began counting at 1000.
 * 
 * At the top of the file "boot.php" is a define for DB_UPDATE_VERSION. Any time there is a change
 * to the database schema or one which requires an upgrade path FROM the existing application,
 * the DB_UPDATE_VERSION AND the UPDATE_VERSION at the top of this file are incremented.
 *
 * The current DB_UPDATE_VERSION is stored in the config area of the database. If the application starts up
 * AND DB_UPDATE_VERSION is greater than the last stored build number, we will process every update function 
 * in order FROM the currently stored value to the new DB_UPDATE_VERSION. This is expected to bring the system 
 * up to current without requiring re-installation or manual intervention.
 *
 * Once the upgrade functions have completed, the current DB_UPDATE_VERSION is stored as the current value.
 * The DB_UPDATE_VERSION will always be one greater than the last numbered script in this file. 
 *
 * If you change the database schema, the following are required:
 *    1. Update the file database.sql to match the new schema.
 *    2. Update this file by adding a new function at the end with the number of the current DB_UPDATE_VERSION.
 *       This function should modify the current database schema AND perform any other steps necessary
 *       to ensure that upgrade is silent AND free FROM requiring interaction.
 *    3. Increment the DB_UPDATE_VERSION in boot.php *AND* the UPDATE_VERSION in this file to match it
 *    4. TEST the upgrade prior to checkin AND filing a pull request.
 *
 */



function update_1000() {

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` DROP `like`, DROP `dislike` ");

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `verb` CHAR( 255 ) NOT NULL AFTER `body` ,
		ADD `object-type` CHAR( 255 ) NOT NULL AFTER `verb` ,
		ADD `object` TEXT NOT NULL AFTER `object-type` ");

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "` ADD `duplex` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `knowyou`");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `duplex` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `rel` ");
 	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` CHANGE `issued-pubkey` `issued-pubkey` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");  
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `term-date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `avatar-date`");
}

function update_1001() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `wall` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `type` ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD INDEX ( `wall` )");  
}

function update_1002() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `gravity` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `wall` ");
}

function update_1003() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` DROP `issued-pubkey` , DROP `ret-id` , DROP `ret-pubkey` ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `usehub` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `ret-aes`");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `hub-verify` CHAR( 255 ) NOT NULL AFTER `usehub`");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD INDEX ( `uid` ),  ADD INDEX ( `self` ),  ADD INDEX ( `issued-id` ),  ADD INDEX ( `dfrn-id` )"); 
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD INDEX ( `blocked` ),  ADD INDEX ( `readonly` )");
}

function update_1004() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `subhub` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `usehub`");
}

function update_1005() {

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `spubkey` TEXT NOT NULL AFTER `prvkey` ,
		ADD `sprvkey` TEXT NOT NULL AFTER `spubkey`");

}

function update_1006() {

	// create 's' keys for everybody that does not have one

	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE `spubkey` = '' ");
	if(count($r)) {
		foreach($r as $rr) {
			$sres=openssl_pkey_new(array('encrypt_key' => false ));
			$sprvkey = '';
			openssl_pkey_export($sres, $sprvkey);
			$spkey = openssl_pkey_get_details($sres);
			$spubkey = $spkey["key"];
			$r = q("UPDATE  `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` SET `spubkey` = %s, `sprvkey` = %s
				WHERE `uid` = %d LIMIT 1",
				dbesc($spubkey),
				dbesc($sprvkey),
				intval($rr['uid'])
			);
		}
	}
}

function update_1007() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `page-flags` INT NOT NULL DEFAULT '0' AFTER `notify-flags`");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD INDEX ( `nickname` )");  
}

function update_1008() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` ADD `with` TEXT NOT NULL AFTER `marital` ");
}

function update_1009() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `allow_location` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `default-location` ");
}

function update_1010() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `lrdd` CHAR( 255 ) NOT NULL AFTER `url` ");
}

function update_1011() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `nick` CHAR( 255 ) NOT NULL AFTER `name` ");
	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE 1");
	if(count($r)) {
		foreach($r as $rr) {
				q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` SET `nick` = %s WHERE `id` = %d LIMIT 1",
					dbesc(basename($rr['url'])),
					intval($rr['id'])
				);
		}
	}
}

function update_1012() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `inform` MEDIUMTEXT NOT NULL AFTER `tag` ");
}

function update_1013() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `target-type` CHAR( 255 ) NOT NULL 
		AFTER `object` , ADD `target` TEXT NOT NULL AFTER `target-type`");
} 

function update_1014() {
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/Photo.php"));
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `micro` TEXT NOT NULL AFTER `thumb` ");
	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` WHERE `scale` = 4");
	if(count($r)) {
		foreach($r as $rr) {
			$ph = new Photo($rr['data']);
			if($ph->is_valid()) {
				$ph->scaleImage(48);
				$ph->store($rr['uid'],$rr['contact-id'],$rr['resource-id'],$rr['filename'],$rr['album'],6,(($rr['profile']) ? 1 : 0));
			}
		}
	}
	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE 1");
	if(count($r)) {
		foreach($r as $rr) {		
			if(stristr($rr['thumb'],'avatar'))
				q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` SET `micro` = %s WHERE `id` = %d LIMIT 1",
					dbesc(str_replace('avatar','micro',$rr['thumb'])),
					intval($rr['id']));
			else
				q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` SET `micro` = %s WHERE `id` = %d LIMIT 1",
					dbesc(str_replace('5.jpg','6.jpg',$rr['thumb'])),
					intval($rr['id']));
		}
	}
}

function update_1015() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` CHANGE `body` `body` mediumtext NOT NULL");
}

function update_1016() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `openid` CHAR( 255 ) NOT NULL AFTER `email` ");
}

function update_1017() {

	q(" CREATE TABLE IF NOT EXISTS  `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "clients") . "` (
`client_id` VARCHAR( 20 ) NOT NULL ,
`pw` VARCHAR( 20 ) NOT NULL ,
`redirect_uri` VARCHAR( 200 ) NOT NULL ,
PRIMARY KEY ( `client_id` ) 
) ENGINE = INNODB DEFAULT CHARSET=utf8 ");

	q(" CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "tokens") . "` (
`id` VARCHAR( 40 ) NOT NULL ,
`client_id` VARCHAR( 20 ) NOT NULL ,
`expires` INT NOT NULL ,
`scope` VARCHAR( 200 ) NOT NULL ,
PRIMARY KEY ( `id` ) 
) ENGINE = INNODB DEFAULT CHARSET=utf8 ");

	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "auth_codes") . "` (
`id` VARCHAR( 40 ) NOT NULL ,
`client_id` VARCHAR( 20 ) NOT NULL ,
`redirect_uri` VARCHAR( 200 ) NOT NULL ,
`expires` INT NOT NULL ,
`scope` VARCHAR( 250 ) NOT NULL ,
PRIMARY KEY ( `id` ) 
) ENGINE = INNODB DEFAULT CHARSET=utf8 ");

}

function update_1018() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue") . "` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`cid` INT NOT NULL ,
`created` DATETIME NOT NULL ,
`last` DATETIME NOT NULL ,
`content` MEDIUMTEXT NOT NULL
) ENGINE = INNODB DEFAULT CHARSET=utf8 ");
}

function update_1019() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mail") . "` DROP `delivered`");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` ADD `showwith` TINYINT(1) NOT NULL DEFAULT '0' AFTER `marital` ");
}

function update_1020() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` DROP `showwith`");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `thr-parent` CHAR( 255 ) NOT NULL AFTER `parent-uri` ");
}

function update_1021() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile_check") . "` ADD `sec` CHAR( 255 ) NOT NULL AFTER `dfrn_id` ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile_check") . "` ADD `cid` INT(10) unsigned  NOT NULL DEFAULT '0' AFTER `uid`");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `private` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `deny_gid` ");
}

function update_1022() {
	q("CREATE TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "pconfig") . "` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`uid` INT NOT NULL DEFAULT '0',
		`cat` CHAR( 255 ) NOT NULL ,
		`k` CHAR( 255 ) NOT NULL ,
		`v` MEDIUMTEXT NOT NULL
		) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci ");
}

function update_1023() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `register_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `timezone` ,
	ADD `login_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `register_date` ");
}

function update_1024() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` ADD `keywords` TEXT NOT NULL AFTER `religion` ");
}

function update_1025() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `maxreq` INT(11) NOT NULL DEFAULT '10' AFTER `pwdreset` ");
}

function update_1026() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "hook") . "` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`hook` CHAR( 255 ) NOT NULL ,
	`file` CHAR( 255 ) NOT NULL ,
	`function` CHAR( 255 ) NOT NULL
	) ENGINE = INNODB DEFAULT CHARSET=utf8 ");
}


function update_1027() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "addon") . "` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`name` CHAR( 255 ) NOT NULL ,
	`version` CHAR( 255 ) NOT NULL ,
	`installed` TINYINT( 1 ) NOT NULL DEFAULT '0' 
	) ENGINE = INNODB DEFAULT CHARSET=utf8 ");
}

function update_1028() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `openidserver` TEXT NOT NULL AFTER `deny_gid` ");
}

function update_1029() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `info` MEDIUMTEXT NOT NULL AFTER `reason` ");
}

function update_1030() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `bdyear` CHAR( 4 ) NOT NULL COMMENT 'birthday notify flag' AFTER `profile-id` ");

	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "event") . "` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`uid` INT NOT NULL ,
	`cid` INT NOT NULL ,
	`created` DATETIME NOT NULL ,
	`edited` DATETIME NOT NULL ,
	`start` DATETIME NOT NULL ,
	`finish` DATETIME NOT NULL ,
	`desc` TEXT NOT NULL ,
	`location` TEXT NOT NULL ,
	`type` CHAR( 255 ) NOT NULL ,
	`adjust` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`allow_cid` MEDIUMTEXT NOT NULL ,
	`allow_gid` MEDIUMTEXT NOT NULL ,
	`deny_cid` MEDIUMTEXT NOT NULL ,
	`deny_gid` MEDIUMTEXT NOT NULL
	) ENGINE = INNODB DEFAULT CHARSET=utf8 ");


}

function update_1031() {
	// Repair any bad links that slipped into the item table
	$r = q("SELECT `id`, `object` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` WHERE `object` != '' ");
	if($r  &&  count($r)) {
		foreach($r as $rr) {
			if(strstr($rr['object'],'type=&quot;http')) {
				q("UPDATE  `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` SET `object` = %s WHERE `id` = %d LIMIT 1",
					dbesc(str_replace('type=&quot;http','href=&quot;http',$rr['object'])),
					intval($rr['id'])
				);
			}
		}
	}
}
	
function update_1032() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` ADD `pdesc` CHAR( 255 ) NOT NULL AFTER `name` ");
}

function update_1033() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "cache") . "` (
 		`k` CHAR( 255 ) NOT NULL PRIMARY KEY ,
 		`v` TEXT NOT NULL,
 		`updated` DATETIME NOT NULL
		) ENGINE = INNODB DEFAULT CHARSET=utf8 ");
}


function update_1034() {

	// If you have any of these parent-less posts they can cause problems, AND 
	// we need to delete them. You can't see them anyway.
	// Legitimate items will usually get re-created on the next 
	// pull FROM the hub.
	// But don't get rid of a post that may have just come in 
	// AND may not yet have the parent id set.

	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` WHERE `parent` = 0 AND `created` < UTC_TIMESTAMP() - INTERVAL 2 MINUTE");

}


function update_1035() {

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `success_update` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `last-update` ");

}

function update_1036() {

	$r = dbq("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE `network` = 'dfrn'  &&  `photo` LIKE '%include/photo%' ");
	if(count($r)) {
		foreach($r as $rr) {
			q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` SET `photo` = %s, `thumb` = %s, `micro` = %s WHERE `id` = %d LIMIT 1",
				dbesc(str_replace('include/photo','photo',$rr['photo'])),
				dbesc(str_replace('include/photo','photo',$rr['thumb'])),
				dbesc(str_replace('include/photo','photo',$rr['micro'])),
				intval($rr['id']));
		}
	}
}

function update_1037() {

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` CHANGE `lrdd` alias` CHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ");

}

function update_1038() {
 q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `plink` CHAR( 255 ) NOT NULL AFTER `target` ");
}

function update_1039() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "addon") . "` ADD `timestamp` BIGINT NOT NULL DEFAULT '0'");
}


function update_1040() {

	q("CREATE TABLE IF NOT EXISTS " . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`url` CHAR( 255 ) NOT NULL ,
	`name` CHAR( 255 ) NOT NULL ,
	`photo` CHAR( 255 ) NOT NULL
	) ENGINE = INNODB DEFAULT CHARSET=utf8 ");

	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "ffinder") . "` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`uid` INT UNSIGNED NOT NULL ,
	`cid` INT UNSIGNED NOT NULL ,
	`fid` INT UNSIGNED NOT NULL
	) ENGINE = INNODB DEFAULT CHARSET=utf8 ");

}

function update_1041() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` CHANGE `keywords` prv_keywords` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` ADD `pub_keywords` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `religion` ");
}

function update_1042() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `expire` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `maxreq` ");
}


function update_1043() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `blockwall` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `blocked` ");
}

function update_1044() {
	//q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` ADD FULLTEXT ( `pub_keywords` ) "); 
	//q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` ADD FULLTEXT ( `prv_keywords` ) "); 
}

function update_1045() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `language` CHAR( 16 ) NOT NULL DEFAULT 'en' AFTER `timezone` ");
}

function update_1046() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `attach` MEDIUMTEXT NOT NULL AFTER `tag` ");
}

function update_1047() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `writable` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `readonly` ");
}

function update_1048() {
	q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` SET `writable` = 1 WHERE `network` = 'stat' AND `notify` != '' ");
}

function update_1049() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mailacct") . "` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`uid` INT NOT NULL,
	`server` CHAR( 255 ) NOT NULL ,
	`user` CHAR( 255 ) NOT NULL ,
	`pass` CHAR( 255 ) NOT NULL ,
	`reply_to` CHAR( 255 ) NOT NULL ,
	`last_check` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'
	) ENGINE = INNODB ");
}

function update_1050() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "attach") . "` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`uid` INT NOT NULL ,
	`filetype` CHAR( 64 ) NOT NULL ,
	`filesize` INT NOT NULL ,
	`data` LONGBLOB NOT NULL ,
	`created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`edited` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`allow_cid` MEDIUMTEXT NOT NULL ,
	`allow_gid` MEDIUMTEXT NOT NULL ,
	`deny_cid` MEDIUMTEXT NOT NULL ,
	`deny_gid` MEDIUMTEXT NOT NULL
	) ENGINE = INNODB ");

}

function update_1051() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mailacct") . "` ADD `port` INT NOT NULL AFTER `server` ,
		ADD `ssltype` CHAR( 16 ) NOT NULL AFTER `port` ,
		ADD `mailbox` CHAR( 255 ) NOT NULL AFTER `ssltype` ");

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `addr` CHAR( 255 ) NOT NULL AFTER `url` ");
}

function update_1052() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mailacct") . "` CHANGE `pass` `pass` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mailacct") . "` ADD `pubmail` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `reply_to` ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `pubmail` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `private` ");
}


function update_1053() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `extid` CHAR( 255 ) NOT NULL AFTER `parent-uri` , ADD INDEX ( `extid` ) ");
}

function update_1054() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "register") . "` ADD `language` CHAR( 16 ) NOT NULL AFTER `password` ");
}

function update_1055() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` ADD `hidewall` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `hide-friends` ");
}

function update_1056() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "attach") . "` ADD `hash` CHAR( 64 ) NOT NULL AFTER `uid` ");
}

function update_1057() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "attach") . "` ADD `filename` CHAR( 255 ) NOT NULL AFTER `hash` ");
}

function update_1058() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `event-id` INT NOT NULL AFTER `resource-id` ");
}

function update_1059() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue") . "` ADD `network` CHAR( 32 ) NOT NULL AFTER `cid` ");
}

function update_1060() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "event") . "` ADD `uri` CHAR( 255 ) NOT NULL AFTER `cid` ");
}

function update_1061() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "event") . "` ADD `nofinish` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `type` ");
}

function update_1062() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `prvnets` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `page-flags` ");
}
function update_1063() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "addon") . "` ADD `plugin_admin` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `timestamp` ");
}

function update_1064() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `app` CHAR( 255 ) NOT NULL AFTER `body` ");
}

function update_1065() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "` ADD `fid` INT NOT NULL DEFAULT '0' AFTER `uid`");
}

function update_1066() {
	$r = q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `received` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `edited` ");
	if($r)
		q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD INDEX ( `received` ) ");

	$r = q("UPDATE  `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` SET `received` = `edited` WHERE 1");
}

function update_1067() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "ffinder") . "` ADD `type` CHAR( 16 ) NOT NULL AFTER `id` ,
	ADD `note` TEXT NOT NULL AFTER `type` ");
}

function update_1068() {
	// 1067 was short-sighted. Undo it.
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "ffinder") . "` DROP `type` , DROP `note` ");

	// AND do this instead.

	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fsuggest") . "` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`uid` INT NOT NULL ,
	`cid` INT NOT NULL ,
	`name` CHAR( 255 ) NOT NULL ,
	`url` CHAR( 255 ) NOT NULL ,
	`photo` CHAR( 255 ) NOT NULL ,
	`note` TEXT NOT NULL ,
	`created` DATETIME NOT NULL 
	) ENGINE = INNODB DEFAULT CHARSET=utf8");

}

function update_1069() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fsuggest") . "` ADD `request` CHAR( 255 ) NOT NULL AFTER `url` ");
	q("ALTER TABLE " . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "` ADD `request` CHAR( 255 ) NOT NULL AFTER `photo` ");
}

// mail body needs to accomodate private photos

function update_1070() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mail") . "` CHANGE `body` body` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ");
}

function update_1071() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` ADD INDEX ( `uid` ) ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` ADD INDEX ( `resource-id` ) ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` ADD INDEX ( `album` ) ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` ADD INDEX ( `scale` ) ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` ADD INDEX ( `profile` ) ");

}

function update_1072() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `starred` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `visible` ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD INDEX ( `starred` ) ");
}

function update_1073() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `remote_self` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `self` ");
}

function update_1074() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `hidewall` TINYINT( 1) NOT NULL DEFAULT '0' AFTER `blockwall` ");
	$r = q("SELECT `uid` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` WHERE `is-default` = 1 AND `hidewall` = 1");
	if(count($r)) {
		foreach($r as $rr)
			q("UPDATE  `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` SET `hidewall` = 1 WHERE `uid` = %d LIMIT 1",
				intval($rr['uid'])
			);
	}
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` DROP `hidewall`");
}

function update_1075() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `guid` CHAR( 16 ) NOT NULL AFTER `uid` ");
	$r = q("SELECT `uid` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE 1");
	if(count($r)) {
		foreach($r as $rr) {
			$found = true;
			do {
				$guid = substr(random_string(),0,16);
				$x = q("SELECT `uid` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE `guid` = %s LIMIT 1",
					dbesc($guid)
				);
				if(! count($x))
					$found = false;
			} while ($found == true );

			q("UPDATE  `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` SET `guid` = %s WHERE `uid` = %d LIMIT 1",
				dbesc($guid),
				intval($rr['uid'])
			);
		}
	}
}

function update_1076() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "guid") . "` ( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`guid` CHAR( 16 ) NOT NULL , INDEX ( `guid` ) ) ENGINE = INNODB ");

}

// There was a typo in 1076 so we'll try again in 1077 to make sure
// We'll also make it big enough to allow for future growth, I seriously 
// doubt Diaspora will be able to leave guids at 16 bytes,
// AND we can also use the same structure for our own larger guids

function update_1077() {
	q("CREATE TABLE IF NOT EXISTS `guid` ( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`guid` CHAR( 16 ) NOT NULL , INDEX ( `guid` ) ) ENGINE = INNODB ");

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "guid") . "` CHANGE `guid` `guid` CHAR( 64 ) NOT NULL"); 
}

function update_1078() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `guid` CHAR( 64 ) NOT NULL AFTER `id` , ADD INDEX ( `guid` ) ");
}

function update_1079() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "sign") . "` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`iid` INT UNSIGNED NOT NULL ,
	`signed_text` MEDIUMTEXT NOT NULL ,
	`signature` TEXT NOT NULL ,
	`signer` CHAR( 255 ) NOT NULL ,
	INDEX ( `iid` ) 
	) ENGINE = INNODB ");

	q("ALTER TABLE " . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "` ADD `nick` CHAR( 255 ) NOT NULL ,
	ADD `addr` CHAR( 255 ) NOT NULL ,
	ADD `notify` CHAR( 255 ) NOT NULL ,
	ADD `poll` CHAR( 255 ) NOT NULL ,
	ADD `confirm` CHAR( 255 ) NOT NULL ,
	ADD `priority` TINYINT( 1 ) NOT NULL ,
	ADD `network` CHAR( 32 ) NOT NULL ,
	ADD `alias` CHAR( 255 ) NOT NULL ,
	ADD `pubkey` TEXT NOT NULL ,
	ADD INDEX ( `addr` ) , 
	ADD INDEX ( `network` ) ");

}

function update_1080() {
	q("ALTER TABLE " . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "` ADD `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
}

function update_1081() {
	// there was a typo in update 1081 so it was corrected AND moved up to 1082
}

function update_1082() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` ADD `guid` CHAR( 64 ) NOT NULL AFTER `contact-id`,
		ADD INDEX ( `guid` )  ");
	// make certain the following code is only executed once
	$r = q("SELECT `id` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` WHERE `guid` != '' LIMIT 1");
	if($r  &&  count($r))
		return;
	$r = q("SELECT distinct( `resource-id` ) FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` WHERE 1 group by `id`");
	if(count($r)) {
		foreach($r as $rr) {
			$guid = get_guid();
			q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` SET `guid` = %s WHERE `resource-id` = %s",
				dbesc($guid),
				dbesc($rr['resource-id'])
			);
		}
	}
}

function update_1083() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "deliverq") . "` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`cmd` CHAR( 32 ) NOT NULL ,
	`item` INT NOT NULL ,
	`contact` INT NOT NULL
	) ENGINE = INNODB ");

}

function update_1084() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `attag` CHAR( 255 ) NOT NULL AFTER `nick` ");
}

function update_1085() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "search") . "` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`uid` INT NOT NULL ,
	`term` CHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	INDEX ( `uid` ),
	INDEX ( `term` ) 
	) ENGINE = INNODB ");
}

function update_1086() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `bookmark` TINYINT(1) NOT NULL DEFAULT '0' AFTER `starred` ");
}

function update_1087() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `commented` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `edited` ");

	$r = q("SELECT `id` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` WHERE `parent` = `id` ");
	if(count($r)) {
		foreach($r as $rr) {
			$x = q("SELECT max( `created` ) AS `cdate` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` WHERE `parent` = %d LIMIT 1",
				intval($rr['id'])
			);
			if(count($x))
				q("UPDATE  `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` SET `commented` = %s WHERE `id` = %d LIMIT 1",
					dbesc($x[0]['cdate']),
					intval($rr['id'])
				);
		}
	}
}

function update_1088() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `account_expired` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `expire` ,
		ADD `account_expires_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `account_expired` ,
		ADD `expire_notification_sent` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `account_expires_on` ");
}

function update_1089() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD `blocktags` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `hidewall` ");
}

function update_1090() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `batch` CHAR(255) NOT NULL AFTER `prvkey` ");

	q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` SET `batch` = concat(substring_index( `url`,'/',3),'/receive/public') WHERE `network` = 'dspr' ");

}

function update_1091() {

	// catch a few stragglers that may have crept in before we added this on remote connects
	q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` SET `batch` = concat(substring_index( `url`,'/',3),'/receive/public') WHERE `network` = 'dspr' AND `batch` = '' ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue") . "` ADD `batch` TINYINT( 1 ) NOT NULL DEFAULT '0' ");
	q("ALTER TABLE " . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "` ADD `batch` CHAR(255) NOT NULL AFTER `addr` ");

}

function update_1092() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD INDEX ( `login_date` ) ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD INDEX ( `account_expired` ) ");
}

function update_1093() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fserver") . "` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`server` CHAR( 255 ) NOT NULL ,
	`posturl` CHAR( 255 ) NOT NULL ,
	`key` TEXT NOT NULL,
	INDEX ( `server` ) 
	) ENGINE = INNODB ");

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "` ADD `visible` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `uid` ");

}

function update_1094() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `postopts` TEXT NOT NULL AFTER `target` ");
}

function update_1095() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `bd` DATE NOT NULL AFTER `bdyear` ");
}

function update_1096() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `origin` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `deleted` , ADD INDEX ( `origin` ) ");
}

function update_1097() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue") . "`
		ADD INDEX ( `cid` ), 
		ADD INDEX ( `created` ), 
		ADD INDEX ( `last` ), 
		ADD INDEX ( `network` ), 
		ADD INDEX ( `batch` ) 
	");
}

function update_1098() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` 
		ADD INDEX ( `network` ), 
		ADD INDEX ( `name` ), 
		ADD INDEX ( `nick` ), 
		ADD INDEX ( `attag` ), 
		ADD INDEX ( `url` ),
		ADD INDEX ( `addr` ), 
		ADD INDEX ( `batch` ) 
	");
}

function update_1099() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`name` CHAR( 255 ) NOT NULL ,
	`url` CHAR( 255 ) NOT NULL ,
	`nurl` CHAR( 255 ) NOT NULL ,
	`photo` CHAR( 255 ) NOT NULL
	) ENGINE = INNODB ");

	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`cid` INT NOT NULL ,
	`uid` INT NOT NULL ,
	`gcid` INT NOT NULL,
	`updated` DATETIME NOT NULL
	) ENGINE = INNODB ");

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "` ADD INDEX ( `nurl` ) ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "` ADD INDEX ( `cid` ), ADD INDEX ( `uid` ), ADD INDEX ( `gcid` ), ADD INDEX ( `updated` ) ");

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `poco` TEXT NOT NULL AFTER `confirm`"); 

}

function update_1100() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `nurl` CHAR( 255 ) NOT NULL AFTER `url` ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD INDEX ( `nurl` ) ");

	require_once($GLOBALS['xoops']->path("/modules/friendica/include/text.php"));

	$r = q("SELECT id, url FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE url != '' AND nurl = '' ");
	if(count($r)) {
		foreach($r as $rr) {
			q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` SET nurl = %s WHERE id = %d LIMIT 1",
				dbesc(normalise_link($rr['url'])),
				intval($rr['id'])
			); 
		}
	}
}


function update_1101() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcign") . "` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`uid` INT NOT NULL ,
	`gcid` INT NOT NULL
	) ENGINE = INNODB ");

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcign") . "` ADD INDEX ( `uid` ), ADD INDEX ( `gcid` ) ");
}

function update_1102() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "clients") . "` ADD `name` TEXT NULL DEFAULT NULL AFTER `redirect_uri`)  "); 
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "clients") . "` ADD `icon` TEXT NULL DEFAULT NULL AFTER `name` "); 
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "clients") . "` ADD `uid` INT NOT NULL DEFAULT 0 AFTER `icon` "); 

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "tokens") . "` ADD `secret` TEXT NOT NULL AFTER `id` "); 
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "tokens") . "` ADD `uid` INT NOT NULL AFTER `scope` "); 
}


function update_1103() {
//	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD INDEX ( `wall` ) "); 
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD FULLTEXT ( `tag` ) "); 
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD INDEX ( `pending` ) "); 
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD INDEX ( `hidewall` ) "); 
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD INDEX ( `blockwall` ) "); 
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD INDEX ( `blocked` ) "); 
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ADD INDEX ( `verified` ) "); 

}

function update_1104() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `forum_mode` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `origin` , ADD INDEX ( `forum_mode` ) "); 

}

function update_1105() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mail") . "` ADD `convid` INT NOT NULL AFTER `contact-id` ");
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mail") . "` ADD `guid` CHAR( 64 ) NOT NULL AFTER `uid` ");

	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "conv") . "` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`guid` CHAR( 64 ) NOT NULL ,
	`recips` MEDIUMTEXT NOT NULL ,
	`uid` INT NOT NULL
	) ENGINE = INNODB ");
}


function update_1106() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD INDEX ( `author-link` ) "); 

}

function update_1107() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD INDEX ( `bookmark` ) "); 

}

function update_1108() { 
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `hidden` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `writable` ,
ADD INDEX ( `hidden` ) "); 

}

function update_1109() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "conv") . "` ADD `creator` CHAR( 255 ) NOT NULL ,
		ADD `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		ADD `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		ADD `subject` MEDIUMTEXT NOT NULL,
		ADD INDEX ( `created` ), ADD INDEX ( `updated` ) "); 
}

function update_1110() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mail") . "` ADD `reply` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `seen`,
		ADD INDEX ( `reply` ), ADD INDEX ( `uid` ), ADD INDEX ( `guid` ), ADD INDEX ( `seen` ),
		ADD INDEX ( `uri` ), ADD INDEX ( `parent-uri` ), ADD INDEX ( `created` ), ADD INDEX ( `convid` ) "); 

}

function update_1111() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "` ADD `connect` CHAR( 255 ) NOT NULL ");
}


function update_1112() {

	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "notify") . "` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`type` INT( 11 ) NOT NULL ,
`name` CHAR( 255 ) NOT NULL ,
`url` CHAR( 255 ) NOT NULL ,
`photo` CHAR( 255 ) NOT NULL ,
`date` DATETIME NOT NULL ,
`msg` MEDIUMTEXT NOT NULL ,
`uid` INT NOT NULL ,
`link` CHAR( 255 ) NOT NULL ,
`seen` TINYINT( 1 ) NOT NULL DEFAULT '0'
) ENGINE = INNODB ");

	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "notify") . "` ADD INDEX ( `type` ), ADD INDEX ( `uid` ), ADD INDEX ( `seen` ), ADD INDEX ( `date` ) ");

}

function update_1113() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "notify") . "` ADD `verb` CHAR( 255 ) NOT NULL ,
ADD `otype` CHAR( 16 ) NOT NULL");
}

function update_1114() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item_id") . "` (
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
) ENGINE = INNODB ");

}

function update_1115() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `moderated` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `pubmail`, ADD INDEX ( `moderated` ) ");
}

function update_1116() {
	//typo! corrected update was rolled forward
}

function update_1117() {
q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "manage") . "` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uid` INT NOT NULL ,
`mid` INT NOT NULL,
INDEX ( `uid` ),
INDEX ( `mid` ) 
) ENGINE = INNODB ");

}

function update_1118() {
	// rolled forward
}

function update_1119() {
q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ADD `closeness` TINYINT( 2 ) NOT NULL DEFAULT '99' AFTER `reason` , ADD INDEX ( `closeness` ) ");
q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` SET closeness = 0 WHERE self = 1");
q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `spam` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `visible` , ADD INDEX ( `spam` ) ");
}


function update_1120() {

	// item table update FROM 1119 did not get into database.sql file.
	// might be missing on new installs. We'll check.

	$r = q("describe item");
	if($r  &&  count($r)) {
		foreach($r as $rr)
			if($rr['Field'] == 'spam')
				return;
	}
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `spam` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `visible` , ADD INDEX ( `spam` ) ");

}

function update_1121() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "poll_result") . "` (
	`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`poll_id` INT NOT NULL ,
	`choice` INT NOT NULL ,
	INDEX ( `poll_id` ),
	INDEX ( `choice` ) 
	) ENGINE = INNODB ");

	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "poll") . "` (
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
	) ENGINE = INNODB ");

}

function update_1122() {
q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "notify") . "` ADD `hash` CHAR( 64 ) NOT NULL AFTER `id` ,
ADD INDEX ( `hash` ) "); 
}

function update_1123() {
set_config('system','allowed_themes','dispy,quattro,testbubble,vier,darkbubble,darkzero,duepuntozero,greenzero,purplezero,quattro-green,slackr');
}

function update_1124() {
q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD INDEX ( `author-name` ) ");
}

function update_1125() {
  q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "notify_threads") . "` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `notify-id` INT NOT NULL,
  `master-parent-item` INT( 10 ) unsigned NOT NULL DEFAULT '0',
  `parent-item` INT( 10 ) unsigned NOT NULL DEFAULT '0',
  `receiver-uid` INT NOT NULL,
  INDEX ( `master-parent-item` ),
  INDEX ( `receiver-uid` ) 
  ) ENGINE = INNODB DEFAULT CHARSET=utf8");
}

function update_1126() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mailacct") . "` ADD `action` INT NOT NULL AFTER `pass`,
		ADD `movetofolder` CHAR(255) NOT NULL AFTER `action`");
}

function update_1127() {
	q("CREATE TABLE IF NOT EXISTS `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "spam") . "` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `uid` INT NOT NULL,
  `spam` INT NOT NULL DEFAULT '0',
  `ham` INT NOT NULL DEFAULT '0',
  `term` CHAR(255) NOT NULL,
  INDEX ( `uid` ),
  INDEX ( `spam` ),
  INDEX ( `ham` ),
  INDEX ( `term` ) 
  ) ENGINE = INNODB DEFAULT CHARSET=utf8");
}


function update_1128() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "spam") . "` ADD `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `term` ");
}

function update_1129() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "notify") . "` ADD `parent` INT NOT NULL AFTER `link` , ADD INDEX ( `parent` ), ADD INDEX ( `link` ), ADD INDEX ( `otype` ) "); 
}

function update_1130() {
	q("ALTER TABLE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` ADD `file` MEDIUMTEXT NOT NULL AFTER `inform`, ADD INDEX ( `file`(60) ) ");
}

