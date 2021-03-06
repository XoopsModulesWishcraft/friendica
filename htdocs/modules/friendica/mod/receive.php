<?php

/**
 * Diaspora endpoint
 */


require_once($GLOBALS['xoops']->path("/modules/friendica/include/salmon.php"));
require_once($GLOBALS['xoops']->path("/modules/friendica/include/crypto.php"));
require_once($GLOBALS['xoops']->path("/modules/friendica/include/diaspora.php"));

	
function receive_post(&$a) {

	$public = false;

	if(($a->argc == 2)  &&  ($a->argv[1] === 'public')) {
		$public = true;
	}
	else {

		if($a->argc != 3 || $a->argv[1] !== 'users')
			http_status_exit(500);

		$guid = $a->argv[2];

		$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE `guid` = %s AND `account_expired` = 0 LIMIT 1",
			dbesc($guid)
		);
		if(! count($r))
			http_status_exit(500);

		$importer = $r[0];
	}

	// It is an application/x-www-form-urlencoded

	$xml = urldecode($_POST['xml']);

	logger('mod-diaspora: new salmon ' . $xml, LOGGER_DATA);

	if(! $xml)
		http_status_exit(500);

	$msg = diaspora_decode($importer,$xml);

	logger('mod-diaspora: decoded msg: ' . print_r($msg,true), LOGGER_DATA);

	if(! is_array($msg))
		http_status_exit(500);

	$ret = 0;
	if($public)
		diaspora_dispatch_public($msg);
	else
		$ret = diaspora_dispatch($importer,$msg);

	http_status_exit(($ret) ? $ret : 200);
	// NOTREACHED
}

