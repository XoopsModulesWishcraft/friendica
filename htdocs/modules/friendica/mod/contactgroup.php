<?php

require_once($GLOBALS['xoops']->path("/modules/friendica/include/group.php"));

function contactgroup_content(&$a) {


	if(! local_user()) {
		killme();
	}

	if(($a->argc > 2)  &&  intval($a->argv[1])  &&  intval($a->argv[2])) {
		$r = q("SELECT `id` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE `id` = %d AND `uid` = %d AND `self` = 0 AND `blocked` = 0 AND `pending` = 0 LIMIT 1",
			intval($a->argv[2]),
			intval(local_user())
		);
		if(count($r))
			$change = intval($a->argv[2]);
	}

	if(($a->argc > 1)  &&  (intval($a->argv[1]))) {

		$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "` WHERE `id` = %d AND `uid` = %d AND `deleted` = 0 LIMIT 1",
			intval($a->argv[1]),
			intval(local_user())
		);
		if(! count($r)) {
			killme();
		}

		$group = $r[0];
		$members = group_get_members($group['id']);
		$preselected = array();
		if(count($members))	{
			foreach($members as $member)
				$preselected[] = $member['id'];
		}

		if($change) {
			if(in_array($change,$preselected)) {
				group_rmv_member(local_user(),$group['name'],$change);
			}
			else {
				group_add_member(local_user(),$group['name'],$change);
			}
		}
	}

	killme();
}