ZenMagick Wiki Plugin
=====================
This plugin adds a simple wiki to ZenMagick. The wiki itself is driven by
pawfaliki (http://www.pawfal.org/pawfaliki).

NOTE: pawfaliki.php is a patched version to work in this context. Updating
this file with a newer version without modifications will *not* work.


INSTALLAION
===========
* Download (obvious ;)
  Download the latest version from http://zenmagick.radebatz.net

* Extract into the ZenMagick plugins directory

* Install the plugin via the ZenMagick plugins admin page

* After that you should be able to browse to the zen-cart main_page 'wiki';
  Example: http://your.host/index.php?main_page=wiki

* If using ZenMagick's pretty links feature, you will have to add the following two
  lines to your main .htaccess file:

  RewriteRule ^wiki/([^/]+)/?$ index.php?main_page=wiki&page=$1 [QSA,L]
  RewriteRule ^wiki/?$ index.php?main_page=wiki&page=WikiRoot [QSA,L]

* Enjoy!


NOTE: The plugin will create some directories to store the data and temp files. Those
directories will not be deleted on uninstall:
* wiki/files
* wiki/tmp


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


SIDEBOX
=======
The plugin also creates a sidebox 'zm_wiki' that will display the contents of
'WikiNav'. This cona be configured like any other sidebox. The template is
generated in the default theme.


LAYOUT
======
If you would like to use a different layout for the wiki pages, add the following
layout settings to your theme's ThemeInfo class (contructor method):

        $this->setLayout('zm_view_wiki', 'wiki_layout');
        $this->setLayout('zm_view_wiki_edit', 'wiki_layout');

