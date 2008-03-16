======================================================
This is the ZenMagick plugin for ImageHandler2 support
======================================================

Installation
============
1) Download, unpack and install ImageHandler2
2) Unzip the plugin package into the zenmagick/plugins directory.
3) Install plugin using the ZenMagick Plugin Manager.
4) Configure ImageHandler2 options


Template changes
================
In order to use the image hover feature of ImageHandler2, the following
manual steps are required:

a) Copy style_imagehover.css and jscript_imagehover.js into the default theme's content folder (zenmagick/themes/default/content)
These files are part of the ImageHandler2 distribution.

b) Add the following two lines to the <head> element of the used layout file(s):

    <link rel="stylesheet" type="text/css" media="screen,projection" href="<?php ZMRuntime::getTheme()->themeURL("style_imagehover.css") ?>" />
    <script type="text/javascript" src="<?php ZMRuntime::getTheme()->themeURL("jscript_imagehover.js") ?>"></script>

Ideally, they would be the last two lines before the closing head tag (</head>)

c) You also might want to adjust the width/height for the image popup in common.js.
