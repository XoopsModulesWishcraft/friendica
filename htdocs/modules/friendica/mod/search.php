<?php

function search_saved_searches() {

	$o = '';

	$r = q("SELECT `id`, `term` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "search") . "` WHERE `uid` = %d",
		intval(local_user())
	);

	if(count($r)) {
		$o .= '<div id="saved-search-list" class="widget">';
		$o .= '<h3>' . t('Saved Searches') . '</h3>' . "\r\n";
		$o .= '<ul id="saved-search-ul">' . "\r\n";
		foreach($r as $rr) {
			$o .= '<li class="saved-search-li clear"><a href="search/?f=&remove=1&search=' . $rr['term'] . '" class="icon drophide savedsearchdrop" title="' . t('Remove term') . '" onclick="return confirmDelete();" onmouseover="imgbright(this);" onmouseout="imgdull(this);" ></a> <a href="search/?f=&search=' . $rr['term'] . '" class="savedsearchterm" >' . $rr['term'] . '</a></li>' . "\r\n";
		}
		$o .= '</ul><div class="clear"></div></div>' . "\r\n";
	}		

	return $o;

}


function search_init(&$a) {

	$search = ((x($_GET,'search')) ? notags(trim(rawurldecode($_GET['search']))) : '');

	if(local_user()) {
		if(x($_GET,'save')  &&  $search) {
			$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "search") . "` WHERE `uid` = %d AND `term` = %s LIMIT 1",
				intval(local_user()),
				dbesc($search)
			);
			if(! count($r)) {
				q("INSERT INTO `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "search") . "` ( `uid`, `term` ) values ( %d, %s) ",
					intval(local_user()),
					dbesc($search)
				);
			}
		}
		if(x($_GET,'remove')  &&  $search) {
			q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "search") . "` WHERE `uid` = %d AND `term` = %s LIMIT 1",
				intval(local_user()),
				dbesc($search)
			);
		}

		$a->page['aside'] .= search_saved_searches();

	}
	else
		unset($_SESSION['theme']);



}



function search_post(&$a) {
	if(x($_POST,'search'))
		$a->data['search'] = $_POST['search'];
}


function search_content(&$a) {

	if((get_config('system','block_public'))  &&  (! local_user())  &&  (! remote_user())) {
		notice( t('Public access denied.') . EOL);
		return;
	}
	
	nav_set_selected('search');

	require_once($GLOBALS['xoops']->path("/modules/friendica/include/bbcode.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/security.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/conversation.php"));

	$o = '<div id="live-search"></div>' . "\r\n";

	$o .= '<h3>' . t('Search This Site') . '</h3>';

	if(x($a->data,'search'))
		$search = notags(trim($a->data['search']));
	else
		$search = ((x($_GET,'search')) ? notags(trim(rawurldecode($_GET['search']))) : '');

	$o .= search($search,'search-box','/search',((local_user()) ? true : false));

	if(! $search)
		return $o;

	// Here is the way permissions work in the search module...
	// Only public wall posts can be shown
	// OR your own posts if you are a logged in member

	$s_regx  = sprintf("AND ( `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`body` REGEXP %s OR `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`tag` REGEXP %s )", 
		dbesc(preg_quote($search)), dbesc('\\]' . preg_quote($search) . '\\['));

	$search_alg = $s_regx;

	$r = q("SELECT COUNT(*) AS `total`
		FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`contact-id` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`uid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid`
		WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`visible` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deleted` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`moderated` = 0
		AND (( `wall` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`allow_cid` = ''  AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`allow_gid` = '' AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deny_cid` = '' AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deny_gid` = '' AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`hidewall` = 0) 
			OR `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid` = %d )
		AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pending` = 0
		$search_alg ",
		intval(local_user())
	);

	if(count($r))
		$a->set_pager_total($r[0]['total']);

	if(! $r[0]['total']) {
		info( t('No results.') . EOL);
		return $o;
	}

	$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.*, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id` AS `item_id`, 
		`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`name`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` , `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`url`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`rel`,
		`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`network`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`thumb`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`self`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`writable`, 
		`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` AS `cid`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`uid` AS `contact-uid`,
		`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`nickname`
		FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`contact-id`
		LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`uid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid`
		WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`visible` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deleted` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`moderated` = 0
		AND (( `wall` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`allow_cid` = ''  AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`allow_gid` = '' AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deny_cid` = '' AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deny_gid` = '' AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`private` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`hidewall` = 0 ) 
			OR `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid` = %d )
		AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pending` = 0
		$search_alg
		ORDER BY `received` DESC LIMIT %d , %d ",
		intval(local_user()),
		intval($a->pager['start']),
		intval($a->pager['itemspage'])

	);

	$o .= '<h2>Search results for: ' . $search . '</h2>';

	$o .= conversation($a,$r,'search',false);

	$o .= paginate($a);

	return $o;
}

