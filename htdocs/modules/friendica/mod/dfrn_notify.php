<?php

require_once($GLOBALS['xoops']->path("/modules/friendica/library/simplepie/simplepie.inc"));
require_once($GLOBALS['xoops']->path("/modules/friendica/include/items.php"));
require_once($GLOBALS['xoops']->path("/modules/friendica/include/event.php"));


function dfrn_notify_post(&$a) {

	$dfrn_id      = ((x($_POST,'dfrn_id'))      ? notags(trim($_POST['dfrn_id']))   : '');
	$dfrn_version = ((x($_POST,'dfrn_version')) ? (float) $_POST['dfrn_version']    : 2.0);
	$challenge    = ((x($_POST,'challenge'))    ? notags(trim($_POST['challenge'])) : '');
	$data         = ((x($_POST,'data'))         ? $_POST['data']                    : '');
	$key          = ((x($_POST,'key'))          ? $_POST['key']                     : '');
	$dissolve     = ((x($_POST,'dissolve'))     ? intval($_POST['dissolve'])        :  0);
	$perm         = ((x($_POST,'perm'))         ? notags(trim($_POST['perm']))      : 'r');

	$writable = (-1);
	if($dfrn_version >= 2.21) {
		$writable = (($perm === 'rw') ? 1 : 0);
	}

	$direction = (-1);
	if(strpos($dfrn_id,':') == 1) {
		$direction = intval(substr($dfrn_id,0,1));
		$dfrn_id = substr($dfrn_id,2);
	}

	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "challenge") . "` WHERE `dfrn-id` = %s AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "challenge") . "`. = %s LIMIT 1",
		dbesc($dfrn_id),
		dbesc($challenge)
	);
	if(! count($r)) {
		logger('dfrn_notify: could not match challenge to dfrn_id ' . $dfrn_id . ' challenge=' . $challenge);
		xml_status(3);
	}

	$r = q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "challenge") . "` WHERE `dfrn-id` = %s AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "challenge") . "`. = %s LIMIT 1",
		dbesc($dfrn_id),
		dbesc($challenge)
	);

	// find the local user who owns this relationship.

	$sql_extra = '';
	switch($direction) {
		case (-1):
			$sql_extra = sprintf(" AND ( `issued-id` = %s OR `dfrn-id` = %s ) ", dbesc($dfrn_id), dbesc($dfrn_id));
			break;
		case 0:
			$sql_extra = sprintf(" AND `issued-id` = %s AND `duplex` = 1 ", dbesc($dfrn_id));
			break;
		case 1:
			$sql_extra = sprintf(" AND `dfrn-id` = %s AND `duplex` = 1 ", dbesc($dfrn_id));
			break;
		default:
			xml_status(3);
			break; // NOTREACHED
	}
		 
	// be careful - $importer will contain both the `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`information for the contact
	// sending us the post, AND also the userinformation for the person receiving it.
	// since they are mixed together, it is easy to get them confused.

	$r = q("SELECT	`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.*, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`uid` AS `importer_uid`, 
					`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pubkey` AS `cpubkey`, 
					`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`prvkey` AS `cprvkey`, 
					`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`thumb` AS `thumb`, 
					`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`url` AS `url`,
					`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`name` AS `senderName`,
					`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.* 
			FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` 
			LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`uid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`uid`
			WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pending` = 0 
				AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`nickname` = %s AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`account_expired` = 0 $sql_extra LIMIT 1",
		dbesc($a->argv[1])
	);

	if(! count($r)) {
		logger('dfrn_notify: contact not found for dfrn_id ' . $dfrn_id);
		xml_status(3);
		//NOTREACHED
	}

	// $importer in this case contains the `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` record for the remote `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` joined with the user record of our `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`. 

	$importer = $r[0];

	if(($writable != (-1))  &&  ($writable != $importer['writable'])) {
		q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` SET `writable` = %d WHERE `id` = %d LIMIT 1",
			intval($writable),
			intval($importer['id'])
		);
		$importer['writable'] = $writable;
	}

	logger('dfrn_notify: received notify FROM ' . $importer['name'] . ' for ' . $importer['username']);
	logger('dfrn_notify: data: ' . $data, LOGGER_DATA);

	if($dissolve == 1) {

		/**
		 * Relationship is dissolved permanently
		 */

		require_once($GLOBALS['xoops']->path("/modules/friendica/include/Contact.php")); 
		contact_remove($importer['id']);
		logger('relationship dissolved : ' . $importer['name'] . ' dissolved ' . $importer['username']);
		xml_status(0);

	}

	if(strlen($key)) {
		$rawkey = hex2bin(trim($key));
		logger('rino: md5 raw key: ' . md5($rawkey));
		$final_key = '';

		if($dfrn_version >= 2.1) {
			if((($importer['duplex'])  &&  strlen($importer['cprvkey'])) || (! strlen($importer['cpubkey']))) {
				openssl_private_decrypt($rawkey,$final_key,$importer['cprvkey']);
			}
			else {
				openssl_public_decrypt($rawkey,$final_key,$importer['cpubkey']);
			}
		}
		else {
			if((($importer['duplex'])  &&  strlen($importer['cpubkey'])) || (! strlen($importer['cprvkey']))) {
				openssl_public_decrypt($rawkey,$final_key,$importer['cpubkey']);
			}
			else {
				openssl_private_decrypt($rawkey,$final_key,$importer['cprvkey']);
			}
		}

		logger('rino: received key : ' . $final_key);
		$data = aes_decrypt(hex2bin($data),$final_key);
		logger('rino: decrypted data: ' . $data, LOGGER_DATA);
	}


	$ret = local_delivery($importer,$data);
	xml_status($ret);

	// NOTREACHED
}


function dfrn_notify_content(&$a) {

	if(x($_GET,'dfrn_id')) {

		// initial communication FROM external contact, $direction is their direction.
		// If this is a duplex communication, ours will be the opposite.

		$dfrn_id = notags(trim($_GET['dfrn_id']));
		$dfrn_version = (float) $_GET['dfrn_version'];

		logger('dfrn_notify: new notification dfrn_id=' . $dfrn_id);

		$direction = (-1);
		if(strpos($dfrn_id,':') == 1) {
			$direction = intval(substr($dfrn_id,0,1));
			$dfrn_id = substr($dfrn_id,2);
		}

		$hash = random_string();

		$status = 0;

		$r = q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "challenge") . "` WHERE `expire` < " . intval(time()));

		$r = q("INSERT INTO `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "challenge") . "` ( `challenge`, `dfrn-id`, `expire` ) 
			VALUES( %s, %s, %d ) ",
			dbesc($hash),
			dbesc($dfrn_id),
			intval(time() + 90 )
		);

		logger('dfrn_notify: challenge=' . $hash, LOGGER_DEBUG );

		$sql_extra = '';
		switch($direction) {
			case (-1):
				$sql_extra = sprintf(" AND ( `issued-id` = %s OR `dfrn-id` = %s ) ", dbesc($dfrn_id), dbesc($dfrn_id));
				$my_id = $dfrn_id;
				break;
			case 0:
				$sql_extra = sprintf(" AND `issued-id` = %s AND `duplex` = 1 ", dbesc($dfrn_id));
				$my_id = '1:' . $dfrn_id;
				break;
			case 1:
				$sql_extra = sprintf(" AND `dfrn-id` = %s AND `duplex` = 1 ", dbesc($dfrn_id));
				$my_id = '0:' . $dfrn_id;
				break;
			default:
				$status = 1;
				break; // NOTREACHED
		}

		$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.*, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`nickname` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`uid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`uid`
				WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`pending` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`nickname` = %s 
				AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`account_expired` = 0 $sql_extra LIMIT 1",
				dbesc($a->argv[1])
		);

		if(! count($r))
			$status = 1;

		$challenge = '';
		$encrypted_id = '';
		$id_str = $my_id . '.' . mt_rand(1000,9999);

		$prv_key = trim($r[0]['prvkey']);
		$pub_key = trim($r[0]['pubkey']);
		$dplx = intval($r[0]['duplex']);

		if((($dplx)  &&  (strlen($prv_key))) || ((strlen($prv_key))  &&  (!(strlen($pub_key))))) {
			openssl_private_encrypt($hash,$challenge,$prv_key);
			openssl_private_encrypt($id_str,$encrypted_id,$prv_key);
		}
		elseif(strlen($pub_key)) {
			openssl_public_encrypt($hash,$challenge,$pub_key);
			openssl_public_encrypt($id_str,$encrypted_id,$pub_key);
		}
		else
			$status = 1;

		$challenge    = bin2hex($challenge);
		$encrypted_id = bin2hex($encrypted_id);

		$rino = ((function_exists('mcrypt_encrypt')) ? 1 : 0);

		$rino_enable = get_config('system','rino_encrypt');

		if(! $rino_enable)
			$rino = 0;


		header("Content-type: text/xml");

		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n" 
			. '<dfrn_notify>' . "\r\n"
			. "\t" . '<status>' . $status . '</status>' . "\r\n"
			. "\t" . '<dfrn_version>' . DFRN_PROTOCOL_VERSION . '</dfrn_version>' . "\r\n"
			. "\t" . '<rino>' . $rino . '</rino>' . "\r\n" 
			. "\t" . '<dfrn_id>' . $encrypted_id . '</dfrn_id>' . "\r\n" 
			. "\t" . '<challenge>' . $challenge . '</challenge>' . "\r\n"
			. '</dfrn_notify>' . "\r\n" ;

		killme();
	}

}