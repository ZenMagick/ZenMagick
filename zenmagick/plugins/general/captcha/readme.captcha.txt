Name
====
CAPTCHA

Version Date
==============
v. 2.9 11.08.2008 15:01

Author
======
Andrew Berezin http://eCommerce-Service.com/

Description
===========
This Script generates an CAPTCHA image using GD library and true type fonts (TTF).

Affected files
==============
/includes/modules/pages/contact_us/header_php.php
/includes/modules/pages/links_submit/header_php.php
/includes/modules/pages/product_reviews_write/header_php.php
/includes/modules/pages/tell_a_friend/header_php.php
/includes/modules/YOUR_TEMPLATE/create_account.php
/includes/templates/YOUR_TEMPLATE/templates/tpl_contact_us_default.php
/includes/templates/YOUR_TEMPLATE/templates/tpl_links_submit_default.php
/includes/templates/YOUR_TEMPLATE/templates/tpl_modules_create_account.php
/includes/templates/YOUR_TEMPLATE/templates/tbl_product_reviews_write_default.php
/includes/templates/YOUR_TEMPLATE/templates/tpl_tell_a_friend_default.php

Added files
===========
/fonts/verdana.ttf

Affects DB
==========
Yes (creates new records into configuration_group and configuration tables)

DISCLAIMER
==========
Installation of this contribution is done at your own risk.
Backup your ZenCart database and any and all applicable files before proceeding.

Features:
=========
- using TTF and GD library
- using SESSION to store generated CAPTCHA Code and image (no data base tables using)

Install:
========
0. BACKUP! BACKUP! BACKUP! BACKUP! BACKUP! BACKUP! 
1. Unzip and upload all new files to your store directory;
2. EDIT: 
Use applied files as a sample. All changes of a code are made in brackets 
// BOF CAPTCHA
... code ...
// EOF CAPTCHA
/includes/modules/pages/contact_us/header_php.php
/includes/modules/pages/links_submit/header_php.php
/includes/modules/pages/tell_a_friend/header_php.php
/includes/modules/YOUR_TEMPLATE/create_account.php
/includes/templates/YOUR_TEMPLATE/templates/tpl_links_submit_default.php
/includes/templates/YOUR_TEMPLATE/templates/tpl_modules_create_account.php
/includes/templates/YOUR_TEMPLATE/templates/tpl_contact_us_default.php
/includes/templates/YOUR_TEMPLATE/templates/tpl_tell_a_friend_default.php

3. Go to Admin->Tools->Install SQL Patches and install install.sql (don't use upload - use copy/paste to install sql).
4. Go to Admin>Configuration>CAPTCHA and setting up your CAPTCHA configuration; 
5. If you want to add or remove using ttf you must do it in /fonts/ directory.

Tips
====
You can get free true type fonts here - http://www.dustismo.com/

History
=======
v 1.0 18.09.2006 15:05
Initial version
v 1.9 28.02.2007 9:11
1. All code revision
2. Add trap ob_start()/ob_end_clean() including application_top.php in captcha_img.php.
3. Add links_submit support
v 2.0 05.03.2007 8:20
1. Generate CAPTCHA code when img displaing;
2. Add Redraw ability;
3. Add redraw_button function;
v 2.1 06.03.2007 8:48
1. Code optimization;
2. Add new config parameter - Image Type.
v 2.2 15.03.2007 16:12
1. Use zen_href_link to generate img address;
2. Add Review Write page support (Thanks to Mega Man);
3. Add french language (Thanks to Mega Man).
v 2.3 16.03.2007 10:51
1. Some Admin configuration adjustments added;
2. Remove non CAPTCHA update code from Review Write page;
3. Add gif support;
v 2.4 20.03.2007 11:39
1. Support for GD 1;
2. Use of a class dir instead of function opendir;
v 2.5 23.03.2007 13:11
1. Remove "glob" function;
2. Add error messages constants;
3. Add session id to captcha_img.php href link;
4. Use direct output (no output buffer used);
5. The functions generateCaptcha(), createCaptchaImg() and showCaptchaImg() are incorporated in 
   one function generateCaptcha();
v 2.5a 24.03.2007 16:00
1. Add captcha_debug.php.
v 2.6 31.03.2007 10:43
1. Fixed ssl problem. Thanks to KennyOz.
v 2.7 28.04.2007 6:12
1. Remove "ob_" functions from captcha_img.php.
2. Add 
header('Content-Transfer-Encoding: binary');
header('Content-Disposition:attachment; filename=captcha_img.' . $this->img_type);
v 2.7a 23.06.2007 19:25
1. Add German language - Thanks to Nicolas Schudel.
v 2.8 28.04.2007 6:12
1. Bug fix - use zen_image_button;
v 2.9 11.08.2008 15:01
1. Bug fix - use zen_image_button with IMAGE_USE_CSS_BUTTONS == 'yes';
2. Update to support ZC1.3.8a and Links Manager 3.3.1 - thanks to t. mike howeth
3. corrected text in /includes/languages/english/extra_definitions/captcha.php - thanks to t. mike howeth
