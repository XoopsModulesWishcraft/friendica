<?php

/**
 * Zot endpoint
 */


require_once($GLOBALS['xoops']->path("/modules/friendica/include/salmon.php"));
require_once($GLOBALS['xoops']->path("/modules/friendica/include/crypto.php"));
// not yet ready for prime time
//require_once($GLOBALS['xoops']->path("/modules/friendica/include/zot.php"));
	
function post_post(&$a) {

	$bulk_delivery = false;

	if($a->argc == 1) {
		$bulk_delivery = true;
	}
	else {
		$nickname = $a->argv[2];
		$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE `nickname` = %s 
				AND `account_expired` = 0 LIMIT 1",
			dbesc($nickname)
		);
		if(! count($r))
			http_status_exit(500);

		$importer = $r[0];
	}

	$xml = file_get_contents('php://input');

	logger('mod-post: new zot: ' . $xml, LOGGER_DATA);

	if(! $xml)
		http_status_exit(500);

	$msg = zot_decode($importer,$xml);

	logger('mod-post: decoded msg: ' . print_r($msg,true), LOGGER_DATA);

	if(! is_array($msg))
		http_status_exit(500);

	$ret = 0;
	$ret = zot_incoming($bulk_delivery, $importer,$msg);
	http_status_exit(($ret) ? $ret : 200);
	// NOTREACHED
}

