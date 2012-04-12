<status>{{ if $status }}
    <created_at>$status.created_at</created_at>
    <id>$status.id</id>
    <text>$status.text</text>
    <source>$status.source</source>
    <truncated>$status.truncated</truncated>
    <in_reply_to_status_id>$status.in_reply_to_status_id</in_reply_to_status_id>
    <in_reply_to_user_id>$status.in_reply_to_user_id</in_reply_to_user_id>
    <favorited>$status.favorited</favorited>
    <in_reply_to_screen_name>$status.in_reply_to_screen_name</in_reply_to_screen_name>
    <geo>$status.geo</geo>
    <coordinates>$status.coordinates</coordinates>
    <place>$status.place</place>
    <contributors>$status.contributors</contributors>
	<user>
	  <id>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.id</id>
	  <name>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.name</name>
	  <screen_name>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.screen_name</screen_name>
	  <location>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.location</location>
	  <description>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.description</description>
	  <profile_image_url>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.profile_image_url</profile_image_url>
	  <url>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.url</url>
	  <protected>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.protected</protected>
	  <followers_count>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.followers</followers_count>
	  <profile_background_color>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.profile_background_color</profile_background_color>
  	  <profile_text_color>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.profile_text_color</profile_text_color>
  	  <profile_link_color>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.profile_link_color</profile_link_color>
  	  <profile_sidebar_fill_color>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.profile_sidebar_fill_color</profile_sidebar_fill_color>
  	  <profile_sidebar_border_color>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.profile_sidebar_border_color</profile_sidebar_border_color>
  	  <friends_count>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.friends_count</friends_count>
  	  <created_at>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.created_at</created_at>
  	  <favourites_count>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.favourites_count</favourites_count>
  	  <utc_offset>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.utc_offset</utc_offset>
  	  <time_zone>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.time_zone</time_zone>
  	  <profile_background_image_url>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.profile_background_image_url</profile_background_image_url>
  	  <profile_background_tile>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.profile_background_tile</profile_background_tile>
  	  <profile_use_background_image>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.profile_use_background_image</profile_use_background_image>
  	  <notifications></notifications>
  	  <geo_enabled>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.geo_enabled</geo_enabled>
  	  <verified>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.verified</verified>
  	  <following></following>
  	  <statuses_count>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.statuses_count</statuses_count>
  	  <lang>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.lang</lang>
  	  <contributors_enabled>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.contributors_enabled</contributors_enabled>
	  </user>
{{ endif }}</status>
