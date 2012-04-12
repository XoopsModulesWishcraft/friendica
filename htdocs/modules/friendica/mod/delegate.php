<?php

function delegate_content(&$a) {

	if(! local_user()) {
		notice( t('Permission denied.') . EOL);
		return;
	}

	if($a->argc > 2  &&  $a->argv[1] === 'add'  &&  intval($a->argv[2])) {

		// delegated admins can view but not change delegation permissions

		if(x($_SESSION,'submanage')  &&  intval($_SESSION['submanage']))
			goaway($a->get_baseurl() . '/delegate');


		$id = $a->argv[2];

		$r = q("SELECT `nickname` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE uid = %d LIMIT 1",
			intval($id)
		);
		if(count($r)) {
			$r = q("SELECT id FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE uid = %d AND nurl = %s LIMIT 1",
				intval(local_user()),
				dbesc(normalise_link($a->get_baseurl() . '/profile/' . $r[0]['nickname']))
			);
			if(count($r)) {
				q("INSERT INTO `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "manage") . "` ( uid, mid ) values ( %d , %d ) ",
					intval($a->argv[2]),
					intval(local_user())
				);
			}
		}
		goaway($a->get_baseurl() . '/delegate');
	}

	if($a->argc > 2  &&  $a->argv[1] === 'remove'  &&  intval($a->argv[2])) {

		// delegated admins can view but not change delegation permissions

		if(x($_SESSION,'submanage')  &&  intval($_SESSION['submanage']))
			goaway($a->get_baseurl() . '/delegate');

		q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "manager") . "` WHERE uid = %d AND mid = %d LIMIT 1",
			intval($a->argv[2]),
			intval(local_user())
		);
		goaway($a->get_baseurl() . '/delegate');

	}

	$full_managers = array();

	// These people can manage this account/page with full privilege

	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE `email` = %s AND `password` = %s ",
		dbesc($a->user['email']),
		dbesc($a->user['password'])
	);
	if(count($r))
		$full_managers = $r;

	$delegates = array();

	// find everybody that currently has delegated management to this account/page

	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE uid in ( SELECT uid FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "manager") . "` WHERE mid = %d ) ",
		intval(local_user())
	);

	if(count($r))
		$delegates = $r;

	$uids = array();

	if(count($full_managers))
		foreach($full_managers as $rr)
			$uids[] = $rr['uid'];

	if(count($delegates))
		foreach($delegates as $rr)
			$uids[] = $rr['uid'];

	// find every `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` who might be a candidate for delegation

	$r = q("SELECT nurl FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE substring_index(contact.nurl,'/',3) = %s 
		and contact.uid = %d AND contact.self = 0 AND network = %s ",
		dbesc($a->get_baseurl()),
		intval(local_user()),
		dbesc(NETWORK_DFRN)
	); 

	if(! count($r)) {
		notice( t('No potential page delegates located.') . EOL);
		return;
	}

	$nicknames = array();

	if(count($r)) {
		foreach($r as $rr) {
			$nicknames[] = "'" . dbesc(basename($rr['nurl'])) . "'";
		}
	}

	$potentials = array();

	$nicks = implode(',',$nicknames);

	// get user records for all potential page delegates who are not already delegates or managers

	$r = q("SELECT `uid`, `username`, `nickname` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE nickname in ( $nicks )");

	if(count($r))
		foreach($r as $rr)
			if(! in_array($rr['uid'],$uids))
				$potentials[] = $rr;

	$o = replace_macros(get_markup_template('delegate.tpl'),array(
		'$header' => t('Delegate Page Management'),
		'$base' => $a->get_baseurl(),
		'$desc' => t('Delegates are able to manage all aspects of this account/page except for basic account settings. Please do not delegate your personal account to anybody that you do not trust completely.'),
		'$head_managers' => t('Existing Page Managers'),
		'$managers' => $full_managers,
		'$head_delegates' => t('Existing Page Delegates'),
		'$delegates' => $delegates,
		'$head_potentials' => t('Potential Delegates'),
		'$potentials' => $potentials,
		'$remove' => t('Remove'),
		'$add' => t('Add'),
		'$none' => t('No entries.')
	));


	return $o;


}