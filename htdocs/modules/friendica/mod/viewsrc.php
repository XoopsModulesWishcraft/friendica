<?php


function viewsrc_content(&$a) {

	if(! local_user()) {
		notice( t('Access denied.') . EOL);
		return;
	}

	$item_id = (($a->argc > 1) ? intval($a->argv[1]) : 0);

	if(! $item_id) {
		$a->error = 404;
		notice( t('Item not found.') . EOL);
		return;
	}

	$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`body` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` 
		WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid` = %d AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`visible` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deleted` = 0
		and `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`moderated` = 0
		AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id` = %s LIMIT 1",
		intval(local_user()),
		dbesc($item_id)
	);

	if(count($r))
		$o .= str_replace("\n",'<br />',$r[0]['body']);
	return $o;
}

