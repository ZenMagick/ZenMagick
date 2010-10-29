<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005 Joshua Dechant                                    |
// |                                                                      |   
// | Portions Copyright (c) 2004 The zen-cart developers                  |
// |                                                                      |   
// | http://www.zen-cart.com/index.php                                    |   
// |                                                                      |   
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: seo.php
//

  define('TABLE_SEO_CACHE', DB_PREFIX . 'seo_cache');
?><?php
// Function to reset SEO URLs database cache entries 
// Ultimate SEO URLs v2.1
function zen_reset_cache_data_seo_urls($action) {
	switch ($action){
		case 'reset':
			$GLOBALS['db']->Execute("DELETE FROM " . TABLE_SEO_CACHE . " WHERE cache_name LIKE '%seo_urls%'");
			$GLOBALS['db']->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='false' WHERE configuration_key='SEO_URLS_CACHE_RESET'");
			break;
		default:
			break;
	}
	# The return value is used to set the value upon viewing
	# It's NOT returining a false to indicate failure!!
	return 'false';
}
?><?php function reset_seo_cache() { ZMRuntime::getDatabase()->update("DELETE FROM ".TABLE_SEO_CACHE." WHERE cache_name LIKE '%seo_urls%'"); } ?>