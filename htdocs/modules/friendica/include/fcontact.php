<?php



function fcontact_store($url,$name,$photo) {

	$nurl = str_replace(array('https:','//www.'), array('http:','//'), $url);

	$r = q("SELECT `id` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "` WHERE `url` = %s LIMIT 1",
		dbesc($nurl)
	);

	if(count($r))
		return $r[0]['id'];

	$r = q("INSERT INTO " . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "` ( `url`, `name`, `photo` ) VALUES ( %s, %s, %s ) ",
		dbesc($nurl),
		dbesc($name),
		dbesc($photo)
	);

	if($r) {
		$r = q("SELECT `id` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "` WHERE `url` = %s LIMIT 1",
			dbesc($nurl)
		);
		if(count($r))
			return $r[0]['id'];
	}

	return 0;
}

function ffinder_store($uid,$cid,$fid) {
	$r = q("INSERT INTO `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "ffinder") . "` ( `uid`, `cid`, `fid`) VALUES ( %d, %d, %d ) ",
		intval($uid),
		intval($cid),
		intval($fid)
	);
	return $r;
}

