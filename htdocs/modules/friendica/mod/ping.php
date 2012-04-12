<?php
require_once($GLOBALS['xoops']->path("/modules/friendica/include/datetime.php"));


function ping_init(&$a) {

	header("Content-type: text/xml");
	
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
		<result>";

	$xmlhead="<"."?xml version='1.0' encoding='UTF-8' ?".">";
	if(local_user()){

		$firehose = intval(get_pconfig(local_user(),'system','notify_full'));

		$t = q("SELECT count(*) as total FROM notify WHERE uid = %d AND seen = 0",
			intval(local_user())
		);
		if($t  &&  intval($t[0]['total']) > 49) {
			$z = q("SELECT * FROM notify WHERE uid = %d
				and seen = 0 ORDER BY date desc LIMIT 0, 50",
				intval(local_user())
			);
		}
		else {
			$z1 = q("SELECT * FROM notify WHERE uid = %d
				and seen = 0 ORDER BY date desc LIMIT 0, 50",
				intval(local_user())
			);

			$z2 = q("SELECT * FROM notify WHERE uid = %d
				and seen = 1 ORDER BY date desc LIMIT 0, %d",
				intval(local_user()),
				intval(50 - intval($t[0]['total']))
			);
			$z = array_merge($z1,$z2);
		}



		$tags = array();
		$comments = array();
		$likes = array();
		$dislikes = array();
		$friends = array();
		$posts = array();
		$home = 0;
		$network = 0;

		$r = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`id`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`parent`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`verb`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`wall`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`author-name`, 
				`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`author-link`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`author-avatar`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`created`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`object`, 
				`pitem`.`author-name` AS `pname`, `pitem`.`author-link` AS `plink`
				FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` INNER JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` AS `pitem` ON  `pitem`.`id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`parent`
				WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`unseen` = 1 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`visible` = 1 AND
				 `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`deleted` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`uid` = %d 
				ORDER BY `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "`.`created` DESC",
			intval(local_user())
		);

		if(count($r)) {		

			foreach ($r as $it) {

				if($it['wall'])
					$home ++;
				else
					$network ++;

				switch($it['verb']){
					case ACTIVITY_TAG:
						$obj = parse_xml_string($xmlhead.$it['object']);
						$it['tname'] = $obj->content;
						$tags[] = $it;
						break;
					case ACTIVITY_LIKE:
						$likes[] = $it;
						break;
					case ACTIVITY_DISLIKE:
						$dislikes[] = $it;
						break;
					case ACTIVITY_FRIEND:
						$obj = parse_xml_string($xmlhead.$it['object']);
						$it['fname'] = $obj->title;			
						$friends[] = $it;
						break;
					default:
						if ($it['parent']!=$it['id']) { 
							$comments[] = $it;
						} else {
							if(! $it['wall'])
								$posts[] = $it;
						}
				}
			}
		}

		$intros1 = q("SELECT  `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`id`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`datetime`, 
			`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "`.`name`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "`.`url`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "`.`photo`
			FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro"). "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`fid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "fcontact") . "`.`id`
			WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`uid` = %d  AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`ignore` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`fid`!=0",
			intval(local_user())
		);
		$intros2 = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`id`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`datetime`, 
			`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`name`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`url`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`photo`
			FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro"). "` LEFT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`contact-id` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`id`
			WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`uid` = %d  AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`blocked` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`ignore` = 0 AND `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "`.`contact-id`!=0",
			intval(local_user())
		);
		
		$intro = count($intros1) + count($intros2);
		$intros = $intros1+$intros2;



		$myurl = $a->get_baseurl() . '/profile/' . $a->user['nickname'] ;
		$mails = q("SELECT *,  COUNT(*) AS `total` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mail") . "`
			WHERE `uid` = %d AND `seen` = 0 AND `from-url` != %s ",
			intval(local_user()),
			dbesc($myurl)
		);
		if($mails)
			$mail = $mails[0]['total'];
		
		if ($a->config['register_policy'] == REGISTER_APPROVE  &&  is_site_admin()){
			$regs = q("SELECT `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`name`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`url`, `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`micro`, `register`.`created`, COUNT(*) as `total` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` RIGHT JOIN `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "register") . "` ON `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "register") . "`.`uid` = `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`uid` WHERE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "`.`self` = 1");
			if($regs)
				$register = $regs[0]['total'];
		} else {
			$register = "0";
		}


		function xmlize($href, $name, $url, $photo, $date, $seen, $message){
			$notsxml = '<note href="%s" name="%s" url="%s" photo="%s" date="%s" seen="%s" >%s</note>';
			return sprintf ( $notsxml,
				xmlify($href), xmlify($name), xmlify($url), xmlify($photo), xmlify($date), xmlify($seen), xmlify($message)
			);
		}
		
		echo "<intro>$intro</intro>
				<mail>$mail</mail>
				<net>$network</net>
				<home>$home</home>";
		if ($register!=0) echo "<register>$register</register>";
		
		$tot = $mail+$intro+$register+count($comments)+count($likes)+count($dislikes)+count($friends)+count($posts)+count($tags);

		require_once($GLOBALS['xoops']->path("/modules/friendica/include/bbcode.php"));
		$sysnotify = 0;

		if($firehose) {
			echo '	<notif count="'.$tot.'">';
		}
		else {
			if(count($z)) {
				foreach($z as $zz) {
					if($zz['seen'] == 0)
						$sysnotify ++;
				}
			}						

			echo '	<notif count="'. $sysnotify .'">';
			if(count($z)) {
				foreach($z as $zz) {
					echo xmlize($a->get_baseurl() . '/notify/view/' . $zz['id'], $zz['name'],$zz['url'],$zz['photo'],relative_date($zz['date']), ($zz['seen'] ? 'notify-seen' : 'notify-unseen'), ($zz['seen'] ? '' : '&rarr; ') .strip_tags(bbcode($zz['msg'])));
				}
			}
		}

		if($firehose) {
			if ($intro>0){
				foreach ($intros as $i) { 
					echo xmlize( $a->get_baseurl().'/notifications/intros/'.$i['id'], $i['name'], $i['url'], $i['photo'], relative_date($i['datetime']), 'notify-unseen',t("{0} wants to be your friend") );
				};
			}
			if ($mail>0){
				foreach ($mails as $i) { 
					echo xmlize( $a->get_baseurl().'/message/'.$i['id'], $i['from-name'], $i['from-url'], $i['from-photo'], relative_date($i['created']), 'notify-unseen',t("{0} sent you a message") );
				};
			}
			if ($register>0){
				foreach ($regs as $i) { 
					echo xmlize( $a->get_baseurl().'/admin/users/', $i['name'], $i['url'], $i['micro'], relative_date($i['created']), 'notify-unseen',t("{0} requested registration") );
				};
			}

			if (count($comments)){
				foreach ($comments as $i) {
					echo xmlize( $a->get_baseurl().'/display/'.$a->user['nickname']."/".$i['parent'], $i['author-name'], $i['author-link'], $i['author-avatar'], relative_date($i['created']), 'notify-unseen',sprintf( t("{0} commented %s's post"), $i['pname'] ) );
				};
			}
			if (count($likes)){
				foreach ($likes as $i) {
					echo xmlize( $a->get_baseurl().'/display/'.$a->user['nickname']."/".$i['parent'], $i['author-name'], $i['author-link'], $i['author-avatar'], relative_date($i['created']), 'notify-unseen',sprintf( t("{0} liked %s's post"), $i['pname'] ) );
				};
			}
			if (count($dislikes)){
				foreach ($dislikes as $i) {
					echo xmlize( $a->get_baseurl().'/display/'.$a->user['nickname']."/".$i['parent'], $i['author-name'], $i['author-link'], $i['author-avatar'], relative_date($i['created']), 'notify-unseen',sprintf( t("{0} disliked %s's post"), $i['pname'] ) );
				};
			}
			if (count($friends)){
				foreach ($friends as $i) {
					echo xmlize($a->get_baseurl().'/display/'.$a->user['nickname']."/".$i['parent'],$i['author-name'],$i['author-link'], $i['author-avatar'], relative_date($i['created']), 'notify-unseen',sprintf( t("{0} is now friends with %s"), $i['fname'] ) );
				};
			}
			if (count($posts)){
				foreach ($posts as $i) {
					echo xmlize( $a->get_baseurl().'/display/'.$a->user['nickname']."/".$i['parent'], $i['author-name'], $i['author-link'], $i['author-avatar'], relative_date($i['created']), 'notify-unseen',sprintf( t("{0} posted") ) );
				};
			}
			if (count($tags)){
				foreach ($tags as $i) {
					echo xmlize( $a->get_baseurl().'/display/'.$a->user['nickname']."/".$i['parent'], $i['author-name'], $i['author-link'], $i['author-avatar'], relative_date($i['created']), 'notify-unseen',sprintf( t("{0} tagged %s's post with #%s"), $i['pname'], $i['tname'] ) );
				};
			}

			if (count($cit)){
				foreach ($cit as $i) {
					echo xmlize( $a->get_baseurl().'/display/'.$a->user['nickname']."/".$i['parent'], $i['author-name'], $i['author-link'], $i['author-avatar'], relative_date($i['created']), 'notify-unseen',t("{0} mentioned you in a post") );
				};
			}
		}

		echo "  </notif>";
	}
	echo " <sysmsgs>";

	if(x($_SESSION,'sysmsg')){
		foreach ($_SESSION['sysmsg'] as $m){
			echo "<notice>".xmlify($m)."</notice>";
		}
		unset($_SESSION['sysmsg']);
	}
	if(x($_SESSION,'sysmsg_info')){
		foreach ($_SESSION['sysmsg_info'] as $m){
			echo "<info>".xmlify($m)."</info>";
		}
		unset($_SESSION['sysmsg_info']);
	}
	
	echo " </sysmsgs>";
	echo"</result>
	";

	killme();
}

