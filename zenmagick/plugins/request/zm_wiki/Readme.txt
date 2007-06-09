ZenMagick Wiki Plugin
=====================

This plugin adds a simple wiki to ZenMagick. The wiki itself is driven by
pawfaliki (http://www.pawfal.org/pawfaliki).


INSTALLAION
===========
* Download (obvious ;)
  Download the latest version from http://zenmagick.radebatz.net

* Extract into the ZenMagick request plugins directory

* Install the plugin via the ZenMagick plugins admin page

* After that you should be able to browse to the zen-cart main_page 'wiki';
  Example: http://your.host/index.php?main_page=wiki

* Enjoy!


CONFIGURATION
=============
The plugin checks one ZenMagick setting with name 'plugin.zm_wiki.restriction'.
This controls which users are allowed to edit wiki pages. Valid settings are:

'ALL'                      = everyone can edit
'REGISTERED'               = logged in users can edit
'ADMIN'                    = only admin can edit
'NONE' (or anything else)  = read only

The setting can be configured using the global local.php file; for example, to
give edit rights to registered users only, add the following line:


    zm_set_setting('plugin.zm_wiki.restriction', 'REGISTERED');
