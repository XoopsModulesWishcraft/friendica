<?php

function profile_init(&$a) {

	$blocked = (((get_config('system','block_public'))  &&  (! local_user())  &&  (! remote_user())) ? true : false);

	if($a->argc > 1)
		$which = $a->argv[1];
	else {
		$r = q("SELECT nickname FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE blocked = 0 AND account_expired = 0 AND verified = 1 ORDER BY rand() LIMIT 1");
		if(count($r)) {
			$which = $r[0]['nickname'];
		}
		else {
			notice( t('Requested profile is not available.') . EOL );
			$a->error = 404;
			return;
		}
	}

	$profile = 0;
	if((local_user())  &&  ($a->argc > 2)  &&  ($a->argv[2] === 'view')) {
		$which = $a->user['nickname'];
		$profile = $a->argv[1];		
	}

	profile_load($a,$which,$profile);

	if((x($a->profile,'page-flags'))  &&  ($a->profile['page-flags'] == PAGE_COMMUNITY)) {
		$a->page['htmlhead'] .= '<meta name="friendika.community" content="true" />';
	}
	if(x($a->profile,'openidserver'))				
		$a->page['htmlhead'] .= '<link rel="openid.server" href="' . $a->profile['openidserver'] . '" />' . "\r\n";
	if(x($a->profile,'openid')) {
		$delegate = ((strstr($a->profile['openid'],'://')) ? $a->profile['openid'] : 'http://' . $a->profile['openid']);
		$a->page['htmlhead'] .= '<link rel="openid.delegate" href="' . $delegate . '" />' . "\r\n";
	}

	if(! $blocked) {
		$keywords = ((x($a->profile,'pub_keywords')) ? $a->profile['pub_keywords'] : '');
		$keywords = str_replace(array('#',',',' ',',,'),array('',' ',',',','),$keywords);
		if(strlen($keywords))
			$a->page['htmlhead'] .= '<meta name="keywords" content="' . $keywords . '" />' . "\r\n" ;
	}

	$a->page['htmlhead'] .= '<meta name="dfrn-global-visibility" content="' . (($a->profile['net-publish']) ? 'true' : 'false') . '" />' . "\r\n" ;
	$a->page['htmlhead'] .= '<link rel="alternate" type="application/atom+xml" href="' . $a->get_baseurl() . '/dfrn_poll/' . $which .'" />' . "\r\n" ;
	$uri = urlencode('acct:' . $a->profile['nickname'] . '@' . $a->get_hostname() . (($a->path) ? '/' . $a->path : ''));
	$a->page['htmlhead'] .= '<link rel="lrdd" type="application/xrd+xml" href="' . $a->get_baseurl() . '/xrd/?uri=' . $uri . '" />' . "\r\n";
	header('Link: <' . $a->get_baseurl() . '/xrd/?uri=' . $uri . '>; rel="lrdd"; type="application/xrd+xml"', false);
  	
	$dfrn_pages = array('request', 'confirm', 'notify', 'poll');
	foreach($dfrn_pages as $dfrn)
		$a->page['htmlhead'] .= "<link rel=\"dfrn-{$dfrn}\" href=\"".$a->get_baseurl()."/dfrn_{$dfrn}/{$which}\" />\r\n";
	$a->page['htmlhead'] .= "<link rel=\"dfrn-poco\" href=\"".$a->get_baseurl()."/poco/{$which}\" />\r\n";

}


function profile_content(&$a, $update = 0) {

	if(get_config('system','block_public')  &&  (! local_user())  &&  (! remote_user())) {
		return login();
	}

	require_once($GLOBALS['xoops']->path("/modules/friendica/include/bbcode.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/security.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/conversation.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/acl_selectors.php"));
	$groups = array();

	$tab = 'posts';
	$o = '';

	if($update) {
		// Ensure we've got a profile owner if updating.
		$a->profile['profile_uid'] = $update;
	}
	else {
		if($a->profile['profile_uid'] == local_user()) {
			nav_set_selected('home');
		}
	}

	$contact = null;
	$remote_contact = false;

	if(remote_user()) {
		$contact_id = $_SESSION['visitor_id'];
		$groups = init_groups_visitor($contact_id);
		$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE `id` = %d AND `uid` = %d LIMIT 1",
			intval($contact_id),
			intval($a->profile['profile_uid'])
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

	$is_owner = ((local_user())  &&  (local_user() == $a->profile['profile_uid']) ? true : false);

	if($a->user['hidewall']  &&  (! $is_owner)  &&  (! $remote_contact)) {
		notice( t('Access to this profile has been restricted.') . EOL);
		return;
	}

	
	if(! $update) {
		if(x($_GET,'tab'))
			$tab = notags(trim($_GET['tab']));

		$o.=profile_tabs($a, $is_owner, $a->profile['nickname']);


		if($tab === 'profile') {
			require_once($GLOBALS['xoops']->path("/modules/friendica/include/profile_advanced.php"));
			$o .= advanced_profile($a);
			call_hooks('profile_advanced',$o);
			return $o;
		}

		if(x($_SESSION,'new_member')  &&  $_SESSION['new_member']  &&  $is_owner)
			$o .= '<a href="newmember" id="newmember-tips" style="font-size: 1.2em;"><b>' . t('Tips for New Members') . '</b></a>' . EOL;

		$commpage = (($a->profile['page-flags'] == PAGE_COMMUNITY) ? true : false);
		$commvisitor = (($commpage  &&  $remote_contact == true) ? true : false);

		$celeb = ((($a->profile['page-flags'] == PAGE_SOAPBOX) || ($a->profile['page-flags'] == PAGE_COMMUNITY)) ? true : false);

		if(can_write_wall($a,$a->profile['profile_uid'])) {

			$x = array(
				'is_owner' => $is_owner,
            	'allow_location' => ((($is_owner || $commvisitor)  &&  $a->profile['allow_location']) ? true : false),
	            'default_location' => (($is_owner) ? $a->user['default-location'] : ''),
    	        'nickname' => $a->profile['nickname'],
        	    'lockstate' => (((is_array($a->user)  &&  ((strlen($a->user['allow_cid'])) || (strlen($a->user['allow_gid'])) || (strlen($a->user['deny_cid'])) || (strlen($a->user['deny_gid']))))) ? 'lock' : 'unlock'),
            	'acl' => (($is_owner) ? populate_acl($a->user, $celeb) : ''),
	            'bang' => '',
    	        'visitor' => (($is_owner || $commvisitor) ? 'block' : 'none'),
        	    'profile_uid' => $a->profile['profile_uid']
        	);

        	$o .= status_editor($a,$x);
		}

	}


	/**
	 * Get permissions SQL - if $remote_contact is true, our remote user has been pre-verified AND we already have fetched his/her groups
	 */

	$sql_extra = item_permissions_sql($a->profile['profile_uid'],$remote_contact,$groups);


	if($update) {

		$r = q("SELECT distinct(parent) AS `item_id`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`uid` AS `contact-uid`
			FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`contact-id`
			WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`visible` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deleted` = 0
			and `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`moderated` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`unseen` = 1
			AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pending` = 0
			AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`wall` = 1
			$sql_extra
			ORDER BY `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`created` DESC",
			intval($a->profile['profile_uid'])
		);

	}
	else {

		$r = q("SELECT COUNT(*) AS `total`
			FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`contact-id`
			WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`visible` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deleted` = 0
			and `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`moderated` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pending` = 0 
			AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`parent` AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`wall` = 1
			$sql_extra ",
			intval($a->profile['profile_uid'])
		);

		if(count($r)) {
			$a->set_pager_total($r[0]['total']);
			$a->set_pager_itemspage(40);
		}

		$pager_sql = sprintf(" LIMIT %d, %d ",intval($a->pager['start']), intval($a->pager['itemspage']));

		$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id` AS `item_id`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`uid` AS `contact-uid`
			FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`contact-id`
			WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`visible` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deleted` = 0
			and `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`moderated` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pending` = 0
			AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`parent` AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`wall` = 1
			$sql_extra
			ORDER BY `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`created` DESC $pager_sql ",
			intval($a->profile['profile_uid'])

		);
	}

	$parents_arr = array();
	$parents_str = '';

	if(count($r)) {
		foreach($r as $rr)
			$parents_arr[] = $rr['item_id'];
		$parents_str = implode(', ', $parents_arr);
 
		$items = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.*, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id` AS `item_id`, 
			`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`name`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` , `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`url`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`network`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`rel`, 
			`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`thumb`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`self`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`writable`, 
			`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` AS `cid`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`uid` AS `contact-uid`
			FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` , `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` 
			WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`visible` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deleted` = 0
			and `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`moderated` = 0
			AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`contact-id`
			AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pending` = 0
			AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`parent` IN ( %s )
			$sql_extra ",
			intval($a->profile['profile_uid']),
			dbesc($parents_str)
		);
		
		$items = conv_sort($items,'created');
	} else {
		$items = array();
	}

	if($is_owner  &&  ! $update) {
		$o .= get_birthdays();
		$o .= get_events();
	}

	if((! $update)  &&  ($tab === 'posts')) {

		// This is ugly, but we can't pass the profile_uid through the session to the ajax updater,
		// because browser prefetching might change it on us. We have to deliver it with the page.

		$o .= '<div id="live-profile"></div>' . "\r\n";
		$o .= "<script> var profile_uid = " . $a->profile['profile_uid'] 
			. "; var netargs = '?f='; var profile_page = " . $a->pager['page'] . "; </script>\r\n";
	}


	if($is_owner) {
		$r = q("UPDATE  `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` SET `unseen` = 0 
			WHERE `wall` = 1 AND `unseen` = 1 AND `uid` = %d",
			intval(local_user())
		);
	}

	$o .= conversation($a,$items,'profile',$update);

	if(! $update) {
		$o .= paginate($a);
	}

	return $o;
}
