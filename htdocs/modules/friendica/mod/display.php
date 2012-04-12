<?php


function display_content(&$a) {

	if((get_config('system','block_public'))  &&  (! local_user())  &&  (! remote_user())) {
		notice( t('Public access denied.') . EOL);
		return;
	}

	require_once($GLOBALS['xoops']->path("/modules/friendica/include/bbcode.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/security.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/conversation.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/acl_selectors.php"));


	$o = '<div id="live-display"></div>' . "\r\n";

	$a->page['htmlhead'] .= '<script>$(document).ready(function() {	$(".comment-edit-wrapper  textarea").contact_autocomplete(baseurl+"/acl"); });</script>';


	$nick = (($a->argc > 1) ? $a->argv[1] : '');
	profile_load($a,$nick);

	$item_id = (($a->argc > 2) ? intval($a->argv[2]) : 0);

	if(! $item_id) {
		$a->error = 404;
		notice( t('Item not found.') . EOL);
		return;
	}

	$groups = array();

	$contact = null;
	$remote_contact = false;

	if(remote_user()) {
		$contact_id = $_SESSION['visitor_id'];
		$groups = init_groups_visitor($contact_id);
		$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE `id` = %d AND `uid` = %d LIMIT 1",
			intval($contact_id),
			intval($a->profile['uid'])
		);
		if(count($r)) {
			$contact = $r[0];
			$remote_contact = true;
		}
	}

	if(! $remote_contact) {
		if(local_user()) {
			$contact_id = $_SESSION['cid'];
			$contact = $a->contact;
		}
	}

	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE `uid` = %d AND `self` = 1 LIMIT 1",
		intval($a->profile['uid'])
	);
	if(count($r))
		$a->page_contact = $r[0];

	$is_owner = ((local_user())  &&  (local_user() == $a->profile['profile_uid']) ? true : false);

	if($a->profile['hidewall']  &&  (! $is_owner)  &&  (! $remote_contact)) {
		notice( t('Access to this profile has been restricted.') . EOL);
		return;
	}
	
	if ($is_owner)
		$celeb = ((($a->user['page-flags'] == PAGE_SOAPBOX) || ($a->user['page-flags'] == PAGE_COMMUNITY)) ? true : false);

		$x = array(
			'is_owner' => true,
			'allow_location' => $a->user['allow_location'],
			'default_location' => $a->user['default-location'],
			'nickname' => $a->user['nickname'],
			'lockstate' => ( (is_array($a->user))  &&  ((strlen($a->user['allow_cid'])) || (strlen($a->user['allow_gid'])) || (strlen($a->user['deny_cid'])) || (strlen($a->user['deny_gid']))) ? 'lock' : 'unlock'),
			'acl' => populate_acl($a->user, $celeb),
			'bang' => '',
			'visitor' => 'block',
			'profile_uid' => local_user()
		);	
		$o .= status_editor($a,$x,0,true);


	$sql_extra = item_permissions_sql($a->profile['uid'],$remote_contact,$groups);

	$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.*, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id` AS `item_id`, 
		`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`name`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` , `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`url`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`rel`,
		`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`network`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`thumb`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`self`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`writable`, 
		`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` AS `cid`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`uid` AS `contact-uid`
		FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`contact-id`
		WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`visible` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deleted` = 0
		and `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`moderated` = 0
		AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pending` = 0
		AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`parent` = ( SELECT `parent` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` WHERE ( `id` = %s OR `uri` = %s ))
		$sql_extra
		ORDER BY `parent` DESC, `gravity` ASC, `id` ASC ",
		intval($a->profile['uid']),
		dbesc($item_id),
		dbesc($item_id)
	);


	if(count($r)) {

		if((local_user())  &&  (local_user() == $a->profile['uid'])) {
			q("UPDATE  `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` SET `unseen` = 0 
				WHERE `parent` = %d AND `unseen` = 1",
				intval($r[0]['parent'])
			);
		}


		$o .= conversation($a,$r,'display', false);

	}
	else {
		$r = q("SELECT `id` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` WHERE `id` = %s OR `uri` = %s LIMIT 1",
			dbesc($item_id),
			dbesc($item_id)
		);
		if(count($r)) {
			if($r[0]['deleted']) {
				notice( t('Item has been removed.') . EOL );
			}
			else {	
				notice( t('Permission denied.') . EOL ); 
			}
		}
		else {
			notice( t('Item not found.') . EOL );
		}

	}

	return $o;
}

