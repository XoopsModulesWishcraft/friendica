<?php

/**
 *
 * MySQL database class
 *
 * For debugging, insert 'dbg(1);' anywhere in the program flow.
 * dbg(0); will turn it off. Logging is performed at LOGGER_DATA level.
 * When logging, all binary info is converted to text and html entities are escaped so that 
 * the debugging stream is safe to view within both terminals and web pages.
 *
 */
 
if(! class_exists('dba')) { 
class dba {

	private $debug = 0;
	private $db;
	public  $mysqli = true;
	public  $connected = false;
	public  $error = false;

	function __construct($server,$user,$pass,$db,$install = false) {

		$server = trim($server);
		$user = trim($user);
		$pass = trim($pass);
		$db = trim($db);

		if (!is_object($GLOBAL['xoopsDB'])){
			$this->connected = false;
			$this->db = null;
			return;			
		}
		if(is_object($GLOBAL['xoopsDB'])) {
			$this->db = $GLOBAL['xoopsDB'];
			$this->connected = true;
		}
		if(! $this->connected) {
			$this->db = null;
			if(! $install)
				system_unavailable();
		}
	}

	public function getdb() {
		return $this->db;
	}

	public function q($sql) {
		
		if((! $this->db) || (! $this->connected))
			return false;
		
		$result = @$this->db->queryF($sql);
		
		if($this->debug) {

			$mesg = '';

			if($this->mysqli) {
				if($this->db->errno)
					logger('dba: ' . $this->db->error);
			}
			elseif(mysql_errno($this->db))
				logger('dba: ' . mysql_error($this->db));

			if($result === false)
				$mesg = 'false';
			elseif($result === true)
				$mesg = 'true';
			else {
				$mesg = $result->getRowNum . ' results' . EOL;
			}
    
			$str =  'SQL = ' . printable($sql) . EOL . 'SQL returned ' . $mesg . EOL;

			logger('dba: ' . $str );
		}
		else {

			/**
			 * If dbfail.out exists, we will write any failed calls directly to it,
			 * regardless of any logging that may or may nor be in effect.
			 * These usually indicate SQL syntax errors that need to be resolved.
			 */

			if($result === false) {
				logger('dba: ' . printable($sql) . ' returned false.');
				if(file_exists('dbfail.out'))
					file_put_contents('dbfail.out', printable($sql) . ' returned false' . "\n", FILE_APPEND);
			}
		}

		if(($result === true) || ($result === false))
			return $result;

		$r = array();
		if($result->getRowsNum()) {
			while($x = $this->db->fetchArray($result))
					$r[] = $x;
			unset($result);
		}
		    
		if($this->debug)
			logger('dba: ' . printable(print_r($r, true)), LOGGER_DATA);
		return($r);
	}

	public function dbg($dbg) {
		$this->debug = $dbg;
	}

	public function escape($str) {
		if($this->db  &&  $this->connected) {
			return @$this->db->quote($str);
		}
	}

	function __destruct() {
		if ($this->db) 
			$this->db->close();
	}
}}

if(! function_exists('printable')) {
function printable($s) {
	$s = preg_replace("~([\x01-\x08\x0E-\x0F\x10-\x1F\x7F-\xFF])~",".", $s);
	$s = str_replace("\x00",'.',$s);
	if(x($_SERVER,'SERVER_NAME'))
		$s = escape_tags($s);
	return $s;
}}

// Procedural functions
if(! function_exists('dbg')) { 
function dbg($state) {
	global $db;
	if($db)
	$db->dbg($state);
}}

if(! function_exists('dbesc')) { 
function dbesc($str) {
	global $db;
	if($db  &&  $db->connected)
		return($db->escape($str));
	else
		return("'".str_replace("'","\\'",$str)."'");
}}



// Function: q($sql,$args);
// Description: execute SQL query with printf style args.
// Example: $r = q("SELECT * FROM `%s` WHERE `uid` = %d",
//                   'user', 1);

if(! function_exists('q')) { 
function q($sql) {

	global $db;
	$args = func_get_args();
	unset($args[0]);

	if($db  &&  $db->connected) {
		$ret = $db->q(vsprintf($sql,$args));
		return $ret;
	}

	/**
	 *
	 * This will happen occasionally trying to store the 
	 * session data after abnormal program termination 
	 *
	 */
	logger('dba: no database: ' . print_r($args,true));
	return false; 

}}

/**
 *
 * Raw db query, no arguments
 *
 */

if(! function_exists('dbq')) { 
function dbq($sql) {

	global $db;
	if($db  &&  $db->connected)
		$ret = $db->q($sql);
	else
		$ret = false;
	return $ret;
}}


// Caller is responsible for ensuring that any integer arguments to 
// dbesc_array are actually integers and not malformed strings containing
// SQL injection vectors. All integer array elements should be specifically 
// cast to int to avoid trouble. 


if(! function_exists('dbesc_array_cb')) {
function dbesc_array_cb(&$item, $key) {
	if(is_string($item))
		$item = dbesc($item);
}}


if(! function_exists('dbesc_array')) {
function dbesc_array(&$arr) {
	if(is_array($arr)  &&  count($arr)) {
		array_walk($arr,'dbesc_array_cb');
	}
}}		


