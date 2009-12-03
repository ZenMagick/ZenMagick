<?php
/*
	+----------------------------------------------------------------------+
	|	Ultimate SEO URLs For Zen Cart, version 2.100                        |
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

	class SEO_URL_INSTALLER{	
		var $default_config;
		var $db;
		var $attributes;

		function SEO_URL_INSTALLER() {
			$this->attributes = array();
		
			$x = 0;
			$this->default_config = array();

			$this->default_config['SEO_ENABLED'] = array(
				'DEFAULT' => 'true',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Enable SEO URLs?', 'SEO_ENABLED', 'true', 'Enable the SEO URLs?  This is a global setting and will turn them off completely.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),')"
			);
			$x++;

			$this->default_config['SEO_ADD_CPATH_TO_PRODUCT_URLS'] = array(
				'DEFAULT' => 'false',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Add cPath to product URLs?', 'SEO_ADD_CPATH_TO_PRODUCT_URLS', 'false', 'This setting will append the cPath to the end of product URLs (i.e. - some-product-p-1.html?cPath=xx).', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),')"
			);
			$x++;

			$this->default_config['SEO_ADD_CAT_PARENT'] = array(
				'DEFAULT' => 'true',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Add category parent to begining of URLs?', 'SEO_ADD_CAT_PARENT', 'true', 'This setting will add the category parent name to the beginning of the category URLs (i.e. - parent-category-c-1.html).', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),')"
			);
			$x++;

			$this->default_config['SEO_URLS_FILTER_SHORT_WORDS'] = array(
				'DEFAULT' => '0',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Filter Short Words', 'SEO_URLS_FILTER_SHORT_WORDS', '0', 'This setting will filter words less than or equal to the value from the URL.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, NULL)"
			);
			$x++;

			$this->default_config['SEO_URLS_USE_W3C_VALID'] = array(
				'DEFAULT' => 'true',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Output W3C valid URLs (parameter string)?', 'SEO_URLS_USE_W3C_VALID', 'true', 'This setting will output W3C valid URLs.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),')"
			);
			$x++;

			$this->default_config['USE_SEO_CACHE_GLOBAL'] = array(
				'DEFAULT' => 'true',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Enable SEO cache to save queries?', 'USE_SEO_CACHE_GLOBAL', 'true', 'This is a global setting and will turn off caching completely.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),')"
			);
			$x++;

			$this->default_config['USE_SEO_CACHE_PRODUCTS'] = array(
				'DEFAULT' => 'true',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Enable product cache?', 'USE_SEO_CACHE_PRODUCTS', 'true', 'This will turn off caching for the products.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),')"
				);
			$x++;

			$this->default_config['USE_SEO_CACHE_CATEGORIES'] = array(
				'DEFAULT' => 'true',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Enable categories cache?', 'USE_SEO_CACHE_CATEGORIES', 'true', 'This will turn off caching for the categories.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),')"
			);
			$x++;

			$this->default_config['USE_SEO_CACHE_MANUFACTURERS'] = array(
				'DEFAULT' => 'true',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Enable manufacturers cache?', 'USE_SEO_CACHE_MANUFACTURERS', 'true', 'This will turn off caching for the manufacturers.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),')"
			);
			$x++;

			$this->default_config['USE_SEO_CACHE_ARTICLES'] = array(
				'DEFAULT' => 'true',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Enable articles cache?', 'USE_SEO_CACHE_ARTICLES', 'true', 'This will turn off caching for the articles.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),')"
			);
			$x++;

			$this->default_config['USE_SEO_CACHE_INFO_PAGES'] = array(
				'DEFAULT' => 'true',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Enable information cache?', 'USE_SEO_CACHE_INFO_PAGES', 'true', 'This will turn off caching for the information pages.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),')"
			);
			$x++;

			$this->default_config['USE_SEO_REDIRECT'] = array(
				'DEFAULT' => 'true',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Enable automatic redirects?', 'USE_SEO_REDIRECT', 'true', 'This will activate the automatic redirect code and send 301 headers for old to new URLs.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),')"
			);
			$x++;

			$this->default_config['SEO_REWRITE_TYPE'] = array(
				'DEFAULT' => 'Rewrite',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Choose URL Rewrite Type', 'SEO_REWRITE_TYPE', 'Rewrite', 'Choose which SEO URL format to use.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''Rewrite''),')"
			);
			$x++;

			$this->default_config['SEO_CHAR_CONVERT_SET'] = array(
				'DEFAULT' => '',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Enter special character conversions', 'SEO_CHAR_CONVERT_SET', '', 'This setting will convert characters.<br><br>The format <b>MUST</b> be in the form: <b>char=>conv,char2=>conv2</b>', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, NULL)"
			);
			$x++;

			$this->default_config['SEO_REMOVE_ALL_SPEC_CHARS'] = array(
				'DEFAULT' => 'false',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Remove all non-alphanumeric characters?', 'SEO_REMOVE_ALL_SPEC_CHARS', 'false', 'This will remove all non-letters and non-numbers.  This should be handy to remove all special characters with 1 setting.', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''),')"
			);
			$x++;

			$this->default_config['SEO_URLS_CACHE_RESET'] = array(
				'DEFAULT' => 'false',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Reset SEO URLs Cache', 'SEO_URLS_CACHE_RESET', 'false', 'This will reset the cache data for SEO', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), 'zen_reset_cache_data_seo_urls', 'zen_cfg_select_option(array(''reset'', ''false''),')"
			);
			$x++;

			//IMAGINADW.COM
			$this->default_config['SEO_URLS_ONLY_IN'] = array(
				'DEFAULT' => 'index, category, product_info, products_new, products_all, featured_products, specials, contact_us, conditions, privacy, reviews, shippinginfo, faqs_all, site_map, gv_faq, discount_coupon, page, page_2, page_3, page_4',
				'QUERY' => "INSERT INTO `".TABLE_CONFIGURATION."` VALUES ('', 'Enter pages to allow rewrite', 'SEO_URLS_ONLY_IN', 'index, category, product_info, products_new, products_all, featured_products, specials, contact_us, conditions, privacy, reviews, shippinginfo, faqs_all, site_map, gv_faq, discount_coupon, page, page_2, page_3, page_4', 'This setting will allow the rewrite only in the specified pages. If it\'s empty all pages will be rewrited. <br><br>The format <b>MUST</b> be in the form: <b>page1,page2,page3</b>', GROUP_INSERT_ID, ".$x.", NOW(), NOW(), NULL, NULL)"
			);
			$x++;

			$this->db = &$GLOBALS['db'];

			$this->init();
		}
	
/**
 * Initializer - if there are settings not defined the default config will be used and database settings installed. 
 * @author Bobby Easland 
 * @version 1.1
 */	
	function init() {
		foreach( $this->default_config as $key => $value ){
			$container[] = defined($key) ? 'true' : 'false';
		} # end foreach
		$this->attributes['IS_DEFINED'] = in_array('false', $container) ? false : true;
		switch(true){
			case ( !$this->attributes['IS_DEFINED'] ):
				$this->eval_defaults();
				$sql = "SELECT configuration_key, configuration_value  
						FROM " . TABLE_CONFIGURATION . " 
						WHERE configuration_key LIKE '%SEO%'";
				$result = $this->db->Execute($sql);
				$num_rows = $result->RecordCount();
				$this->attributes['IS_INSTALLED'] = (sizeof($container) == $num_rows) ? true : false;
				if ( !$this->attributes['IS_INSTALLED'] ){
					$this->install_settings(); 
				}
				break;
			default:
				$this->attributes['IS_INSTALLED'] = true;
				break;
		} # end switch
	} # end function
	
/**
 * This function evaluates the default serrings into defined constants 
 * @author Bobby Easland 
 * @version 1.0
 */	
	function eval_defaults(){
		foreach( $this->default_config as $key => $value ){
			define($key, $value['DEFAULT']);
		} # end foreach
	} # end function

/**
 * This function removes the database settings (configuration and cache)
 * @author Bobby Easland 
 * @version 1.0
 */	
	function uninstall_settings(){
		$this->db->Execute("DELETE FROM `".TABLE_CONFIGURATION_GROUP."` WHERE `configuration_group_title` LIKE '%SEO%'");
		$this->db->Execute("DELETE FROM `".TABLE_CONFIGURATION."` WHERE `configuration_key` LIKE '%SEO%'");
		$this->db->Execute("DROP TABLE IF EXISTS " . TABLE_SEO_CACHE);
	} # end function
	
/**
 * This function installs the database settings
 * @author Bobby Easland 
 * @version 1.0
 */	
	function install_settings(){
		$this->uninstall_settings();
		$sort_order_query = "SELECT MAX(sort_order) as max_sort FROM `".TABLE_CONFIGURATION_GROUP."`";
		$sort = $this->db->Execute($sort_order_query);
		$next_sort = $sort->fields['max_sort'] + 1;
		$insert_group = "INSERT INTO `".TABLE_CONFIGURATION_GROUP."` VALUES ('', 'SEO URLs', 'Options for Ultimate SEO URLs by Chemo', '".$next_sort."', '1')";
        // badly fix MySQL5 issue
        $insert_group = str_replace(" (''", " (NULL", $insert_group);
		$this->db->Execute($insert_group);
		$group_id = $this->db->insert_ID();

		foreach ($this->default_config as $key => $value){
			$sql = str_replace('GROUP_INSERT_ID', $group_id, $value['QUERY']);
            // badly fix MySQL5 issue
			$sql = str_replace(" (''", " (NULL", $sql);
			$this->db->Execute($sql);
		}

		$insert_cache_table = "CREATE TABLE " . TABLE_SEO_CACHE . " (
		  `cache_id` varchar(32) NOT NULL default '',
		  `cache_language_id` tinyint(1) NOT NULL default '0',
		  `cache_name` varchar(255) NOT NULL default '',
		  `cache_data` mediumtext NOT NULL,
		  `cache_global` tinyint(1) NOT NULL default '1',
		  `cache_gzip` tinyint(1) NOT NULL default '1',
		  `cache_method` varchar(20) NOT NULL default 'RETURN',
		  `cache_date` datetime NOT NULL default '0000-00-00 00:00:00',
		  `cache_expires` datetime NOT NULL default '0000-00-00 00:00:00',
		  PRIMARY KEY  (`cache_id`,`cache_language_id`),
		  KEY `cache_id` (`cache_id`),
		  KEY `cache_language_id` (`cache_language_id`),
		  KEY `cache_global` (`cache_global`)
		) TYPE=MyISAM;";
		$this->db->Execute($insert_cache_table);
	} # end function	
} # end class
?>
