<?php

function notes_init(&$a) {

	if(! local_user())
		return;

	$profile = 0;

	$which = $a->user['nickname'];

//	profile_load($a,$which,$profile);

}


function notes_content(&$a,$update = false) {

	if(! local_user()) {
		notice( t('Permission denied.') . EOL);
		return;
	}

	require_once($GLOBALS['xoops']->path("/modules/friendica/include/bbcode.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/security.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/conversation.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/acl_selectors.php"));
	$groups = array();


	$o = '';

	$remote_contact = false;

	$contact_id = $_SESSION['cid'];
	$contact = $a->contact;

	$is_owner = true;

	$o ="";
	$o .= profile_tabs($a,True);

	if(! $update) {
		$o .= '<h3>' . t('Personal Notes') . '</h3>';

		$commpage = false;
		$commvisitor = false;

		$celeb = false;



		$x = array(
			'is_owner' => $is_owner,
       		'allow_location' => (($a->user['allow_location']) ? true : false),
	        'default_location' => $a->user['default-location'],
    	    'nickname' => $a->user['nickname'],
   	    	'lockstate' => 'lock',
	       	'acl' => '',
    	    'bang' => '',
        	'visitor' => 'block',
	   	    'profile_uid' => local_user(),
			'button' => t('Save')

    	);

    	$o .= status_editor($a,$x,$a->contact['id']);

		$o .= '<div id="live-notes"></div>' . "\r\n";
		$o .= "<script> var profile_uid = " . local_user() 
			. "; var netargs = '/?f='; var profile_page = " . $a->pager['page'] . "; </script>\r\n";

	}

	// Construct permissions

	// default permissions - anonymous user
	
	$sql_extra = " AND `allow_cid` = '<" . $a->contact['id'] . ">' ";

	$r = q("SELECT COUNT(*) AS `total`
		FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`contact-id`
		WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`visible` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`moderated` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deleted` = 0
		AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pending` = 0 
		AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`parent` AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`wall` = 0
		$sql_extra ",
		intval(local_user())

	);

	if(count($r)) {
		$a->set_pager_total($r[0]['total']);
		$a->set_pager_itemspage(40);
	}

	$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id` AS `item_id`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`uid` AS `contact-uid`
		FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`contact-id`
		WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`visible` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deleted` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`moderated` = 0
		AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pending` = 0
		AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`parent` AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`wall` = 0
		$sql_extra
		ORDER BY `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`created` DESC LIMIT %d , %d ",
		intval(local_user()),
		intval($a->pager['start']),
		intval($a->pager['itemspage'])

	);

	$parents_arr = array();
	$parents_str = '';

	if(count($r)) {
		foreach($r as $rr)
			$parents_arr[] = $rr['item_id'];
		$parents_str = implode(', ', $parents_arr);
 
		$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.*, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id` AS `item_id`, 
			`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`name`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` , `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`url`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`network`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`rel`, 
			`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`thumb`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`self`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`writable`, 
			`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` AS `cid`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`uid` AS `contact-uid`
			FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`contact-id`
			WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`visible` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`moderated` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deleted` = 0
			AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pending` = 0
			AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`parent` IN ( %s )
			$sql_extra
			ORDER BY `parent` DESC, `gravity` ASC, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id` ASC ",
			intval(local_user()),
			dbesc($parents_str)
		);
	}

	$o .= conversation($a,$r,'notes',$update);


	$o .= paginate($a);
	return $o;
}
