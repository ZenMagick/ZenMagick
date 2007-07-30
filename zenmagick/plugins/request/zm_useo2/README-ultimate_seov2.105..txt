Ultimate SEO URLs 2.105 for Zen Cart 1.3.7
==========================================

COMPATIBLE with Zen cart 1.3.7
MYSQL 5.0 and PHP 5.0 - Compatible

New config option to select what pages must be rewrited and redirected. 
By default this option takes the important SEO pages (categories, products, static pages) and omit all the rest. You can disable it leaving it blank.

Now this contrib is safe to use in any site with any checkout procedure.


SEND YOUR BUGS AND SUGGESTIONS TO: http://dev.imaginacolombia.com
I'll try to update this contribution regularly.



Example URL Transformations:

From: http://yoursite.com/index.php?main_page=product_info&products_id=24
To: http://yoursite.com/disciples-sacred-lands-linked-p-24.html

From: http://yoursite.com/index.php?main_page=index&cPath=2_20
To: http://yoursite.com/software-strategy-c-2_20.html

From: http://yoursite.com/index.php?main_page=contact_us
To: http://yoursite.com/contact_us.html



==========================
INSTALLATION INSTRUCTIONS
==========================

1. Copy files from `_zen_cart_folder` to your Zen Cart install
2. Copy (or MERGE if you have made changes) files from v137-specific-files to your Zen Cart install
3. A sample .htaccess file is included. Simply rename to .htaccess and edit the word /shop/ to match your site
4. Config the module in CONFIGURATION - SEO URLS

==========================
UPDATE INSTRUCTIONS
==========================
No additional steps. Automatic Update. Contrib config will be reset.


===============
RECOMMENDATIONS
===============
If your site is new go to the config and change the option Enable automatic redirects to false.




========
HISTORY
========
2007-07-25 - Version 2.105 - New config option to select what pages must be rewrited and redirected. Now this contrib is safe to use in any site with any checkout procedure. (http://www.imaginacolombia.com)
2007-07-07 - Version 2.104 - COMPATIBLE with Zen cart 1.3.7 - MYSQL 5.0 y PHP 5.0 - $nosefu array to avoid seo in certain pages (http://www.imaginacolombia.com)
2006-09-04 - Version 2.103 - COMPATIBLE with Zen cart 1.3.5 (DrByte)
2005-07-22 – Version 2.100 - ORIGINAL VERSION http://www.dream-scape.com/pub/zencart/Ultimate_SEO_URLs/