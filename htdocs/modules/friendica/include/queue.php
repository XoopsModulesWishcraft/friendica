<?php
require_once($GLOBALS['xoops']->path("/modules/friendica/boot.php"));
require_once($GLOBALS['xoops']->path("/modules/friendica/include/queue_fn.php"));

function queue_run($argv, $argc){
	global $a, $db;

	if(is_null($a)){
		$a = new App;
	}
  
	if(is_null($db)){
		@include_once($GLOBALS['xoops']->path("/modules/friendica/include/.htconfig.php"));
		require_once($GLOBALS['xoops']->path("/modules/friendica/include/dba.php"));
		$db = new dba($db_host, $db_user, $db_pass, $db_data);
		unset($db_host, $db_user, $db_pass, $db_data);
	};


	require_once($GLOBALS['xoops']->path("/modules/friendica/include/session.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/datetime.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/items.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/bbcode.php"));

	load_config('config');
	load_config('system');

	$a->set_baseurl(get_config('system','url'));

	load_hooks();

	if($argc > 1)
		$queue_id = intval($argv[1]);
	else
		$queue_id = 0;

	$deadguys = array();

	logger('queue: start');

	$interval = ((get_config('system','delivery_interval') === false) ? 2 : intval(get_config('system','delivery_interval')));

	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "deliverq") . "` WHERE 1");
	if(count($r)) {
		foreach($r as $rr) {
			logger('queue: `' . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "deliverq") . '` ');
			proc_run('php','include/delivery.php',$rr['cmd'],$rr['item'],$rr['contact']);
			if($interval)
				@time_sleep_until(microtime(true) + (float) $interval);
		}
	}

	$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue") . "`.*, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`name`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`uid` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue"). "`
		LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue") . "`.`cid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id`
		WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue") . "`.`created` < UTC_TIMESTAMP() - INTERVAL 3 DAY");
	if(count($r)) {
		foreach($r as $rr) {
			logger('Removing expired queue item for ' . $rr['name'] . ', uid=' . $rr['uid']);
			logger('Expired queue data :' . $rr['content'], LOGGER_DATA);
		}
		q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue") . "` WHERE `created` < UTC_TIMESTAMP() - INTERVAL 3 DAY");
	}
		
	if($queue_id)
		$r = q("SELECT `id` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue") . "` WHERE `id` = %d LIMIT 1",
			intval($queue_id)
		);
	else
		$r = q("SELECT `id` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue") . "` WHERE `last` < UTC_TIMESTAMP() - INTERVAL 15 MINUTE ");

	if(! count($r)){
		return;
	}

	if(! $queue_id)
		call_hooks('queue_predeliver', $a, $r);


	// delivery loop

	require_once($GLOBALS['xoops']->path("/modules/friendica/include/salmon.php"));
	require_once($GLOBALS['xoops']->path("/modules/friendica/include/diaspora.php"));

	foreach($r as $q_item) {

		// queue_predeliver hooks may have changed the queue db details, 
		// so check again if this entry still needs processing

		if($queue_id) {
			$qi = q("SELECT * FROM queue WHERE `id` = %d LIMIT 1",
				intval($queue_id)
			);
		}
		else {
			$qi = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue") . "` WHERE `id` = %d AND `last` < UTC_TIMESTAMP() - INTERVAL 15 MINUTE ",
				intval($q_item['id'])
			);
		}
		if(! count($qi))
			continue;


		$c = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE `id` = %d LIMIT 1",
			intval($qi[0]['cid'])
		);
		if(! count($c)) {
			remove_queue_item($q_item['id']);
			continue;
		}
		if(in_array($c[0]['notify'],$deadguys)) {
				logger('queue: skipping known dead url: ' . $c[0]['notify']);
				update_queue_time($q_item['id']);
				continue;
		}

		$u = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.*, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`pubkey` AS `upubkey`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`prvkey` AS `uprvkey`
			FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE `uid` = %d LIMIT 1",
			intval($c[0]['uid'])
		);
		if(! count($u)) {
			remove_queue_item($q_item['id']);
			continue;
		}

		$data      = $qi[0]['content'];
		$public    = $qi[0]['batch'];
		$contact   = $c[0];
		$owner     = $u[0];

		$deliver_status = 0;

		switch($contact['network']) {
			case NETWORK_DFRN:
				logger('queue: dfrndelivery: item ' . $q_item['id'] . ' for ' . $contact['name']);
				$deliver_status = dfrn_deliver($owner,$contact,$data);

				if($deliver_status == (-1)) {
					update_queue_time($q_item['id']);
					$deadguys[] = $contact['notify'];
				}
				else {
					remove_queue_item($q_item['id']);
				}
				break;
			case NETWORK_OSTATUS:
				if($contact['notify']) {
					logger('queue: slapdelivery: item ' . $q_item['id'] . ' for ' . $contact['name']);
					$deliver_status = slapper($owner,$contact['notify'],$data);

					if($deliver_status == (-1))
						update_queue_time($q_item['id']);
					else
						remove_queue_item($q_item['id']);
				}
				break;
			case NETWORK_DIASPORA:
				if($contact['notify']) {
					logger('queue: diaspora_delivery: item ' . $q_item['id'] . ' for ' . $contact['name']);
					$deliver_status = diaspora_transmit($owner,$contact,$data,$public);

					if($deliver_status == (-1))
						update_queue_time($q_item['id']);
					else
						remove_queue_item($q_item['id']);
				}
				break;

			default:
				$params = array('owner' => $owner, 'contact' => $contact, 'queue' => $q_item, 'result' => false);
				call_hooks('queue_deliver', $a, $params);
		
				if($params['result'])
						remove_queue_item($q_item['id']);
				else
						update_queue_time($q_item['id']);
	
				break;

		}
	}
		
	return;

}

if (array_search(__file__,get_included_files())===0){
  queue_run($argv,$argc);
  killme();
}
