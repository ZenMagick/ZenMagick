======================================================
This is the ZenMagick plugin for ImageHandler2 support
======================================================

Installation
============
1) Download, unpack and install the zen-cart ImageHandler2 mod.
2) Unzip this plugin package into the zenmagick/plugins directory.
3) Create a new folder 'ih2' in your theme's content folder. If more than one theme is used, you may want to create this in the
   default theme's content folder instead.
3) Copy style_imagehover.css and jscript_imagehover.js into the newly created ih2 folder.
4) Install plugin using the ZenMagick Plugin Manager.
5) Configure ImageHandler2 options


Notes
=====
* Depending on the image dimensions used, you might want to change the width/height for the image popup window in common.js.
* It is possible to disable the hover effect via a plugin option in the Plugin Manager. This will remove all attributes from
  the generated <img> elements. This will also supress inclusion of the the IH2 specific .css and .js files.
