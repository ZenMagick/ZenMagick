This plugin allows to use a single zen-cart/ZenMagick installation with multiple domain names.
Additionally, each domain may use a different theme.
This allows to have, for example, the URLs www.myshoes.com and www.great-shoes.com point to the same
installation. 
This could be useful for different branding (via different themes).

This plugin is inspired by the zen-cart multisite mod.

The plugin can also enforce that accounts are only valid for the domain name they were originally
created for. This does not work when 'isLegacyAPI' is enabled.
The default is to share accounts. To disable that, set 'plugins.zm_site_switch.isShareAccounts' to false;
for example:

    ZMSettings::set('plugins.zm_site_switch.isShareAccounts', false);


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

If in doubt, go into the Multi Site Admin page (Plugins -> Multi Site) and save the configured settings again.


Uninstall
=========
The plugin can be uninstalled by using the Plugin Manager. Please note that the generated config file is not
deleted. 
Also, the SQL changes need to be removed manually. This is in order to prevent accidental data loss. Once the
column is removed, there is no way for this plugin to restore the data.
