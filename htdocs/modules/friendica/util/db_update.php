<?php
/**
* @package util
*/

/* 
* require boot.php
*/
require_once($GLOBALS['xoops']->path("/modules/friendica/boot.php"));

$a = new App;
@include_once($GLOBALS['xoops']->path("/modules/friendica/include/.htconfig.php"));

$lang = get_language();
load_translation_table($lang);

require_once($GLOBALS['xoops']->path("/modules/friendica/include/dba.php"));
$db = new dba($db_host, $db_user, $db_pass, $db_data, false);
        unset($db_host, $db_user, $db_pass, $db_data);

$build = get_config('system','build');

echo "Old DB VERSION: " . $build . "\n";
echo "New DB VERSION: " . DB_UPDATE_VERSION . "\n";


if($build != DB_UPDATE_VERSION) {
	echo "Updating database...";
	check_config();
	echo "Done\n";
}

