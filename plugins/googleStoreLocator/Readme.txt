ZenMagick Google Store Locator Plugin
=====================================
This plugin adds a Google Maps based store locator page to the store.


INSTALLAION
===========
1) Download (obvious ;)
   Download the latest version from http://www.zenmagick.org
2) Extract into the ZenMagick plugins directory
3) Install the plugin via the ZenMagick plugins admin page
4) Configure your store location using the plugin's setup page.
5) Enjoy!


CONFIGURATION
=============
The plugin requires a Google account and a Google Maps access key for your store URL.
If you want to use the admin page to find out the store location, you'll need a 
second key for the admin URL.
Keys can be requested here: http://www.google.com/apis/maps/signup.html

The store URL is everything before index.php?main_page=index,
the admin URL is the store URL + the admin directory name.

Example:
Hostname: www.mystore.com
Zen-Cart installed in document root

Store URL: http://www.mystore.com/
Admin URL: http://www.mystore.com/admin/


The other values are:
- store location (lonitude, latitude)
- initial zoom level


URL and view
============
The URL for the store locator is index.php?main_page=store_locator

The default view used is store_locator.php in the plugin folder. To customize, just copy it into
your theme's views folder and edit.

NOTE: The "locator_map" id on the map div is required for the map to load. Everything else may be customized.
