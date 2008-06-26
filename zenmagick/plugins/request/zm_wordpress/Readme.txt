A ZenMagick plugin to display Wordpress content from within a ZenMagick template.

This plugin has been tested with Wordpress 2.5.


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure as required using the Plugin Manager
   * All that is required is to set the root folder of your Wordpress installation
4) Copy the wp folder into your theme's views directory


The plugin contains a folder wp that contains example view templates to illustrate how things
are done. The blog index url is something like ../index.php?main_page=wp.
In templates it may be generated using the toolbox with:

  $net->url(FILENAME_WP)

The view files are pretty much vanilla Wordpress theme files, so almost all Wordpress code should work.

Please note that the comments form needs a custom hidden form field 'redirect_to' in order to return back
to the ZenMagick view. If removed, the regular blog page will be displayed.

Depending on your needs you might want to remove all Wordpress theme files in order to disable direct access (perhaps
with the exception of the 404.php).


NOTE: It is possible to configure the plugin to use the view files from the plugin folder. However this is not
recommended as they might get overriden in subsequent releases.
To configure this, the setting 'plugins.zm_wordpress.isUseOwnViews' needs to be set to true.
