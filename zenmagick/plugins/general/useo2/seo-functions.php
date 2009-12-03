<?php
  /**
   * The following are all copyright notes from the original USEO2 files...
   */

//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005 Joshua Dechant                               |
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

/*=======================================================================*\
|| #################### //-- SCRIPT INFO --// ########################## ||
|| #	Script name: admin/includes/seo_cache_reset.php
|| #	Contribution: Ultimate SEO URLs v2.1
|| #	Version: 2.0
|| #	Date: 30 January 2005
|| # ------------------------------------------------------------------ # ||
|| #################### //-- COPYRIGHT INFO --// ######################## ||
|| #	Copyright (C) 2005 Bobby Easland								# ||
|| #	Internet moniker: Chemo											# ||	
|| #	Contact: chemo@mesoimpact.com									# ||
|| #	Commercial Site: http://gigabyte-hosting.com/					# ||
|| #	GPL Dev Server: http://mesoimpact.com/							# ||
|| #																	# ||
|| #	This script is free software; you can redistribute it and/or	# ||
|| #	modify it under the terms of the GNU General Public License		# ||
|| #	as published by the Free Software Foundation; either version 2	# ||
|| #	of the License, or (at your option) any later version.			# ||
|| #																	# ||
|| #	This script is distributed in the hope that it will be useful,	# ||
|| #	but WITHOUT ANY WARRANTY; without even the implied warranty of	# ||
|| #	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the	# ||
|| #	GNU General Public License for more details.					# ||
|| #																	# ||
|| #	Script is intended to be used with:								# ||
|| #	osCommerce, Open Source E-Commerce Solutions					# ||
|| #	http://www.oscommerce.com										# ||
|| #	Copyright (c) 2003 osCommerce									# ||
|| ###################################################################### ||
\*========================================================================*/

/*
	+----------------------------------------------------------------------+
	|	Ultimate SEO URLs For Zen Cart, version 2.101                        |
	+----------------------------------------------------------------------+
	|                                                                      |
	|	Derrived from Ultimate SEO URLs v2.1 for osCommerce by Chemo         |
	|                                                                      |
	|	Portions Copyright 2005, Joshua Dechant                              |
	|                                                                      |
	|	Portions Copyright 2005, Bobby Easland                               |
	|                                                                      |
	|	Portions Copyright 2003 The zen-cart developers                      |
	|                                                                      |
	+----------------------------------------------------------------------+
	| This source file is subject to version 2.0 of the GPL license,       |
	| that is bundled with this package in the file LICENSE, and is        |
	| available through the world-wide-web at the following url:           |
	| http://www.zen-cart.com/license/2_0.txt.                             |
	| If you did not receive a copy of the zen-cart license and are unable |
	| to obtain it through the world-wide-web, please send a note to       |
	| license@zen-cart.com so we can mail you a copy immediately.          |
	+----------------------------------------------------------------------+
*/

?>
<?php

    // SEO database table
    define('TABLE_SEO_CACHE', DB_PREFIX . 'seo_cache');

    /**
     * Reset SEO cache.
     */
    function reset_seo_cache() {
    global $db;

        $db->Execute("DELETE FROM " . TABLE_SEO_CACHE . " WHERE cache_name LIKE '%seo_urls%'");
    }


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


    /**
     * ZenMagick SEO API function.
     */
    function zm_build_seo_href($page=null, $parameters='', $isSecure=false, $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
        /* QUICK AND DIRTY WAY TO DISABLE REDIRECTS ON PAGES WHEN SEO_URLS_ONLY_IN is enabled IMAGINADW.COM */
        $sefu = explode(",", ereg_replace(' +', '', SEO_URLS_ONLY_IN));
        if ((SEO_URLS_ONLY_IN != "" && !in_array($page, $sefu)) || (null != ZMSettings::get('seoEnabledPagesList') && !ZMLangUtils::inArray($page, ZMSettings::get('seoEnabledPagesList')))) {
            return ZMRequest::instance()->getToolbox()->net->furl($page, $parameters, $isSecure ? 'SSL' : 'NONSSL', $addSessionId, false, $isStatic, $useContext);
        }
        
        if (!isset($GLOBALS['seo_urls']) && !is_object($GLOBALS['seo_urls'])) {
            //include_once(DIR_WS_CLASSES . 'seo.url.php');
            $GLOBALS['seo_urls'] = &new SEO_URL($_SESSION['languages_id']);
        }
        // no $seo parameter
        return $GLOBALS['seo_urls']->href_link($page, $parameters, $isSecure ? 'SSL' : 'NONSSL', $addSessionId, $isStatic, $useContext);
    }

?>
