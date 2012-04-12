<?php


require_once($GLOBALS['xoops']->path("/modules/friendica/library/openid.php"));


function openid_content(&$a) {

	$noid = get_config('system','no_openid');
	if($noid)
		goaway(z_root());

	if((x($_GET,'openid_mode'))  &&  (x($_SESSION,'openid'))) {
		$openid = new LightOpenID;

		if($openid->validate()) {

			if(x($_SESSION,'register')) {
				unset($_SESSION['register']);
				$args = '';
				$attr = $openid->getAttributes();
				if(is_array($attr)  &&  count($attr)) {
					foreach($attr as $k => $v) {
						if($k === 'namePerson/friendly')
							$nick = notags(trim($v));
						if($k === 'namePerson/first')
							$first = notags(trim($v));
						if($k === 'namePerson')
							$args .= '&username=' . notags(trim($v));
						if($k === 'contact/email')
							$args .= '&email=' . notags(trim($v));
						if($k === 'media/image/aspect11')
							$photosq = bin2hex(trim($v));
						if($k === 'media/image/default')
							$photo = bin2hex(trim($v));
					}
				}
				if($nick)
					$args .= '&nickname=' . $nick;
				elseif($first)
					$args .= '&nickname=' . $first;

				if($photosq)
					$args .= '&photo=' . $photosq;
				elseif($photo)
					$args .= '&photo=' . $photo;

				$args .= '&openid_url=' . notags(trim($_SESSION['openid']));
				if($a->config['register_policy'] != REGISTER_CLOSED)
					goaway($a->get_baseurl() . '/register' . $args);
				else
					goaway(z_root());

				// NOTREACHED
			} 


			$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.*, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`pubkey` AS `upubkey`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.`prvkey` AS `uprvkey`
				FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE `openid` = %s AND `blocked` = 0 AND `account_expired` = 0 AND `verified` = 1 LIMIT 1",
				dbesc($_SESSION['openid'])
			);
			if(! count($r)) {
				notice( t('Login failed.') . EOL );
				goaway(z_root());
  			}
			unset($_SESSION['openid']);

			require_once($GLOBALS['xoops']->path("/modules/friendica/include/security.php"));
			authenticate_success($r[0],true,true);

			// just in case there was no return url SET 
			// AND we fell through

			goaway(z_root());
		}
	}
	notice( t('Login failed.') . EOL);
	goaway(z_root());
	// NOTREACHED
}
