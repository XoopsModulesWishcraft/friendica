<?php

require_once($GLOBALS['xoops']->path("/modules/friendica/mod/friendica.php"));

function friendika_init(&$a) {
	friendica_init($a);
}

function friendika_content(&$a) {
	return friendica_content($a);
}
