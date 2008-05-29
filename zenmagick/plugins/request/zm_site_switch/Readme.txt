This plugin allows to use a single zen-cart/ZenMagick installation with multiple domain names.
Additionally, each domain may use a different theme.
This allows to have, for example, the URLs www.myshoes.com and www.great-shoes.com point to the same
installation. 
This could be useful for different branding (via different themes).

This plugin is inspired by the zen-cart multisite mod.


Installation
============
* Download (obvious ;)
  Download the latest version from http://www.zenmagick.org

* Extract into the ZenMagick plugins directory

* Install the plugin via the ZenMagick plugins admin page

* Configure hostnames/themes (Plugins -> Site Switching)

* Done!


Troubleshooting
===============
The configured information is stored in a flat PHP file in the plugin directory.
Additionally, a include for this file is added to includes/local/configure.php. If configure.php
or the local folder itself do not exist they are created.

So, certain file permissions to create local and/or write/update files in the local folder are required.
