ZenMagick Google Store Locator Plugin
=====================================
This plugin adds a Google Maps based store locator page to the store.


INSTALLAION
===========
* Download (obvious ;)
  Download the latest version from http://zenmagick.radebatz.net

* Extract into the ZenMagick plugins directory

* Install the plugin via the ZenMagick plugins admin page

* Configure your store location using the plugin's setup page.

* If using ZenMagick's pretty links feature, you will have to add the following
  line to your main .htaccess file:

  RewriteRule ^store_locator/?$ index.php?main_page=store_locator [QSA,L]

* Enjoy!


CONFIGURATION
=============
The plugin requires a Google account and a Google Maps access key.

The other values are:
- store location (lonitude, latitude)
- initial zoom level


URL
===
A URL to the store locator may be created with this line:

zm_href(ZM_FILENAME_STORE_LOCATOR);
