ZenMagick Google Analytics plugin
=================================

This plugin allows to configure Goggle Analytics for use with the store.

The plugin default is to create the new ga style JavaScript. If you need the older
urchin style code, add the following line to your global or theme local.php:

    ZMSettings::set('plugins.googleAnalytics.urchin', true);


INSTALLAION
===========
* Download (obvious ;)
  Download the latest version from http://www.zenmagick.org/

* Extract into the ZenMagick plugins directory
  After that you should have a googleAdsence sub-directory in the plugins/general folder.

* Install the plugin via the ZenMagick plugins admin page

* Configure using your Google Analytics details.
  NOTE: As per default the plugin is configured for debug; that means the code is not really active.
  You'll have to change that once you are happy with the configuration.

* Enjoy!
