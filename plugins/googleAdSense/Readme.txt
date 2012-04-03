ZenMagick Google AdSense plugin
===============================
This plugin allows to configure up to 6 Google AdSense ads, including four sideboxes.


INSTALLAION
===========
1) Download (obvious ;)
   Download the latest version from http://www.zenmagick.org
2) Extract into the ZenMagick plugins directory
   After that you should have a googleAdSense folder in the plugins/general folder.
3) Install the plugin via the ZenMagick plugins manager
4) Re-run the 'create sidebox dummy files' file patch from the installation page
5) Configure the Google JavaScript; the first four will be used by the sideboxes, #5 and #6 may be used anywhere
   in your template.

To customize the sideboxes, copy them into your theme's content/boxes folder and change as desired.

To pull the configured AdSense code directly, you can use the following code.
Example: Display Google AdSense code for Ad #5:

  <?php echo $googleAdSense->getAd(5); ?>


NOTES
=====
The included sideboxes are not required. It is also possible to use all configured ads manually in your templates.
Just keep in mind that, if enabled, the sideboxes will use the JS configured in the first four ads.

If you need more that 6 ads, it is possible to change the max by setting 'plugins.googleAdSense.totalAds' to whatever 
number of ads required.
It is required to set this before installing the plugin (for example in your global local.php file). If the plugin is already
installed:
a) Save already configured JS
b) Uninstall
c) Configure the ad max via the setting
d) Install plugin

Example of how to set the max to 8:

storefront,admin:
  settings:
    plugins:
      googleAdSense:
        totalAds: 8
