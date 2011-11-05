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

