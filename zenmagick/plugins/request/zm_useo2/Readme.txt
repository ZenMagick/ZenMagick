This is the ZenMagick plugin for Ultimate SEO 2.x support. The plugin is based
on version 2.103 as found here: 
http://www.zen-cart.com/index.php?main_page=product_contrib_info&cPath=40_47&products_id=231


NEW Installation
================
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Add the Apache webserver redirect rules from the file htaccess_sample
   to your .htaccess file (the ZenMagick version has a comment to indicate where
   to paste them)
3) Install plugin using the ZenMagick Plugin Manager.

After the plugin has been installed you should have the Ultimate SEO
configuration options available in the admin interface.


Coexistence with Ultimate SEO 3.x
=================================
The code included in this plugin does not break Ultimate SEO 3.x. However,
the 3.x version does check for older versions and disables them, even if not
active itself. Therefore, if Ultimate SEO 3.x is installed, this version will
not work.
Also, installing/uninstalling this plugin will remove any SEO configuration value
in the database.


Configuration options
=====================
As with the previous integrated version of Ultimate SEO, pages may be
enabled/disabled, using the setting 'seoEnabledPagesList'
