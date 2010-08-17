===========================================================
Ultimate SEO URLs 2.109 for Zen Cart 1.3.9
===========================================================
COMPATIBLE with Zen cart 1.3.9

Version 2.109 is functionally identical to version 2.108.
Added updated files for  Zen cart 1.3.9 in the `v139-specific-files` folder.

MASK
2010-04-25

===========================================================
Ultimate SEO URLs 2.108 for Zen Cart 1.3.8
===========================================================
Version 2.108 is functionally identical to version 2.107. The only difference is that version 2.108 now supports SEO friendly EZ-Page URLs. 

DISCLAIMER: I DO NOT OFFER TECHNICAL SUPPORT FOR THIS MOD. 

Packaged and posted by a co-traveler on this adventure: 
Ronald Crawford
2010-04-02

===========================================================
Ultimate SEO URLs 2.107 for Zen Cart 1.3.8
===========================================================
Version 2.107 is functionally identical to version 2.106. The only difference is that the English and Spanish language versions have been packaged together in the same downloadable zip file. In the documentation which follows, all references to _zen_cart_folder should be read as either _zen_cart_folder_English or _zen_cart_folder_Spanish depending on the version you wish to install. All further documentation below this point is retained the same as in version 2.106.

DISCLAIMER: I DO NOT OFFER TECHNICAL SUPPORT FOR THIS MOD. I have simply packaged these pre-existing files together for convenience, and to avoid the trap that has led many a person into hours of confusion and grief. I did not write this mod and can't offer any support. Please note that debate continues as to whether the use of this mod will help or hurt your ranking and searchability in the various search engines. You are advised to read the support thread in the Zen Cart forum entitled "Ultimate SEO URLs" found at http://www.zen-cart.com/forum/showthread.php?t=44104 and decide for yourself. USE AT YOUR OWN RISK, after digesting the various arguments presented there. Keep in mind that some of the naysayers have a commercial competitor they wish to promote, and so EVERYTHING on the thread should be read with a grain of salt. As far as I know, this mod has been abandoned by its authors, but continues to be installed and used for whatever worth it may hold.

Packaged and posted by a co-traveler on this adventure: 
Alan Jones
2008-09-05

===========================================================
Ultimate SEO URLs 2.106 for Zen Cart 1.3.8
===========================================================

COMPATIBLE with Zen cart 1.3.8
MYSQL 5.0 and PHP 5.0 - Compatible

New config option to select what pages must be rewrited and redirected. 
By default this option takes the important SEO pages (categories, products, static pages) and omit all the rest. You can disable it leaving it blank.

Now this contrib is safe to use in any site with any checkout procedure.


SEND YOUR BUGS AND SUGGESTIONS TO: http://dev.imaginacolombia.com
That person has promised that he would try to update this contribution regularly.(I hav'nt)

This addon was modified by Anant Bhatia (antz.bin@gmail.com) so that it would work properly with Zen Cart 1.3.8 by merging the files provided by Zen Cart 1.3.8 with the ones provided by Ultimate SEO URLs 2.105 for Zen Cart 1.3.7.



Example URL Transformations:

From: http://yoursite.com/index.php?main_page=product_info&products_id=24
To: http://yoursite.com/disciples-sacred-lands-linked-p-24.html

From: http://yoursite.com/index.php?main_page=index&cPath=2_20
To: http://yoursite.com/software-strategy-c-2_20.html

From: http://yoursite.com/index.php?main_page=contact_us
To: http://yoursite.com/contact_us.html




===========================================================
INSTALLATION INSTRUCTIONS
===========================================================

1. Copy files from `_zen_cart_folder_English` to your Zen Cart install
2a. If you haven't made changes to Zen Cart, Copy and replace files from `v139-specific-files` to your Zen Cart install
2b. If you have made changes to your Zen Cart, follow merge instructions below
3. A sample .htaccess file is included. Simply rename to .htaccess and edit the word /shop/ to match your site
4. Config the module in CONFIGURATION - SEO URLS



===========================================================
MERGE INSTRUCTIONS (optional see installation instructions)
===========================================================

1. Open includes/functions/html_output.php

Locate:

	function zen_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $static = false, $use_dir_ws_catalog = true) {

Replace with:

	function zen_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $static = false, $use_dir_ws_catalog = true) {
		
		/* QUICK AND DIRTY WAY TO DISABLE REDIRECTS ON PAGES WHEN SEO_URLS_ONLY_IN is enabled IMAGINADW.COM */
		$sefu = explode(",", ereg_replace( ' +', '', SEO_URLS_ONLY_IN ));
		if((SEO_URLS_ONLY_IN!="") && !in_array($page,$sefu)) {
			return original_zen_href_link($page, $parameters, $connection, $add_session_id, $search_engine_safe, $static, $use_dir_ws_catalog);
		}
		
		if (!isset($GLOBALS['seo_urls']) && !is_object($GLOBALS['seo_urls'])) {
			include_once(DIR_WS_CLASSES . 'seo.url.php');

			$GLOBALS['seo_urls'] = &new SEO_URL($_SESSION['languages_id']);
		}

		return $GLOBALS['seo_urls']->href_link($page, $parameters, $connection, $add_session_id, $static, $use_dir_ws_catalog);
	}
 
	/*
	 * The HTML href link wrapper function
	 */
	function original_zen_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $static = false, $use_dir_ws_catalog = true) {





2. Open admin/categories.php

Locate:

	$action = (isset($_GET['action']) ? $_GET['action'] : '');

Below it add:

    // Ultimate SEO URLs v2.100
	// If the action will affect the cache entries
	if (preg_match("/(insert|update|setflag)/i", $action)) {
		include_once(DIR_WS_INCLUDES . 'reset_seo_cache.php');
	}




3. Open admin/product.php

Locate:

	$action = (isset($_GET['action']) ? $_GET['action'] : '');

Below it add:

    // Ultimate SEO URLs v2.100
	// If the action will affect the cache entries
	if (preg_match("/(insert|update|setflag)/i", $action)) {
		include_once(DIR_WS_INCLUDES . 'reset_seo_cache.php');
	}





===========================================================
UPDATE INSTRUCTIONS
===========================================================
No additional steps. Automatic Update. Contrib config will be reset.




===========================================================
RECOMMENDATIONS
===========================================================
If your site is new go to the config and change the option Enable automatic redirects to false.




===========================================================
HISTORY
===========================================================
2010-08-15 - Version 2.110 - 1.3.9 updates (That Software Guy)     
2010-04-25 - Version 2.109 - COMPATIBLE with Zen cart 1.3.9 (MASK)
2010-04-02 - Version 2.108 - SEO friendly EZ-Page URLs are now supported
2008-09-05 - Version 2.107 - Merely re-packages the English and Spanish iterations of v2.106 into one downloadable zip file.
2008-01-22 - Version 2.106 - COMPATIBLE with Zen cart 1.3.8
2007-07-25 - Version 2.105 - New config option to select what pages must be rewrited and redirected. Now this contrib is safe to use in any site with any checkout procedure. (http://www.imaginacolombia.com)
2007-07-07 - Version 2.104 - COMPATIBLE with Zen cart 1.3.7 - MYSQL 5.0 y PHP 5.0 - $nosefu array to avoid seo in certain pages (http://www.imaginacolombia.com)
2006-09-04 - Version 2.103 - COMPATIBLE with Zen cart 1.3.5 (DrByte)
2005-07-22 - Version 2.100 - ORIGINAL VERSION http://www.dream-scape.com/pub/zencart/Ultimate_SEO_URLs/
