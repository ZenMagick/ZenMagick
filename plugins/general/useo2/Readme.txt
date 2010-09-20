This is the ZenMagick plugin for Ultimate SEO 2.x support. 
The plugin is based on version 2.105 of the Ultimate SEO mod as found here: 
http://www.zen-cart.com/index.php?main_page=product_contrib_info&cPath=40_47&products_id=231

NOTE: This plugins will only work in combination with Apache httpd!


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) If you are not using a .htacces file you can copy the included htaccess_sample file into your
   store root directory (where the main 'index.php' file is located, next to the zenmagick folder).
   If you already use a .htaccess file, you'll have to merge the RewriteRules from htaccess_sample
   manually into your .htaccess file.
3) Make sure the RewriteBase option is configured properly. (ZenMagick also has a patch to adjust this for you)
4) Install plugin using the ZenMagick Plugin Manager.

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
