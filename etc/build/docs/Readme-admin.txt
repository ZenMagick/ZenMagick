Welcome to ZenMagick Admin
==========================
Version: ${zenmagick.version}


1. Introduction
===============
This is the admin-only package of ZenMagick. It can be installed and run in paralell with
the existing Zen Cart store and admin applications.


2. Installation
===============
The only requirement is that the listed SQL patches (Tools -> ZenMagick Installation) are installed.
The corresponding SQL scripts can be found under zmadmin/shared/etc/sql/mysql.

NOTE: If you have renamed your Zen Cart admin folder you will also need to do the following:
a) Create a new file zmadmin/local.php
b) Edit and insert the following (rename 'admin' with the name of your admin folder):
<?php define('ZC_ADMIN_FOLDER', 'admin'); ?>


3. Additional information
=========================
For more details about ZenMagick please check: http://www.zenmagick.org/

The most recent version can be downloaded at: http://sourceforge.net/project/showfiles.php?group_id=194891
