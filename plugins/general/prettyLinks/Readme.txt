This is the ZenMagick plugin for pretty link (SEO) support.

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


Configuration
=============
It is possible to limit ULR conversion to certain requests by configuring a list of enabled request Ids (main_page=..)
using 'plugins.prettyLinks.seoEnabled'.
