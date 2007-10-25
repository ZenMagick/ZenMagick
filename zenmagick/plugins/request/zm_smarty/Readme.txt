ZenMagick plugin to use the Smarty Template Engine rather than straight PHP.

Introduction
============
ZenMagick does not use a particular templating mechanism. Instead plain PHP is
used in theme/templating files. This gives the theme designer the full power
of PHP and s particularly useful for sidebox development.

Template engines, in contrast, use their own syntax and conventions. They are
a good (better?) way of separating processing from layout, as only restricted
functionallity is available.

Smarty is such a templating engine and included in this plugin.


Installation
============
Smarty itself is not included in this plugin and needs to be downloaded and installed
(copied/uploaded) to your server manually.

1) Unzip the plugin package into the zenmagick/plugins directory.
2) Extract/copy the included demo theme into your theme folder (zenmagick/themes)
3) Create dummy files for zen-cart to recognize the new theme by re-applying the
appropriate patch from the ZenMagick installation screen (admin)
4) Select the new theme (smartyDefault)
5) Install the plugin using the ZenMagick Plugin Manager.
6) Configure the path to your Smarty installation, for example '/usr/local/lib/php/Smarty'
If the value is left empty, the plugin will expect Smarty in '[PLUGIN_DIR]/smarty'

You may either use the included theme for further development or take a copy.
Smarty themes **do not support theme defaults**, so you'll have to include everything
you need in order to work properly.


Configuration
=============
The following smarty class variables are configured with defaults:
$template_dir = [THEME_FOLDER]/content
$compile_dir = [THEME_FOLDER]/templates_c
$cache_dir = [THEME_FOLDER]/cache
$config_dir = [THEME_FOLDER]/configs
$plugins_dir = array( 'plugins', '[PLUGIN_DIR]/zm_plugins');

To customize those and other settings you can provide a callback function zms_smarty_config.
An example function is included in the demo theme.
