<?php
/* ACL selector json backend */
require_once($GLOBALS['xoops']->path("/modules/friendica/include/acl_selectors.php"));

function acl_init(&$a){
	if(!local_user())
		return "";


	$start = (x($_POST,'start')?$_POST['start']:0);
	$count = (x($_POST,'count')?$_POST['count']:100);
	$search = (x($_POST,'search')?$_POST['search']:"");
	$type = (x($_POST,'type')?$_POST['type']:"");
	

	if ($search!=""){
		$sql_extra = "AND `name` LIKE " . dbesc("%%".$search."%%");
		$sql_extra2 = "AND ( `attag` LIKE " . dbesc("%%".$search."%%") . ' OR `name` LIKE ' . dbesc("%%".$search."%%") . ' OR `nick` LIKE ' . dbesc("%%".$search."%%") . ")";
	} else {
		$sql_extra = $sql_extra2 = "";
	}
	
	// count groups AND contacts
	if ($type=='' || $type=='g'){
		$r = q("SELECT COUNT( `id` ) AS g FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "` WHERE `deleted` = 0 AND `uid` = %d $sql_extra",
			intval(local_user())
		);
		$group_count = (int)$r[0]['g'];
	} else {
		$group_count = 0;
	}
	
	if ($type=='' || $type=='c'){
		$r = q("SELECT COUNT( `id` ) AS c FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` 
				WHERE `uid` = %d AND `self` = 0 
				AND `blocked` = 0 AND `pending` = 0 
				AND `notify` != '' $sql_extra" ,
			intval(local_user())
		);
		$contact_count = (int)$r[0]['c'];
	} else {
		$contact_count = 0;
	}
	
	$tot = $group_count+$contact_count;
	
	$groups = array();
	$contacts = array();
	
	if ($type=='' || $type=='g'){
		
		$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "`.`id`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "`.`name`, GROUP_CONCAT(DISTINCT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "`.`contact-id` SEPARATOR) as `uids` 
				FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group"). "` , `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "` 
				WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "`.`deleted` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "`.`uid` = %d 
					AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "`.`gid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "`.`id`
					$sql_extra
				GROUP BY `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "`.`id`
				ORDER BY `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "`.`name`
				LIMIT %d, %d",
			intval(local_user()),
			intval($start),
			intval($count)
		);

		foreach($r as $g){
//		logger('acl: group: ' . $g['name'] . ' members: ' . $g['uids']);		
			$groups[] = array(
				"type"  => "g",
				"photo" => "images/twopeople.png",
				"name"  => $g['name'],
				"id"	=> intval($g['id']),
				"uids"  => array_map("intval", explode(",",$g['uids'])),
				"link"  => ''
			);
		}
	}
	
	if ($type=='' || $type=='c'){
	
		$r = q("SELECT `id`, `name`, `nick`, `micro`, `network`, `url`, `attag` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` 
			WHERE `uid` = %d AND `self` = 0 AND `blocked` = 0 AND `pending` = 0 AND `notify` != ''
			$sql_extra2
			ORDER BY `name` ASC ",
			intval(local_user())
		);
		foreach($r as $g){
			$contacts[] = array(
				"type"  => "c",
				"photo" => $g['micro'],
				"name"  => $g['name'],
				"id"	=> intval($g['id']),
				"network" => $g['network'],
				"link" => $g['url'],
				"nick" => ($g['attag']) ? $g['attag'] : $g['nick'],
			);
		}
			
	}
	
	
	$items = array_merge($groups, $contacts);
	
	$o = array(
		'tot'	=> $tot,
		'start' => $start,
		'count'	=> $count,
		'items'	=> $items,
	);
	
	echo json_encode($o);

	killme();
}


