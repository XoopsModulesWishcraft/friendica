<?php
	/**
	 *  cache api
	 */
	 
	class Cache {
		public static function get($key){
			$r = q("SELECT `v` FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "cache") . "` WHERE `k` = %s LIMIT 1",
				dbesc($key)
			);
			
			if (count($r)) return $r[0]['v'];
			return null;
		}
		
		public static function set($key,$value) {
			$r = q("SELECT * FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "cache") . "` WHERE `k` = %s LIMIT 1",
				dbesc($key)
			);
			if(count($r)) {
				q("UPDATE  `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "cache") . "` SET `v` = %s, `updated = %s WHERE `k` = %s LIMIT 1",
					dbesc($value),
					dbesc(datetime_convert()),
					dbesc($key));
			}
			else {
				q("INSERT INTO `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "cache") . "` ( `k`, `v`, `updated` ) VALUES (%s, %s, %s)",
					dbesc($key),
					dbesc($value),
					dbesc(datetime_convert()));
			}
		}
		
		public static function clear(){
			q("DELETE FROM `" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "cache") . "` WHERE `updated` < %s",
				dbesc(datetime_convert('UTC','UTC',"now - 30 days")));			
		}
		
	}
	 
