<?php
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

	//require_once(dirname(__FILE__) . '/seo.install.php');

	class SEO_URL{
		var $cache;
		var $languages_id;
		var $attributes;
		var $base_url;
		var $base_url_ssl;
		var $reg_anchors;
		var $cache_query;
		var $cache_file;
		var $data;
		var $need_redirect;
		var $is_seopage;
		var $uri;
		var $real_uri;
		var $uri_parsed;
		var $db;
		var $installer;
	
		function SEO_URL($languages_id=''){
			global $session_started;
				
			$this->installer = &new SEO_URL_INSTALLER();

			$this->db = &$GLOBALS['db'];

			if ($languages_id == '') $languages_id = $_SESSION['languages_id'];
			
			$this->languages_id = (int)$languages_id; 
		
			$this->data = array(); 
		
			$seo_pages = array(
				FILENAME_DEFAULT, 
				ZM_FILENAME_CATEGORY, 
				FILENAME_PRODUCT_INFO, 
				FILENAME_POPUP_IMAGE,
				FILENAME_PRODUCT_REVIEWS,
				FILENAME_PRODUCT_REVIEWS_INFO,
			);

			// News & Article Manager SEO support
			if (defined('FILENAME_NEWS_INDEX')) $seo_pages[] = FILENAME_NEWS_INDEX;
			if (defined('FILENAME_NEWS_ARTICLE')) $seo_pages[] = FILENAME_NEWS_ARTICLE;
			if (defined('FILENAME_NEWS_COMMENTS')) $seo_pages[] = FILENAME_NEWS_COMMENTS;
			if (defined('FILENAME_NEWS_ARCHIVE')) $seo_pages[] = FILENAME_NEWS_ARCHIVE;
			if (defined('FILENAME_NEWS_RSS')) $seo_pages[] = FILENAME_NEWS_RSS;

			// Info Manager (Open Operations)
			if (defined('FILENAME_INFO_MANAGER')) $seo_pages[] = FILENAME_INFO_MANAGER;

			$this->attributes = array(
				'PHP_VERSION' => PHP_VERSION,
				'SESSION_STARTED' => $session_started,
				'SID' => (defined('SID') && $this->not_null(SID) ? SID : ''),
				'SEO_ENABLED' => defined('SEO_ENABLED') ? SEO_ENABLED : 'false',
				'SEO_ADD_CPATH_TO_PRODUCT_URLS' => defined('SEO_ADD_CPATH_TO_PRODUCT_URLS') ? SEO_ADD_CPATH_TO_PRODUCT_URLS : 'false',
				'SEO_ADD_CAT_PARENT' => defined('SEO_ADD_CAT_PARENT') ? SEO_ADD_CAT_PARENT : 'true',
				'SEO_URLS_USE_W3C_VALID' => defined('SEO_URLS_USE_W3C_VALID') ? SEO_URLS_USE_W3C_VALID : 'true',
				'USE_SEO_CACHE_GLOBAL' => defined('USE_SEO_CACHE_GLOBAL') ? USE_SEO_CACHE_GLOBAL : 'false',
				'USE_SEO_CACHE_PRODUCTS' => defined('USE_SEO_CACHE_PRODUCTS') ? USE_SEO_CACHE_PRODUCTS : 'false',
				'USE_SEO_CACHE_CATEGORIES' => defined('USE_SEO_CACHE_CATEGORIES') ? USE_SEO_CACHE_CATEGORIES : 'false',
				'USE_SEO_CACHE_MANUFACTURERS' => defined('USE_SEO_CACHE_MANUFACTURERS') ? USE_SEO_CACHE_MANUFACTURERS : 'false',
				'USE_SEO_CACHE_ARTICLES' => defined('USE_SEO_CACHE_ARTICLES') ? USE_SEO_CACHE_ARTICLES : 'false',
				'USE_SEO_CACHE_INFO_PAGES' => defined('USE_SEO_CACHE_INFO_PAGES') ? USE_SEO_CACHE_INFO_PAGES : 'false',
				'USE_SEO_REDIRECT' => defined('USE_SEO_REDIRECT') ? USE_SEO_REDIRECT : 'false',
				'SEO_REWRITE_TYPE' => defined('SEO_REWRITE_TYPE') ? SEO_REWRITE_TYPE : 'false',
				'SEO_URLS_FILTER_SHORT_WORDS' => defined('SEO_URLS_FILTER_SHORT_WORDS') ? SEO_URLS_FILTER_SHORT_WORDS : 'false',
				'SEO_CHAR_CONVERT_SET' => defined('SEO_CHAR_CONVERT_SET') ? $this->expand(SEO_CHAR_CONVERT_SET) : 'false',
				'SEO_REMOVE_ALL_SPEC_CHARS' => defined('SEO_REMOVE_ALL_SPEC_CHARS') ? SEO_REMOVE_ALL_SPEC_CHARS : 'false',
				'SEO_PAGES' => $seo_pages,
				'SEO_INSTALLER' => $this->installer->attributes
			);
		
			$this->base_url = HTTP_SERVER;
			$this->base_url_ssl = HTTPS_SERVER;		
			$this->cache = array();
		
			$this->reg_anchors = array(
				'products_id' => '-p-',
				'cPath' => '-c-',
				'manufacturers_id' => '-m-',
				'pID' => '-pi-',
				'products_id_review' => '-pr-',
				'products_id_review_info' => '-pri-',

				// News & Article Manager SEO support
				'news_article_id' => '-a-',
				'news_comments_article_id' => '-a-',
				'news_dates' => '/',
				'news_archive_dates' => '/archive/',
				'news_rss_feed' => '/rss',

				// Info Manager (Open Operations)
				'info_manager_page_id' => '-i-',
			);
		
			if ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true'){
				$this->cache_file = 'seo_urls_v2_';
				$this->cache_gc();
				if ( $this->attributes['USE_SEO_CACHE_PRODUCTS'] == 'true' ) $this->generate_products_cache();
				if ( $this->attributes['USE_SEO_CACHE_CATEGORIES'] == 'true' ) $this->generate_categories_cache();
				if ( $this->attributes['USE_SEO_CACHE_MANUFACTURERS'] == 'true' ) $this->generate_manufacturers_cache();
				if ( $this->attributes['USE_SEO_CACHE_ARTICLES'] == 'true' && defined('TABLE_NEWS_ARTICLES_TEXT')) $this->generate_news_articles_cache();
				if ( $this->attributes['USE_SEO_CACHE_INFO_PAGES'] == 'true' && defined('TABLE_INFO_MANAGER')) $this->generate_info_manager_cache();
			}

			if ($this->attributes['USE_SEO_REDIRECT'] == 'true'){
				$this->check_redirect();
			} # end if
		} # end constructor

		function href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $static = false, $use_dir_ws_catalog = true) {
			// don't rewrite when disabled
			// don't rewrite images, css, js, xml, real html files, etc
			if ( ($this->attributes['SEO_ENABLED'] == 'false') || (preg_match('/(.+)\.(html?|xml|css|js|png|jpe?g|gif|bmp|tiff?|ico|gz|zip|rar)$/i', $page)) ) {
				return $this->stock_href_link($page, $parameters, $connection, $add_session_id, true, $static, $use_dir_ws_catalog);
			}
      // don't rewrite the paypal IPN notify url
      if ($page == 'ipn_main_handler.php') {
       return $this->stock_href_link($page, $parameters, $connection, $add_session_id, true, $static, $use_dir_ws_catalog);
      }

			if ((!in_array($page, $this->attributes['SEO_PAGES'])) || (($page == FILENAME_DEFAULT) && (!preg_match('/(cpath|manufacturers_id)/i', $parameters)))) {
				if ($page == FILENAME_DEFAULT) {
					$page = '';
				} else {
					$page = $page . '.html';
				}
			}

			if ($connection == 'NONSSL') {
				$link = $this->base_url;
			} elseif ($connection == 'SSL') {
				if (ENABLE_SSL == 'true') {
					$link = $this->base_url_ssl ;
				} else {
					$link = $this->base_url;
				}
			}

      if ($use_dir_ws_catalog) {
        if ($connection == 'SSL' && ENABLE_SSL == 'true') {
          $link .= DIR_WS_HTTPS_CATALOG;
        } else {
          $link .= DIR_WS_CATALOG;
        }
      }

			if (strstr($page, '?')) {
				$separator = '&';
			} else {
				$separator = '?';
			}

			if ($this->not_null($parameters)) { 
				$link .= $this->parse_parameters($page, $parameters, $separator);	
			} else {
				// support SEO pages with no parameters
				switch ($page) {
					case FILENAME_NEWS_RSS:
						$link .= $this->make_url($page, FILENAME_NEWS_INDEX, 'news_rss_feed', '', '.xml', $separator);
						break;
					case FILENAME_NEWS_ARCHIVE:
						$link .= $this->make_url($page, FILENAME_NEWS_INDEX, 'news_archive_dates', '', '', $separator);
						break;
					case FILENAME_NEWS_INDEX:
						$link .= $this->make_url($page, FILENAME_NEWS_INDEX, 'news_dates', '', '', $separator);
						break;

					default:
						$link .= $page;
						break;
				}
			}

			$link = $this->add_sid($link, $add_session_id, $connection, $separator);

			switch($this->attributes['SEO_URLS_USE_W3C_VALID']){
				case 'true':
					if (!isset($_SESSION['customer_id']) && defined('ENABLE_PAGE_CACHE') && ENABLE_PAGE_CACHE == 'true' && class_exists('page_cache')){
						return $link;
					} else {
						return htmlspecialchars(utf8_encode($link));
					}
					break;
				case 'false':
				default:
					return $link;
					break;
			}
		} # end function

		function stock_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $static = false, $use_dir_ws_catalog = true) {
			global $request_type, $session_started, $http_domain, $https_domain;

			if (!$this->not_null($page)) {
				die('</td></tr></table></td></tr></table><br /><br /><strong class="note">Error!<br /><br />Unable to determine the page link!</strong><br /><br />');
			}

			if ($connection == 'NONSSL') {
				$link = HTTP_SERVER;
			} elseif ($connection == 'SSL') {
				if (ENABLE_SSL == 'true') {
					$link = HTTPS_SERVER ;
				} else {
					$link = HTTP_SERVER;
				}
			} else {
				die('</td></tr></table></td></tr></table><br /><br /><strong class="note">Error!<br /><br />Unable to determine connection method on a link!<br /><br />Known methods: NONSSL SSL</strong><br /><br />');
			}

    if ($use_dir_ws_catalog) {
      if ($connection == 'SSL') {
        $link .= DIR_WS_HTTPS_CATALOG;
      } else {
        $link .= DIR_WS_CATALOG;
      }
    }

			if (!$static) {
				if ($this->not_null($parameters)) {
					$link .= 'index.php?main_page='. $page . "&" . $this->output_string($parameters);
				} else {
					$link .= 'index.php?main_page=' . $page;
				}
			} else {
				if ($this->not_null($parameters)) {
					$link .= $page . "?" . $this->output_string($parameters);
				} else {
					$link .= $page;
				}
			}

			$separator = '&';

			while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

			if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
				if ($this->not_null($this->attributes['SID'])) {
					$_sid = $this->attributes['SID'];
				} elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
					if ($http_domain != $https_domain) {
						$_sid = session_name() . '=' . session_id();
					}
				}
			}

			// clean up the link before processing
			while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);
			while (strstr($link, '&amp;&amp;')) $link = str_replace('&amp;&amp;', '&amp;', $link);

			switch(true){
				case (!isset($_SESSION['customer_id']) && defined('ENABLE_PAGE_CACHE') && ENABLE_PAGE_CACHE == 'true' && class_exists('page_cache')):
					$page_cache = true;
					$return = $link . $separator . '<zensid>';
					break;
				case (isset($_sid)):
					$page_cache = false;
					$return = $link . $separator . $_sid;
					break;
				default:
					$page_cache = false;
					$return = $link;
					break;
			}

			$this->cache['STANDARD_URLS'][] = $link;

			switch(true){
				case ($this->attributes['SEO_URLS_USE_W3C_VALID'] == 'true' && !$page_cache):
					return htmlspecialchars(utf8_encode($return));
					break;
				default:
					return $return;
					break;
			}# end swtich
		} # end default tep_href function

		function add_sid($link, $add_session_id, $connection, $separator) {
			global $request_type, $http_domain, $https_domain;

			if ( ($add_session_id == true) && ($this->attributes['SESSION_STARTED']) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
				if ($this->not_null($this->attributes['SID'])) {
					$_sid = $this->attributes['SID'];
				} elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
					if ($http_domain != $https_domain) {
						$_sid = session_name() . '=' . session_id();
					}
				}
			}

			switch(true){
				case (!isset($_SESSION['customer_id']) && defined('ENABLE_PAGE_CACHE') && ENABLE_PAGE_CACHE == 'true' && class_exists('page_cache')):
					$return = $link . $separator . '<zensid>';
					break;
				case ($this->not_null($_sid)):
					$return = $link . $separator . $_sid;
					break;
				default:
					$return = $link;
					break;
			} # end switch
			return $return;
		} # end function
	
/**
 * Function to parse the parameters into an SEO URL 
 * @author Bobby Easland 
 * @version 1.2
 * @param string $page
 * @param string $params
 * @param string $separator NOTE: passed by reference
 * @return string 
 */	
	function parse_parameters($page, $params, &$separator) {
		$p = @explode('&', $params);
		krsort($p);
		$container = array();
		foreach ($p as $index => $valuepair){
			$p2 = @explode('=', $valuepair); 
			switch ($p2[0]){ 

				case 'article_id':
					switch(true) {
						case ($page == FILENAME_NEWS_ARTICLE):
							$url = $this->make_url($page, 'news/' . $this->get_news_article_name($p2[1]), 'news_article_id', $p2[1], '.html', $separator);
							break;
						case ($page == FILENAME_NEWS_COMMENTS):
							$url = $this->make_url($page, 'news/' . $this->get_news_article_name($p2[1]), 'news_comments_article_id', $p2[1], '-comments.html', $separator);
							break;
						default:
							$container[$p2[0]] = $p2[1];
							break;
					}
					break;

				case 'date':
					switch(true) {
						case ($page == FILENAME_NEWS_ARCHIVE):
							$url = $this->make_url($page, FILENAME_NEWS_INDEX, 'news_archive_dates', $p2[1], '.html', $separator);
							break;
						case ($page == FILENAME_NEWS_INDEX):
							$url = $this->make_url($page, FILENAME_NEWS_INDEX, 'news_dates', $p2[1], '.html', $separator);
							break;
						default:
							$container[$p2[0]] = $p2[1];
							break;
					}
					break;

				case 'pages_id':
					switch(true) {
						case ($page == FILENAME_INFO_MANAGER):
							$url = $this->make_url($page, $this->get_info_manager_page_name($p2[1]), 'info_manager_page_id', $p2[1], '.html', $separator);
							break;
						default:
							$container[$p2[0]] = $p2[1];
							break;
					}
					break;

				case 'products_id':
					switch(true) {
//						case ($page == FILENAME_PRODUCT_INFO && !$this->is_attribute_string($params)):
						case ($page == FILENAME_PRODUCT_INFO):
							$url = $this->make_url($page, $this->get_product_name($p2[1]), $p2[0], $p2[1], '.html', $separator);
							break;
						case ($page == FILENAME_PRODUCT_REVIEWS):
							$url = $this->make_url($page, $this->get_product_name($p2[1]), 'products_id_review', $p2[1], '.html', $separator);
							break;
						case ($page == FILENAME_PRODUCT_REVIEWS_INFO):
							$url = $this->make_url($page, $this->get_product_name($p2[1]), 'products_id_review_info', $p2[1], '.html', $separator);
							break;
						default:
							$container[$p2[0]] = $p2[1];
							break;
					} # end switch
					break;
				case 'cPath':
					switch(true){
						case ($page == ZM_FILENAME_CATEGORY || $page == FILENAME_DEFAULT):
							$url = $this->make_url($page, $this->get_category_name($p2[1]), $p2[0], $p2[1], '.html', $separator);
							break;
						case ($this->is_product_string($params)):
							if ($this->attributes['SEO_ADD_CPATH_TO_PRODUCT_URLS'] == 'true') {
								$container[$p2[0]] = $p2[1];
							}
							break;
						default:
							$container[$p2[0]] = $p2[1];
							break;
						} # end switch
					break;
				case 'manufacturers_id':
					switch(true){
						case (($page == ZM_FILENAME_CATEGORY  || $page == FILENAME_DEFAULT) && !$this->is_cPath_string($params) && !$this->is_product_string($params)):
							$url = $this->make_url($page, $this->get_manufacturer_name($p2[1]), $p2[0], $p2[1], '.html', $separator);
							break;
						case ($page == FILENAME_PRODUCT_INFO):
							break;
						default:
							$container[$p2[0]] = $p2[1];
							break;					
						} # end switch
					break;
				case 'pID':
					switch(true){
						case ($page == FILENAME_POPUP_IMAGE):
						$url = $this->make_url($page, $this->get_product_name($p2[1]), $p2[0], $p2[1], '.html', $separator);
						break;
					default:
						$container[$p2[0]] = $p2[1];
						break;
					} # end switch
					break;
				default:
					$container[$p2[0]] = $p2[1]; 
					break;
			} # end switch
		} # end foreach $p
		$url = isset($url) ? $url : $page;
		if ( sizeof($container) > 0 ){
			if ( $imploded_params = $this->implode_assoc($container) ){
				$url .= $separator . $this->output_string( $imploded_params );
				$separator = '&';
			}
		}
		return $url;
	} # end function

/**
 * Function to return the generated SEO URL	 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $page
 * @param string $string Stripped, formed anchor
 * @param string $anchor_type Parameter type (products_id, cPath, etc.)
 * @param integer $id
 * @param string $extension Default = .html
 * @param string $separator NOTE: passed by reference
 * @return string
 */	
	function make_url($page, $string, $anchor_type, $id, $extension = '.html', &$separator){
		// Right now there is but one rewrite method since cName was dropped
		// In the future there will be additional methods here in the switch
		switch ( $this->attributes['SEO_REWRITE_TYPE'] ){
			case 'Rewrite':
				return $string . $this->reg_anchors[$anchor_type] . $id . $extension;
				break;
			default:
				break;
		} # end switch
	} # end function

	function get_info_manager_page_name($pages_id) {
		switch(true){
			case ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true' && defined('INFO_MANAGER_PAGE_NAME_' . $pages_id)):
				$return = constant('INFO_MANAGER_PAGE_NAME_' . $pages_id);
				$this->cache['INFO_MANAGER_PAGES'][$pages_id] = $return;
				break;

			case ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true' && isset($this->cache['INFO_MANAGER_PAGES'][$pages_id])):
				$return = $this->cache['INFO_MANAGER_PAGES'][$pages_id];
				break;

			default:
				$sql = "SELECT pages_title  
						FROM " . TABLE_INFO_MANAGER . " 
						WHERE pages_id = " . (int)$pages_id . " 
						LIMIT 1";
				$result = $this->db->Execute($sql);
				$pages_title = $this->strip($result->fields['pages_title']);
				$this->cache['INFO_MANAGER_PAGES'][$pages_id] = $pages_title;
				$return = $pages_title;
				break;	
		}		
		return $return;
	}

	function get_news_article_name($article_id) {
		switch(true){
			case ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true' && defined('NEWS_ARTICLE_NAME_' . $article_id)):
				$return = constant('NEWS_ARTICLE_NAME_' . $article_id);
				$this->cache['NEWS_ARTICLES'][$article_id] = $return;
				break;

			case ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true' && isset($this->cache['NEWS_ARTICLES'][$article_id])):
				$return = $this->cache['NEWS_ARTICLES'][$article_id];
				break;

			default:
				$sql = "SELECT news_article_name  
						FROM " . TABLE_NEWS_ARTICLES_TEXT . " 
						WHERE article_id = " . (int)$article_id . " 
						AND language_id = " . (int)$this->languages_id . "  
						LIMIT 1";
				$result = $this->db->Execute($sql);
				$news_article_name = $this->strip($result->fields['news_article_name']);
				$this->cache['NEWS_ARTICLES'][$article_id] = $news_article_name;
				$return = $news_article_name;
				break;	
		}		
		return $return;
	}

/**
 * Function to get the product name. Use evaluated cache, per page cache, or database query in that order of precedent	
 * @author Bobby Easland 
 * @version 1.1
 * @param integer $pID
 * @return string Stripped anchor text
 */	
	function get_product_name($pID){
		switch(true){
			case ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true' && defined('PRODUCT_NAME_' . $pID)):
				$return = constant('PRODUCT_NAME_' . $pID);
				$this->cache['PRODUCTS'][$pID] = $return;
				break;

			case ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true' && isset($this->cache['PRODUCTS'][$pID])):
				$return = $this->cache['PRODUCTS'][$pID];
				break;

			default:
				$sql = "SELECT products_name as pName 
						FROM " . TABLE_PRODUCTS_DESCRIPTION . " 
						WHERE products_id = " . (int)$pID . " 
						AND language_id = " . (int)$this->languages_id . "  
						LIMIT 1";
				$result = $this->db->Execute($sql);
				$pName = $this->strip($result->fields['pName']);
				$this->cache['PRODUCTS'][$pID] = $pName;
				$return = $pName;
				break;								
		} # end switch		
		return $return;
	} # end function
	
/**
 * Function to get the category name. Use evaluated cache, per page cache, or database query in that order of precedent 
 * @author Bobby Easland 
 * @version 1.1
 * @param integer $cID NOTE: passed by reference
 * @return string Stripped anchor text
 */	
	function get_category_name(&$cID){
		$full_cPath = $this->get_full_cPath($cID, $single_cID); // full cPath needed for uniformity
		switch(true){
			case ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true' && defined('CATEGORY_NAME_' . $full_cPath)):
				$return = constant('CATEGORY_NAME_' . $full_cPath);
				$this->cache['CATEGORIES'][$full_cPath] = $return;
				break;
			case ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true' && isset($this->cache['CATEGORIES'][$full_cPath])):
				$return = $this->cache['CATEGORIES'][$full_cPath];
				break;
			default:
				switch(true){
					case ($this->attributes['SEO_ADD_CAT_PARENT'] == 'true'):
						$sql = "SELECT c.categories_id, c.parent_id, cd.categories_name as cName, cd2.categories_name as pName  
								FROM ".TABLE_CATEGORIES_DESCRIPTION." cd, ".TABLE_CATEGORIES." c 
								LEFT JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd2 
								ON c.parent_id=cd2.categories_id AND cd2.language_id='".(int)$this->languages_id."' 
								WHERE c.categories_id='".(int)$single_cID."' 
								AND cd.categories_id='".(int)$single_cID."' 
								AND cd.language_id='".(int)$this->languages_id."' 
								LIMIT 1";
						$result = $this->db->Execute($sql);
						$cName = $this->not_null($result->fields['pName']) ? $result->fields['pName'] . ' ' . $result->fields['cName'] : $result->fields['cName'];
						break;
					default:
						$sql = "SELECT categories_name as cName 
								FROM ".TABLE_CATEGORIES_DESCRIPTION." 
								WHERE categories_id='".(int)$single_cID."' 
								AND language_id='".(int)$this->languages_id."' 
								LIMIT 1";
						$result = $this->db->Execute($sql);
						$cName = $result->fields['cName'];
						break;
				}										
				$cName = $this->strip($cName);
				$this->cache['CATEGORIES'][$full_cPath] = $cName;
				$return = $cName;
				break;								
		} # end switch		
		$cID = $full_cPath;
		return $return;
	} # end function

/**
 * Function to get the manufacturer name. Use evaluated cache, per page cache, or database query in that order of precedent.
 * @author Bobby Easland 
 * @version 1.1
 * @param integer $mID
 * @return string
 */	
	function get_manufacturer_name($mID){
		switch(true){
			case ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true' && defined('MANUFACTURER_NAME_' . $mID)):
				$return = constant('MANUFACTURER_NAME_' . $mID);
				$this->cache['MANUFACTURERS'][$mID] = $return;
				break;
			case ($this->attributes['USE_SEO_CACHE_GLOBAL'] == 'true' && isset($this->cache['MANUFACTURERS'][$mID])):
				$return = $this->cache['MANUFACTURERS'][$mID];
				break;
			default:
				$sql = "SELECT manufacturers_name as mName 
						FROM ".TABLE_MANUFACTURERS." 
						WHERE manufacturers_id='".(int)$mID."' 
						LIMIT 1";
				$result = $this->db->Execute($sql);
				$mName = $this->strip($result->fields['mName']);
				$this->cache['MANUFACTURERS'][$mID] = $mName;
				$return = $mName;
				break;								
		} # end switch		
		return $return;
	} # end function

/**
 * Function to retrieve full cPath from category ID 
 * @author Bobby Easland 
 * @version 1.1
 * @param mixed $cID Could contain cPath or single category_id
 * @param integer $original Single category_id passed back by reference
 * @return string Full cPath string
 */	
	function get_full_cPath($cID, &$original){
		if ( is_numeric(strpos($cID, '_')) ){
			$temp = @explode('_', $cID);
			$original = $temp[sizeof($temp)-1];
			return $cID;
		} else {
			$c = array();
			$this->GetParentCategories($c, $cID);
			$c = array_reverse($c);
			$c[] = $cID;
			$original = $cID;
			$cID = sizeof($c) > 1 ? implode('_', $c) : $cID;
			return $cID;
		}
	} # end function

/**
 * Recursion function to retrieve parent categories from category ID 
 * @author Bobby Easland 
 * @version 1.0
 * @param mixed $categories Passed by reference
 * @param integer $categories_id
 */	
	function GetParentCategories(&$categories, $categories_id) {
		$sql = "SELECT parent_id FROM " . TABLE_CATEGORIES . " WHERE categories_id = " . (int)$categories_id;

		$parent_categories = $this->db->Execute($sql);

		while (!$parent_categories->EOF) {
			if ($parent_categories->fields['parent_id'] == 0) return true;

			$categories[sizeof($categories)] = $parent_categories->fields['parent_id'];

			if ($parent_categories->fields['parent_id'] != $categories_id) {
				$this->GetParentCategories($categories, $parent_categories->fields['parent_id']);
			}

			$parent_categories->MoveNext();
		}
	}

	function not_null($value) {
		return zen_not_null($value);
	}

	function is_attribute_string($params){
		if (preg_match('/products_id=([0-9]+):([a-zA-z0-9]{32})/', $params)) {
			return true;
		}

		return false;
	}

	function is_product_string($params) {
		if (preg_match('/products_id=/i', $params)) {
			return true;
		}

		return false;
	}

	function is_cPath_string($params) {
		if (preg_match('/cPath=/i', $params)) {
			return true;
		}

		return false;
	}
	
/**
 * Function to strip the string of punctuation and white space 
 * @author Bobby Easland 
 * @version 1.1
 * @param string $string
 * @return string Stripped text. Removes all non-alphanumeric characters.
 */	
	function strip($string){
		if ( is_array($this->attributes['SEO_CHAR_CONVERT_SET']) ) $string = strtr($string, $this->attributes['SEO_CHAR_CONVERT_SET']);
		$pattern = $this->attributes['SEO_REMOVE_ALL_SPEC_CHARS'] == 'true'
						?	"([^[:alnum:]])+"
						:	"([[:punct:]])+";
		$anchor = ereg_replace($pattern, '', strtolower($string));
		$pattern = "([[:space:]]|[[:blank:]])+"; 
		$anchor = ereg_replace($pattern, '-', $anchor);
		return $this->short_name($anchor); // return the short filtered name 
	} # end function

/**
 * Function to expand the SEO_CONVERT_SET group 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $set
 * @return mixed
 */	
	function expand($set){
		if ( $this->not_null($set) ){
			if ( $data = @explode(',', $set) ){
				foreach ( $data as $index => $valuepair){
					$p = @explode('=>', $valuepair);
					$container[trim($p[0])] = trim($p[1]);
				}
				return $container;
			} else {
				return 'false';
			}
		} else {
			return 'false';
		}
	} # end function
/**
 * Function to return the short word filtered string 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $str
 * @param integer $limit
 * @return string Short word filtered
 */	
	function short_name($str, $limit=3){
		if ( $this->attributes['SEO_URLS_FILTER_SHORT_WORDS'] != 'false' ) $limit = (int)$this->attributes['SEO_URLS_FILTER_SHORT_WORDS'];
		$foo = @explode('-', $str);
		foreach($foo as $index => $value){
			switch (true){
				case ( strlen($value) <= $limit ):
					continue;
				default:
					$container[] = $value;
					break;
			}		
		} # end foreach
		$container = ( sizeof($container) > 1 ? implode('-', $container) : $str );
		return $container;
	}
	
/**
 * Function to implode an associative array 
 * @author Bobby Easland 
 * @version 1.0
 * @param array $array Associative data array
 * @param string $inner_glue
 * @param string $outer_glue
 * @return string
 */	
	function implode_assoc($array, $inner_glue='=', $outer_glue='&') {
		$output = array();
		foreach( $array as $key => $item ){
			if ( $this->not_null($key) && $this->not_null($item) ){
				$output[] = $key . $inner_glue . $item;
			}
		} # end foreach	
		return @implode($outer_glue, $output);
	}

/**
 * Function to translate a string 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $data String to be translated
 * @param array $parse Array of tarnslation variables
 * @return string
 */	
	function parse_input_field_data($data, $parse) {
		return strtr(trim($data), $parse);
	}
	
/**
 * Function to output a translated or sanitized string 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $sting String to be output
 * @param mixed $translate Array of translation characters
 * @param boolean $protected Switch for htemlspecialchars processing
 * @return string
 */	
	function output_string($string, $translate = false, $protected = false) {
		if ($protected == true) {
		  return htmlspecialchars($string);
		} else {
		  if ($translate == false) {
			return $this->parse_input_field_data($string, array('"' => '&quot;'));
		  } else {
			return $this->parse_input_field_data($string, $translate);
		  }
		}
	}

/**
 * Function to generate products cache entries 
 * @author Bobby Easland 
 * @version 1.0
 */	
	function generate_products_cache(){
		$this->is_cached($this->cache_file . 'products', $is_cached, $is_expired);  	
		if ( !$is_cached || $is_expired ) {
		$sql = "SELECT p.products_id as id, pd.products_name as name 
		        FROM ".TABLE_PRODUCTS." p 
				LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd 
				ON p.products_id=pd.products_id 
				AND pd.language_id='".(int)$this->languages_id."' 
				WHERE p.products_status='1'";
		$product = $this->db->Execute($sql);
		$prod_cache = '';
		while (!$product->EOF) {
			$define = 'define(\'PRODUCT_NAME_' . $product->fields['id'] . '\', \'' . $this->strip($product->fields['name']) . '\');';
			$prod_cache .= $define . "\n";
			eval("$define");
			$product->MoveNext();
		}
		$this->save_cache($this->cache_file . 'products', $prod_cache, 'EVAL', 1 , 1);
		unset($prod_cache);
		} else {
			$this->get_cache($this->cache_file . 'products');		
		}
	} # end function
		
/**
 * Function to generate manufacturers cache entries 
 * @author Bobby Easland 
 * @version 1.0
 */	
	function generate_manufacturers_cache(){
		$this->is_cached($this->cache_file . 'manufacturers', $is_cached, $is_expired);  	
		if ( !$is_cached || $is_expired ) { // it's not cached so create it
		$sql = "SELECT m.manufacturers_id as id, m.manufacturers_name as name 
		        FROM ".TABLE_MANUFACTURERS." m 
				LEFT JOIN ".TABLE_MANUFACTURERS_INFO." md 
				ON m.manufacturers_id=md.manufacturers_id 
				AND md.languages_id='".(int)$this->languages_id."'";
		$manufacturers = $this->db->Execute($sql);
		$man_cache = '';
		while (!$manufacturers->EOF) {
			$define = 'define(\'MANUFACTURER_NAME_' . $manufacturer->fields['id'] . '\', \'' . $this->strip($manufacturer->fields['name']) . '\');';
			$man_cache .= $define . "\n";
			eval("$define");
			$manufacturers->MoveNext();
		}
		$this->save_cache($this->cache_file . 'manufacturers', $man_cache, 'EVAL', 1 , 1);
		unset($man_cache);
		} else {
			$this->get_cache($this->cache_file . 'manufacturers');		
		}
	} # end function

/**
 * Function to generate categories cache entries 
 * @author Bobby Easland 
 * @version 1.1
 */	
	function generate_categories_cache(){
		$this->is_cached($this->cache_file . 'categories', $is_cached, $is_expired);  	
		if ( !$is_cached || $is_expired ) { // it's not cached so create it
			switch(true){
				case ($this->attributes['SEO_ADD_CAT_PARENT'] == 'true'):
					$sql = "SELECT c.categories_id as id, c.parent_id, cd.categories_name as cName, cd2.categories_name as pName  
							FROM ".TABLE_CATEGORIES." c 
							LEFT JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd2 
							ON c.parent_id=cd2.categories_id AND cd2.language_id='".(int)$this->languages_id."', 
							".TABLE_CATEGORIES_DESCRIPTION." cd 
							WHERE c.categories_id=cd.categories_id 
							AND cd.language_id='".(int)$this->languages_id."'";
					//IMAGINADW.COM;
					break;
				default:
					$sql = "SELECT categories_id as id, categories_name as cName 
							FROM ".TABLE_CATEGORIES_DESCRIPTION."  
							WHERE language_id='".(int)$this->languages_id."'";
					break;
			} # end switch
		$category = $this->db->Execute($sql);
		$cat_cache = '';
		while (!$category->EOF) {	
			$id = $this->get_full_cPath($category->fields['id'], $single_cID);
			$name = $this->not_null($category->fields['pName']) ? $category->fields['pName'] . ' ' . $category->fields['cName'] : $category->fields['cName']; 
			$define = 'define(\'CATEGORY_NAME_' . $id . '\', \'' . $this->strip($name) . '\');';
			$cat_cache .= $define . "\n";
			eval("$define");
			$category->MoveNext();
		}
		$this->save_cache($this->cache_file . 'categories', $cat_cache, 'EVAL', 1 , 1);
		unset($cat_cache);
		} else {
			$this->get_cache($this->cache_file . 'categories');		
		}
	} # end function

/**
 * Function to generate articles cache entries 
 * @author Bobby Easland 
 * @version 1.0
 */	
	function generate_news_articles_cache(){
		$this->is_cached($this->cache_file . 'news_articles', $is_cached, $is_expired);  	
		if ( !$is_cached || $is_expired ) { // it's not cached so create it
			$sql = "SELECT article_id as id, news_article_name as name 
					FROM ".TABLE_NEWS_ARTICLES_TEXT." 
					WHERE language_id = '".(int)$this->languages_id."'";
			$article = $this->db->Execute($sql);
			$article_cache = '';
			while (!$article->EOF) {
				$define = 'define(\'NEWS_ARTICLE_NAME_' . $article->fields['id'] . '\', \'' . $this->strip($article->fields['name']) . '\');';
				$article_cache .= $define . "\n";
				eval("$define");
				$article->MoveNext();
			}
			$this->save_cache($this->cache_file . 'news_articles', $article_cache, 'EVAL', 1 , 1);
			unset($article_cache);
		} else {
			$this->get_cache($this->cache_file . 'news_articles');		
		}
	} # end function

/**
 * Function to generate information cache entries 
 * @author Bobby Easland 
 * @version 1.0
 */	
	function generate_info_manager_cache(){
		$this->is_cached($this->cache_file . 'info_manager', $is_cached, $is_expired);  	
		if ( !$is_cached || $is_expired ) { // it's not cached so create it
			$sql = "SELECT pages_id as id, pages_title as name 
					FROM ".TABLE_INFO_MANAGER;
			$information = $this->db->Execute($sql);
			$information_cache = '';
			while (!$information->EOF) {
				$define = 'define(\'INFO_MANAGER_PAGE_NAME_' . $information->fields['id'] . '\', \'' . $this->strip($information->fields['name']) . '\');';
				$information_cache .= $define . "\n";
				eval("$define");
				$information->MoveNext();
			}
			$this->save_cache($this->cache_file . 'info_manager', $information_cache, 'EVAL', 1 , 1);
			unset($information_cache);
		} else {
			$this->get_cache($this->cache_file . 'info_manager');		
		}
	} # end function

/**
 * Function to save the cache to database 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $name Cache name
 * @param mixed $value Can be array, string, PHP code, or just about anything
 * @param string $method RETURN, ARRAY, EVAL
 * @param integer $gzip Enables compression
 * @param integer $global Sets whether cache record is global is scope
 * @param string $expires Sets the expiration
 */	
	function save_cache($name, $value, $method='RETURN', $gzip=1, $global=0, $expires = '30 days'){
		$expires = date('Y-m-d H:i:s', strtotime('+' . $expires));

		if ($method == 'ARRAY') $value = serialize($value);
		$value = ( $gzip === 1 ? base64_encode(gzdeflate($value, 1)) : addslashes($value) );
		$sql_data_array = array(
			'cache_id' => md5($name),
			'cache_language_id' => (int)$this->languages_id,
			'cache_name' => $name,
			'cache_data' => $value,
			'cache_global' => (int)$global,
			'cache_gzip' => (int)$gzip,
			'cache_method' => $method,
			'cache_date' => date("Y-m-d H:i:s"),
			'cache_expires' => $expires
		);				
		$this->is_cached($name, $is_cached, $is_expired);
		$cache_check = ( $is_cached ? 'true' : 'false' );
		switch ( $cache_check ) {
			case 'true': 
				zen_db_perform(TABLE_SEO_CACHE, $sql_data_array, 'update', "cache_id='".md5($name)."'");
				break;				
			case 'false':
				zen_db_perform(TABLE_SEO_CACHE, $sql_data_array, 'insert');
				break;				
			default:
				break;
		} # end switch ($cache check)		
		# unset the variables...clean as we go
		unset($value, $expires, $sql_data_array);		
	}# end function save_cache()
	
/**
 * Function to get cache entry 
 * @author Bobby Easland 
 * @version 1.0
 * @param string $name
 * @param boolean $local_memory
 * @return mixed
 */	
	function get_cache($name = 'GLOBAL', $local_memory = false){
		$select_list = 'cache_id, cache_language_id, cache_name, cache_data, cache_global, cache_gzip, cache_method, cache_date, cache_expires';
		$global = ( $name == 'GLOBAL' ? true : false ); // was GLOBAL passed or is using the default?
		switch($name){
			case 'GLOBAL': 
				$cache = $this->db->Execute("SELECT ".$select_list." FROM " . TABLE_SEO_CACHE . " WHERE cache_language_id='".(int)$this->languages_id."' AND cache_global='1'");
				break;
			default: 
				$cache = $this->db->Execute("SELECT ".$select_list." FROM " . TABLE_SEO_CACHE . " WHERE cache_id='".md5($name)."' AND cache_language_id='".(int)$this->languages_id."'");
				break;
		}
		$num_rows = $cache->RecordCount();
		if ($num_rows){ 
			$container = array();
			while(!$cache->EOF){
				$cache_name = $cache->fields['cache_name']; 
				if ( $cache->fields['cache_expires'] > date("Y-m-d H:i:s") ) { 
					$cache_data = ( $cache->fields['cache_gzip'] == 1 ? gzinflate(base64_decode($cache->fields['cache_data'])) : stripslashes($cache->fields['cache_data']) );
					switch($cache->fields['cache_method']){
						case 'EVAL': // must be PHP code
							eval("$cache_data");
							break;							
						case 'ARRAY': 
							$cache_data = unserialize($cache_data);							
						case 'RETURN': 
						default:
							break;
					} # end switch ($cache['cache_method'])					
					if ($global) $container['GLOBAL'][$cache_name] = $cache_data; 
					else $container[$cache_name] = $cache_data; // not global				
				} else { // cache is expired
					if ($global) $container['GLOBAL'][$cache_name] = false; 
					else $container[$cache_name] = false; 
				}# end if ( $cache['cache_expires'] > date("Y-m-d H:i:s") )			
				if ( $this->keep_in_memory || $local_memory ) {
					if ($global) $this->data['GLOBAL'][$cache_name] = $container['GLOBAL'][$cache_name]; 
					else $this->data[$cache_name] = $container[$cache_name]; 
				}
				$cache->MoveNext();					
			} # end while ($cache = $this->DB->FetchArray($this->cache_query))			
			unset($cache_data);
			switch (true) {
				case ($num_rows == 1): 
					if ($global){
						if ($container['GLOBAL'][$cache_name] == false || !isset($container['GLOBAL'][$cache_name])) return false;
						else return $container['GLOBAL'][$cache_name]; 
					} else { // not global
						if ($container[$cache_name] == false || !isset($container[$cache_name])) return false;
						else return $container[$cache_name];
					} # end if ($global)					
				case ($num_rows > 1): 
				default: 
					return $container; 
					break;
			}# end switch (true)			
		} else { 
			return false;
		}# end if ( $num_rows )		
	} # end function get_cache()

/**
 * Function to get cache from memory
 * @author Bobby Easland 
 * @version 1.0
 * @param string $name
 * @param string $method
 * @return mixed
 */	
	function get_cache_memory($name, $method = 'RETURN'){
		$data = ( isset($this->data['GLOBAL'][$name]) ? $this->data['GLOBAL'][$name] : $this->data[$name] );
		if ( isset($data) && !empty($data) && $data != false ){ 
			switch($method){
				case 'EVAL': // data must be PHP
					eval("$data");
					return true;
					break;
				case 'ARRAY': 
				case 'RETURN':
				default:
					return $data;
					break;
			} # end switch ($method)
		} else { 
			return false;
		} # end if (isset($data) && !empty($data) && $data != false)
	} # end function get_cache_memory()

/**
 * Function to perform basic garbage collection for database cache system 
 * @author Bobby Easland 
 * @version 1.0
 */	
	function cache_gc(){
		$this->db->Execute("DELETE FROM " . TABLE_SEO_CACHE . " WHERE cache_expires <= '" . date("Y-m-d H:i:s") . "'");
	}

/**
 * Function to check if the cache is in the database and expired  
 * @author Bobby Easland 
 * @version 1.0
 * @param string $name
 * @param boolean $is_cached NOTE: passed by reference
 * @param boolean $is_expired NOTE: passed by reference
 */	
	function is_cached($name, &$is_cached, &$is_expired){ // NOTE: $is_cached and $is_expired is passed by reference !!
		$this->cache_query = $this->db->Execute("SELECT cache_expires FROM " . TABLE_SEO_CACHE . " WHERE cache_id='".md5($name)."' AND cache_language_id='".(int)$this->languages_id."' LIMIT 1");
		$is_cached = ( $this->cache_query->RecordCount() > 0 ? true : false );
		if ($is_cached){ 
			$is_expired = ( $this->cache_query->fields['cache_expires'] <= date("Y-m-d H:i:s") ? true : false );
			unset($check);
		}
	}# end function is_cached()

/**
 * Function to initialize the redirect logic
 * @author Bobby Easland 
 * @version 1.1
 */	
	function check_redirect(){
		$this->need_redirect = false; 
		$this->uri = ltrim( basename($_SERVER['REQUEST_URI']), '/' );
		$this->real_uri = ltrim( basename($_SERVER['SCRIPT_NAME']) . '?' . $_SERVER['QUERY_STRING'], '/' );

		// damn zen cart attributes use illegal url characters
		if ($this->is_attribute_string($this->uri)) {
			$parsed_url = parse_url($this->uri);
			$this->uri_parsed = parse_url($parsed_url['scheme']);
			$this->uri_parsed['query'] = preg_replace('/products_id=([0-9]+)/', 'products_id=$1:' . $parsed_url['path'], $this->uri_parsed['query']);
		} else {
			$this->uri_parsed = parse_url($this->uri);
		}

		$this->attributes['SEO_REDIRECT']['URI'] = $this->uri;
		$this->attributes['SEO_REDIRECT']['REAL_URI'] = $this->real_uri;			
		$this->attributes['SEO_REDIRECT']['URI_PARSED'] = $this->uri_parsed;			
		$this->need_redirect(); 
		$this->check_seo_page();

		if ($this->need_redirect && $this->is_seopage && $this->attributes['USE_SEO_REDIRECT'] == 'true') {
			$this->do_redirect();			
		}
	} # end function
	
/**
 * Function to check if the URL needs to be redirected 
 * @author Bobby Easland 
 * @version 1.2
 */	
	function need_redirect() {
		$this->need_redirect = ((preg_match('/main_page=/i', $this->uri)) ? true : false);
		// QUICK AND DIRTY WAY TO DISABLE REDIRECTS ON PAGES WHEN SEO_URLS_ONLY_IN is enabled IMAGINADW.COM 
		$sefu = explode(",", ereg_replace( ' +', '', SEO_URLS_ONLY_IN ));
		if ((SEO_URLS_ONLY_IN!="") && !in_array($_GET['main_page'],$sefu) ) $this->need_redirect = false;
		// IMAGINADW.COM

		$this->attributes['SEO_REDIRECT']['NEED_REDIRECT'] = $this->need_redirect ? 'true' : 'false';
	}
	
/**
 * Function to check if it's a valid redirect page 
 * @author Bobby Easland 
 * @version 1.1
 */	
	function check_seo_page() {
		if (!isset($_GET['main_page']) || (!$this->not_null($_GET['main_page']))) {
			$_GET['main_page'] = 'index';
		}

		$this->is_seopage = (($this->attributes['SEO_ENABLED'] == 'true') ? true : false);

		$this->attributes['SEO_REDIRECT']['IS_SEOPAGE'] = $this->is_seopage ? 'true' : 'false';
	}
	
/**
 * Function to perform redirect 
 * @author Bobby Easland 
 * @version 1.0
 */	
	function do_redirect() {
		$p = @explode('&', $this->uri_parsed['query']);
		foreach( $p as $index => $value ) {						
			$tmp = @explode('=', $value);

			if ($tmp[0] == 'main_page') continue;

			switch($tmp[0]){
				case 'products_id':
					if ($this->is_attribute_string('products_id=' . $tmp[1])) {
						$pieces = explode(':', $tmp[1]);						
						$params[] = $tmp[0] . '=' . $pieces[0];
					} else {
						$params[] = $tmp[0] . '=' . $tmp[1];
					}
					break;
				default:
					$params[] = $tmp[0].'='.$tmp[1];
					break;						
			}
		} # end foreach( $params as $var => $value )
		$params = ( sizeof($params) > 1 ? implode('&', $params) : $params[0] );

		$url = $this->href_link($_GET['main_page'], $params, 'NONSSL', false);
		// cleanup url for redirection
		$url = str_replace('&amp;', '&', $url);

		switch($this->attributes['USE_SEO_REDIRECT']){
			case 'true':
				header("HTTP/1.1 301 Moved Permanently"); 
				header("Location: $url");
				break;
			default:
				$this->attributes['SEO_REDIRECT']['REDIRECT_URL'] = $url;
				break;
		} # end switch
	} # end function do_redirect

} # end class
?>
