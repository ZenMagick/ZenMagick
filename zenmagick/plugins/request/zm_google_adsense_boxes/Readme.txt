ZenMagick Google AdSense Boxes plugin
=====================================

This plugin allows to configure up to 4 (default) sideboxes with Google AdSense ads.


INSTALLAION
===========
* Download (obvious ;)
  Download the latest version from http://zenmagick.radebatz.net

* Extract into the ZenMagick request plugins directory
  After that you should have a zm_google_adsence_boxes sub-directory in the plugins/request folder.

* Install the plugin via the ZenMagick plugins admin page
  - The installation will generate dummy files for zen-cart so the standard zen-cart box manager
    will recognize the new boxes
  - The installation will also create default implementations for the boxes in the ZenMagick default theme

* Configure the Google JavaScript that will generate the actual ads in the boxes

* Copy/Create individual boxes implementations for your theme (optional)


Plugin Customization
====================
It is possible to customize some of the plugin aspects. More specific the following
settings may be changed:

* _ZM_GOOGLE_ADSENSE_BOXES_COUNT
  The number of sideboxes that may be managed via the plugin

* _ZM_GOOGLE_ADSENSE_BOX_PREFIX
  The box prefix; this controlls the box filename

* _ZM_GOOGLE_ADSENSE_BOX_TEMPLATE
  Perhaps the most interesting options. Changing this setting allows to use a custom 
  template that will be used to generate the boxes.

Settings must be stored in a file called config.php. config-sample.php is provided as
starting point using the default values.

DO NOT EDIT config-sample.php or the actual plugin class, as updates will overwrite those files!
