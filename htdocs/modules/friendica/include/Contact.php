<?php


// Included here for completeness, but this is a very dangerous operation.
// It is the caller's responsibility to confirm the requestor's intent and
// authorisation to do this.

function user_remove($uid) {
	if(! $uid)
		return;
	$a = get_app();
	logger('Removing user: ' . $uid);

	$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE uid = %d LIMIT 1", intval($uid));

	call_hooks('remove_user',$r[0]);


	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE `uid` = %d", intval($uid));
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group") . "` WHERE `uid` = %d", intval($uid));
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "group_member") . "` WHERE `uid` = %d", intval($uid));
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "intro") . "` WHERE `uid` = %d", intval($uid));
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "event") . "` WHERE `uid` = %d", intval($uid));
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` WHERE `uid` = %d", intval($uid));
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mail") . "` WHERE `uid` = %d", intval($uid));
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` WHERE `uid` = %d", intval($uid));
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile") . "` WHERE `uid` = %d", intval($uid));
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "profile_check") . "` WHERE `uid` = %d", intval($uid));
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "pconfig") . "` WHERE `uid` = %d", intval($uid));
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "` WHERE `uid` = %d", intval($uid));
	if($uid == local_user()) {
		unset($_SESSION['authenticated']);
		unset($_SESSION['uid']);
		goaway($a->get_baseurl());
	}
}


function contact_remove($id) {
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` WHERE `id` = %d LIMIT 1",
		intval($id)
	);
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "item") . "` WHERE `contact-id` = %d ",
		intval($id)
	);
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "photo") . "` WHERE `contact-id` = %d ",
		intval($id)
	);
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "mail") . "` WHERE `contact-id` = %d ",
		intval($id)
	);
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "event") . "` WHERE `cid` = %d ",
		intval($id)
	);
	q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "queue") . "` WHERE `cid` = %d ",
		intval($id)
	);

}


// `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` has refused to recognise us as a friend. We will start a countdown.
// If they still don't recognise us in 32 days, the relationship is over,
// AND we won't waste any more time trying to communicate with them.
// This provides for the possibility that their database is temporarily messed
// up or some other transient event AND that there's a possibility we could recover FROM it.
 
if(! function_exists('mark_for_death')) {
function mark_for_death($contact) {
	if($contact['term-date'] == '0000-00-00 00:00:00') {
		q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` SET `term-date` = %s WHERE `id` = %d LIMIT 1",
				dbesc(datetime_convert()),
				intval($contact['id'])
		);
	}
	else {
		$expiry = $contact['term-date'] . ' + 32 days ';
		if(datetime_convert() > datetime_convert('UTC','UTC',$expiry)) {

			// relationship is really truly dead. 

			contact_remove($contact['id']);

		}
	}

}}

if(! function_exists('unmark_for_death')) {
function unmark_for_death($contact) {
	// It's a miracle. Our dead `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` has inexplicably come back to life.
	q("UPDATE `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "contact") . "` SET `term-date` = %s WHERE `id` = %d LIMIT 1",
		dbesc('0000-00-00 00:00:00'),
		intval($contact['id'])
	);
}}

if(! function_exists('contact_photo_menu')){
function contact_photo_menu($contact) {

	$a = get_app();
	
	$contact_url="";
	$pm_url="";
	$status_link="";
	$photos_link="";
	$posts_link="";

	$sparkle = false;
	if($contact['network'] === NETWORK_DFRN) {
		$sparkle = true;
		$profile_link = $a->get_baseurl() . '/redir/' . $contact['id'];
	}
	else
		$profile_link = $contact['url'];

	if($profile_link === 'mailbox')
		$profile_link = '';

	if($sparkle) {
		$status_link = $profile_link . "?url=status";
		$photos_link = $profile_link . "?url=photos";
		$profile_link = $profile_link . "?url=profile";
		$pm_url = $a->get_baseurl() . '/message/new/' . $contact['id'];
	}

	$contact_url = $a->get_baseurl() . '/contacts/' . $contact['id'];
	$posts_link = $a->get_baseurl() . '/network/?cid=' . $contact['id'];

	$menu = Array(
		t("View status") => $status_link,
		t("View profile") => $profile_link,
		t("View photos") => $photos_link,		
		t("View recent") => $posts_link, 
		t("Edit contact") => $contact_url,
		t("Send PM") => $pm_url,
	);
	
	
	$args = array('contact' => $contact, 'menu' => $menu);
	
	call_hooks('contact_photo_menu', $args);
	
	$o = "";
	foreach($menu as $k=>$v){
		if ($v!="") {
			if(($k !== t("View recent"))  &&  ($k !== t("Send PM")))
				$o .= "<li><a target=\"redir\" href=\"$v\">$k</a></li>\n";
			else
				$o .= "<li><a href=\"$v\">$k</a></li>\n";
		}
	}
	return $o;
}}
