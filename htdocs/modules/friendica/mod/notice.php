<?php
	/* identi.ca -> friendica items permanent-url compatibility */
	
	function notice_init(&$a){
		$id = $a->argv[1];
		$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.nickname FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` LEFT JOIN item ON item.uid=`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.uid WHERE item.id=%d",
				intval($id)
				);
		if (count($r)){
			$nick = $r[0]['nickname'];
			$url = $a->get_baseurl()."/display/$nick/$id";
			goaway($url);
		} else {
			$a->error = 404;
			notice( t('Item not found.') . EOL);

		}
		return;

	}
