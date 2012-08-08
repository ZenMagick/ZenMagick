A ZenMagick plugin to display Wordpress content from within a ZenMagick template.

This plugin has been tested with Wordpress 2.5 and 2.6.


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure as required using the Plugin Manager
   * All that is required is to set the root folder of your Wordpress installation
   * Optionally, you can configure a list of pages that can display WP content (empty means all)
     CAUTION: Loading WP content does affect performance, so limiting it is usually a good idea!
4) Copy the wp folder into your theme's views directory


The plugin contains a folder wp that contains example view templates to illustrate how things
are done. The blog index url is something like ../index.php?main_page=wp.
In templates it may be generated using the toolbox with:

  $net->url('')


Views
=====
The view files are pretty much vanilla Wordpress theme files, so almost all Wordpress code should work.

Please note that the comments form needs a custom hidden form field 'redirect_to' in order to return back
to the ZenMagick view. If removed, the regular blog page will be displayed.
Also, the action of the search form is not Wordpress standard and a hidden field 'main_page' has been added.

Depending on your needs you might want to remove all Wordpress theme files in order to disable direct access (perhaps
with the exception of the 404.php).

To customize views, just copy the views/wp folder into your theme's content folder. So, the location of the wp
index.php file should be zenmagick/themes/[YOUR_THEME]/content/views/wp/index.php.
Theme view files always take precedence over the plugin version.


Sidebar
=======
To only show wp-sidebar.php when in the blog add the following to your theme's
EventListener onThemeLoaded event:

    if ('' == $request->getRequestId()) {
        $this->container->get('templateManager')->setRightColBoxes(array('wp-sidebar.php'));
    }


Permalinks
==========
If you want to use Wordpress permalinks (Apache httpd only), you can do so by following these instructions:

1) Configure a URL path prefix.
   This is required to make the redirect rules work. These instructions will assume a value of 'blog'

2) Add the following redirect rules to the main .htaccess file (replace 'blog' with whatever
   you configured as path prefix):

      RewriteRule ^blog/?$ index.php?main_page=wp [QSA,L]
      RewriteRule ^blog/.*$ index.php?main_page=wp [QSA,L]
      RewriteRule ^blog\?(.*)$ index.php?main_page=wp?$1 [QSA,L]

3) Enable permalinks in Wordpress

NOTE: This will only work if wordpress is *not* installed in the document root, but in a subfolder.


Limitations
===========
RSS URLs are not rewritten. This means those will point to the original Wordpress installation.
