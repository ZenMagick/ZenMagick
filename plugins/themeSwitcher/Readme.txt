This is a ZenMagick plugin to allow users to switch between different themes.


Installation
============
1) Download and unpack SImple SEO URL and unpack into your zen-cart
   installation as documented except, DO NOT UPDATE/REPLACE html_output.php
2) Unzip the plugin package into the zenmagick/plugins directory.
3) Install plugin using the ZenMagick Plugin Manager.


How to switch
=============
Without anything, the configured theme will be used. A URL like
index.php?themeId=my_theme can be used to switch to a different theme.

The following code is an example of how a list of theme URLs could be generated:

  $themeIds = array('default', 'foo');
  foreach ($themeIds as $theme) {
      echo '<a href="'.$net->url(null, 'themeId='.$theme, $request->isSecure(), false).'">'.$theme.'</a> ';
  }


Automatic style switch option
=============================
If the setting 'plugins.themeSwitcher.themes' is set, it is taken as a list of themes to be displayed in a simple
list. The links will be injected right after the body tag.
To customize please see the filterResponse method of the plugin.

Setting examples:

* simple (list of theme ids): 

storefront:
  settings:
    themeSwitcher:
      themes: default,demo

* advanced (themeId and label):

storefront:
  settings:
    themeSwitcher:
      themes: 'default:Default,demo:Demo'

If the setting is not set or null, a list of all themes will be build.
