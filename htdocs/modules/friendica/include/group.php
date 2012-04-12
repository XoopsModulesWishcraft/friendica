<?php


function group_add($uid,$name) {

	$ret = false;
	if(x($uid)  &&  x($name)) {
		$r = group_byname($uid,$name); // check for dups
		if($r !== false) {

			// This could be a problem. 
			// Let's assume we've just created a group which we once deleted
			// all the old members are gone, but the group remains so we don't break any security
			// access lists. What we're doing here is reviving the dead group, but old content which
			// was restricted to this group may now be seen by the new group members. 

			$z = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "` WHERE `id` = %d LIMIT 1",
				intval($r)
			);
			if(count($z)  &&  $z[0]['deleted']) {
				$r = q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . " SET `deleted` = 0 WHERE `uid` = %d AND `name` = %s LIMIT 1",
					intval($uid),
					dbesc($name)
				);
				notice( t('A deleted group with this name was revived. Existing item permissions <strong>may</strong> apply to this group AND any future members. If this is not what you intended, please create another group with a different name.') . EOL); 
			}
			return true;
		}
		$r = q("INSERT INTO `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "` ( `uid`, `name` ) 
			VALUES( %d, %s ) ",
			intval($uid),
			dbesc($name)
		);
		$ret = $r;
	}	
	return $ret;
}


function group_rmv($uid,$name) {
	$ret = false;
	if(x($uid)  &&  x($name)) {
		$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "` WHERE `uid` = %d AND `name` = %s LIMIT 1",
			intval($uid),
			dbesc($name)
		);
		if(count($r))
			$group_id = $r[0]['id'];
		if(! $group_id)
			return false;

		// remove all members
		$r = q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "` WHERE `uid` = %d AND `gid` = %d ",
			intval($uid),
			intval($group_id)
		);

		// remove group
		$r = q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . " SET `deleted` = 1 WHERE `uid` = %d AND `name` = %s LIMIT 1",
			intval($uid),
			dbesc($name)
		);

		$ret = $r;

	}

	return $ret;
}

function group_byname($uid,$name) {
	if((! $uid) || (! strlen($name)))
		return false;
	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "` WHERE `uid` = %d AND `name` = %s LIMIT 1",
		intval($uid),
		dbesc($name)
	);
	if(count($r))
		return $r[0]['id'];
	return false;
}

function group_rmv_member($uid,$name,$member) {
	$gid = group_byname($uid,$name);
	if(! $gid)
		return false;
	if(! ( $uid  &&  $gid  &&  $member))
		return false;
	$r = q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "` WHERE `uid` = %d AND `gid` = %d AND `contact-id` = %d LIMIT 1 ",
		intval($uid),
		intval($gid),
		intval($member)
	);
	return $r;
	

}


function group_add_member($uid,$name,$member) {
	$gid = group_byname($uid,$name);
	if((! $gid) || (! $uid) || (! $member))
		return false;

	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "` WHERE `uid` = %d AND `id` = %d AND `contact-id` = %d LIMIT 1",	
		intval($uid),
		intval($gid),
		intval($member)
	);
	if(count($r))
		return true;	// You might question this, but 
				// we indicate success because the group member was in fact created
				// -- It was just created at another time
 	if(! count($r))
		$r = q("INSERT INTO `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "` ( `uid`, `gid`, `contact-id` )
			VALUES( %d, %d, %d ) ",
			intval($uid),
			intval($gid),
			intval($member)
	);
	return $r;
}

function group_get_members($gid) {
	$ret = array();
	if(intval($gid)) {
		$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "`.`contact-id`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.* FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "`
			LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "`.`contact-id`
			WHERE `gid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "`.`uid` = %d ORDER BY `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`name` ASC ",
			intval($gid),
			intval(local_user())
		);
		if(count($r))
			$ret = $r;
	}
	return $ret;
}

function group_public_members($gid) {
	$ret = 0;
	if(intval($gid)) {
		$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` AS `contact-id` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "` 
			LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "`.`contact-id`
			WHERE `gid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "`.`uid` = %d 
			AND  `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`network` = %s AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`notify` != '' ",
			intval($gid),
			intval(local_user()),
			dbesc(NETWORK_OSTATUS)
		);		
		if(count($r))
			$ret = count($r);
	}
	return $ret;
}



function group_side($every="contacts",$each="group",$edit = false, $group_id = 0, $cid = 0) {

	$o = '';

	if(! local_user())
		return '';

	$groups = array();
	
	$groups[] = array(
		'text' 	=> t('Everybody'),
		'id' => 0,
		'selected' => (($group_id == 0) ? 'group-selected' : ''),
		'href' 	=> $every,
	);



	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "` WHERE `deleted` = 0 AND `uid` = %d ORDER BY `name` ASC",
		intval($_SESSION['uid'])
	);
	$member_of = array();
	if($cid) {
		$member_of = groups_containing(local_user(),$cid);
	} 

	if(count($r)) {
		foreach($r as $rr) {
			$selected = (($group_id == $rr['id']) ? ' group-selected' : '');
			
			if ($edit) {
				$groupedit = array(
					'href' => "group/".$rr['id'],
					'title' => t('edit'),
				);
			} else {
				$groupedit = null;
			}
			
			$groups[] = array(
				'id'		=> $rr['id'],
				'cid'		=> $cid,
				'text' 		=> $rr['name'],
				'selected' 	=> $selected,
				'href'		=> $each."/".$rr['id'],
				'edit'		=> $groupedit,
				'ismember'	=> in_array($rr['id'],$member_of),
			);
		}
	}
	
	
	$tpl = get_markup_template("group_side.tpl");
	$o = replace_macros($tpl, array(
		'$title'		=> t('Groups'),
		'$edittext'     => t('Edit group'),
		'$createtext' 	=> t('Create a new group'),
		'$groups'		=> $groups,
		'$add'			=> t('add'),
	));
		
	
	return $o;
}

function expand_groups($a) {
	if(! (is_array($a)  &&  count($a)))
		return array();
	$groups = implode(',', $a);
	$groups = dbesc($groups);
	$r = q("SELECT `contact-id` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "` WHERE `gid` IN ( $groups )");
	$ret = array();
	if(count($r))
		foreach($r as $rr)
			$ret[] = $rr['contact-id'];
	return $ret;
}


function member_of($c) {

	$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "`.`name`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "`.`id` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "`.`gid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "`.`id` WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "`.`contact-id` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "`.`deleted` = 0 ORDER BY `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "`.`name` ASC ",	intval($c)
	);

	return $r;

}

function groups_containing($uid,$c) {

	$r = q("SELECT `gid` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "` WHERE `uid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "`.`contact-id` = %d ",
		intval($uid),
		intval($c)
	);

	$ret = array();
	if(count($r)) {
		foreach($r as $rr)
			$ret[] = $rr['gid'];
	}

	return $ret;
}
