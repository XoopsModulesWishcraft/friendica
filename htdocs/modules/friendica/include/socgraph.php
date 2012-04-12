<?php

require_once($GLOBALS['xoops']->path("/modules/friendica/include/datetime.php"));


/*
 * poco_load
 *
 * Given a contact-id (minimum), load the PortableContacts friend list for that contact,
 * AND ADD the entries to the gcontact (Global Contact) table, or update existing entries
 * if anything (name or photo) has changed.
 * We use normalised urls for comparison which ignore http vs https AND www.domain vs domain
 *
 * Once the global `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` is stored ADD (if necessary) the `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` linkage which associates
 * the given uid, cid to the global `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` entry. There can be many uid/cid combinations
 * pointing to the same global `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` id. 
 *
 */
 



function poco_load($cid,$uid = 0,$url = null) {
	$a = get_app();

	if($cid) {
		if((! $url) || (! $uid)) {
			$r = q("SELECT `poco`, `uid` FROM ". $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE `id` = %d LIMIT 1",
				intval($cid)
			);
			if(count($r)) {
				$url = $r[0]['poco'];
				$uid = $r[0]['uid'];
			}
		}
		if(! $uid)
			return;
	}

	if(! $url)
		return;

	$url = $url . (($uid) ? '/@me/@all?fields=displayName,urls,photos' : '?fields=displayName,urls,photos') ;

	logger('poco_load: ' . $url, LOGGER_DEBUG);

	$s = fetch_url($url);

	logger('poco_load: returns ' . $s, LOGGER_DATA);

	logger('poco_load: return code: ' . $a->get_curl_code(), LOGGER_DEBUG);

	if(($a->get_curl_code() > 299) || (! $s))
		return;


	$j = json_decode($s);

	logger('poco_load: json: ' . print_r($j,true),LOGGER_DATA);

	if(! isset($j->entry))
		return;

	$total = 0;
	foreach($j->entry as $entry) {

		$total ++;
		$profile_url = '';
		$profile_photo = '';
		$connect_url = '';
		$name = '';

		$name = $entry->displayName;

		foreach($entry->urls as $url) {
			if($url->type == 'profile') {
				$profile_url = $url->value;
				continue;
			}
			if($url->type == 'webfinger') {
				$connect_url = str_replace('acct:' , '', $url->value);
				continue;
			}

		} 
		foreach($entry->photos as $photo) {
			if($photo->type == 'profile') {
				$profile_photo = $photo->value;
				continue;
			}
		}

		if((! $name) || (! $profile_url) || (! $profile_photo))
			continue; 
		 
		$x = q("SELECT * FROM `".$GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "` WHERE `nurl` = %s LIMIT 1",
			dbesc(normalise_link($profile_url))
		);

		if(count($x)) {
			$gcid = $x[0]['id'];

			if($x[0]['name'] != $name || $x[0]['photo'] != $profile_photo) {
				q("UPDATE `".$GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") ."` SET `name` = %s, `photo` = %s, `connect` = %s 
					WHERE `nurl` = %s LIMIT 1",
					dbesc($name),
					dbesc($profile_photo),
					dbesc($connect_url),
					dbesc(normalise_link($profile_url))
				);
			}
		}
		else {
			q("INSERT INTO `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "` ( `name`, `url`, `nurl`, `photo`, `connect` )
				values ( %s, %s, %s, %s, %s) ",
				dbesc($name),
				dbesc($profile_url),
				dbesc(normalise_link($profile_url)),
				dbesc($profile_photo),
				dbesc($connect_url)
			);
			$x = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "` WHERE `nurl` = %s LIMIT 1",
				dbesc(normalise_link($profile_url))
			);
			if(count($x))
				$gcid = $x[0]['id'];
		}
		if(! $gcid)
			return;

		$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`  WHERE `cid` = %d AND `uid` = %d AND `gcid` = %d LIMIT 1",
			intval($cid),
			intval($uid),
			intval($gcid)
		);
		if(! count($r)) {
			q("INSERT INTO `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`  ( `cid`, `uid`, `gcid`, `updated` ) values (%d, %d, %d, %s) ",
				intval($cid),
				intval($uid),
				intval($gcid),
				dbesc(datetime_convert())
			);
		}
		else {
			q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "` SET updated = %s WHERE `cid` = %d AND `uid` = %d AND `gcid` = %d LIMIT 1",
				dbesc(datetime_convert()),
				intval($cid),
				intval($uid),
				intval($gcid)
			);
		}

	}
	logger("poco_load: loaded $total entries",LOGGER_DEBUG);

	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`  WHERE `cid` = %d AND `uid` = %d AND `updated` < UTC_TIMESTAMP - INTERVAL 2 DAY",
		intval($cid),
		intval($uid)
	);

}


function count_common_friends($uid,$cid) {

	$r = q("SELECT count(*) as `total`
		FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`.`gcid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "`.`id`
		WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`.`cid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`.`uid` = %d
		AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "`.`nurl` IN (SELECT nurl FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE uid = %d AND self = 0 AND id != %d ) ",
		intval($cid),
		intval($uid),
		intval($uid),
		intval($cid)
	);

	if(count($r))
		return $r[0]['total'];
	return 0;

}


function common_friends($uid,$cid) {

	$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "`.* 
		FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`.`gcid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "`.`id`
		WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`.`cid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`.`uid` = %d
		AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "`.`nurl` IN (SELECT nurl FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE uid = %d AND self = 0 AND id != %d ) 
		ORDER BY `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "`.`name` ASC ",
		intval($cid),
		intval($uid),
		intval($uid),
		intval($cid)
	);

	return $r;

}

function count_all_friends($uid,$cid) {

	$r = q("SELECT count(*) as `total`
		FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") ."` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`.`gcid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "`.`id`
		WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`.`cid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`.`uid` = %d ",
		intval($cid),
		intval($uid)
	);

	if(count($r))
		return $r[0]['total'];
	return 0;

}


function all_friends($uid,$cid,$start = 0, $limit = 80) {

	$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "`.*FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`.`gcid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "`.`id`	WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`.`cid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "glink") . "`.`uid` = %d ORDER BY `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "gcontact") . "`.`name` ASC LIMIT %d, %d", intval($cid), intval($uid), intval($start), intval($limit));

	return $r;
}



function suggestion_query($uid, $start = 0, $limit = 40) {

	if(! $uid)
		return array();

	$r = q("SELECT count(glink.gcid) as `total`, gcontact.* FROM gcontact 
		left join glink on glink.gcid = gcontact.id 
		WHERE uid = %d AND NOT gcontact.nurl IN ( SELECT nurl FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE uid = %d)
		AND NOT gcontact.id IN ( SELECT gcid FROM gcign WHERE uid = %d )
		group by glink.gcid ORDER BY total desc LIMIT %d, %d ",
		intval($uid),
		intval($uid),
		intval($uid),
		intval($start),
		intval($limit)
	);

	if(count($r)  &&  count($r) >= ($limit -1))
		return $r;

	$r2 = q("SELECT gcontact.* FROM gcontact 
		left join glink on glink.gcid = gcontact.id 
		WHERE glink.uid = 0 AND glink.cid = 0 AND NOT gcontact.nurl IN ( SELECT nurl FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE uid = %d)
		AND NOT gcontact.id IN ( SELECT gcid FROM gcign WHERE uid = %d )
		ORDER BY rand() LIMIT %d, %d ",
		intval($uid),
		intval($uid),
		intval($start),
		intval($limit)
	);


	return array_merge($r,$r2);

}

function update_suggestions() {

	$a = get_app();

	$done = array();

	poco_load(0,0,$a->get_baseurl() . '/poco');

	$done[] = $a->get_baseurl() . '/poco';

	if(strlen(get_config('system','directory_submit_url'))) {
		$x = fetch_url('http://dir.friendica.com/pubsites');
		if($x) {
			$j = json_decode($x);
			if($j->entries) {
				foreach($j->entries as $entry) {
					$url = $entry->url . '/poco';
					if(! in_array($url,$done))
						poco_load(0,0,$entry->url . '/poco');
				}
			}
		}
	}

	$r = q("SELECT distinct(poco) as poco FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE network = %s",
		dbesc(NETWORK_DFRN)
	);

	if(count($r)) {
		foreach($r as $rr) {
			$base = substr($rr['poco'],0,strrpos($rr['poco'],'/'));
			if(! in_array($base,$done))
				poco_load(0,0,$base);
		}
	}
}
