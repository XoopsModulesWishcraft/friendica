<?php

function msearch_post(&$a) {

	$perpage = (($_POST['n']) ? $_POST['n'] : 80);
	$page = (($_POST['p']) ? intval($_POST['p'] - 1) : 0);
	$startrec = (($page+1) * $perpage) - $perpage;

	$search = $_POST['s'];
	if(! strlen($search))
		killme();

	$r = q("SELECT COUNT(*) AS `total` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`uid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "`.`uid` WHERE `is-default` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`hidewall` = 0 AND `pub_keywords` LIKE (%s) ",
		dbesc('%'.$search.'%')
	);
	if(count($r))
		$total = $r[0]['total'];

	$r = q("SELECT `pub_keywords`, `username`, `nickname`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`uid` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`uid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "`.`uid` WHERE `is-default` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`hidewall` = 0 AND `pub_keywords` LIKE %s LIMIT %d , %d ",
		dbesc('%'.$search.'%'),
		intval($startrec),
		intval($perpage)
	);

	$results = array();
	if(count($r)) {
		foreach($r as $rr)
			$results[] = array(
				'name' => $rr['name'], 
				'url' => $a->get_baseurl() . '/profile/' . $rr['nickname'], 
				'photo' => $a->get_baseurl() . '/photo/avatar/' . $rr['uid'] . 'jpg',
				'tags' => str_replace(array(',','  '),array(' ',' '),$rr['pub_keywords'])
			);
	}

	$output = array('total' => $total, 'items_page' => $perpage, 'page' => $page + 1, 'results' => $results);

	echo json_encode($output);

	killme();

}