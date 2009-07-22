======================================================
This is the ZenMagick plugin for Fual Slimbox support
======================================================


Fual Slimbox
============
Fual Slimbox is another variation of the lightbox effect, however the underlying JavaScript is a bit more lightweight (slim!).
This plugin is based on the Zen Cart fual_slimbox mod (v0.1.5)
In contrast to the original mod, this plugin tries to stay out of the way of the designer, so the changes required to templates are minimal.
Additional HTML/CSS may be used to create additional links to trigger the lightbox.


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure Fual Slimbox options
4) Copy the theme_files/slimbox folder into your theme's content directory.


Template changes
================
1) Copy the slimbox folder from the theme_files directory into you theme's content directory.
   After that the file 'stylesheet_slimbox_ex.css' should have the path 'zenmagick/themes/[YOUR_THEME]/content/slimbox/stylesheet_slimbox_ex.css'
   To use the plugin from more than one theme, you can also copy the slimbox folder into the default theme.

2) All required JavaScript and CSS will be included in the page dynamically as required, so no changes to your layout are required.

3) To use Fual Slimbox on images, add the rel attribute to all a elements pointing to large images or as required.
   In the example below, the images are all grouped by the ref 'gallery'. In order to avoid mixing images, it is recommented to use
   different group references for unrelated images (for example in sideboxes); example: rel="lightbox[featured]".


Template code examples
======================
1) Product info view:

      <a href="<?php $net->absolute($imageInfo->getLargeImage()) ?>" rel="lightbox[gallery]"><?php zm_image($imageInfo, PRODUCT_IMAGE_MEDIUM) ?></a>

2) For additional images (around line #71):

      <a href="<?php $net->absolute($addImg->getLargeImage()) ?>" rel="lightbox[gallery]"><img src="<?php $net->absolute($addImg->getDefaultImage()) ?>" alt="" title="" /></a>

3) To create additional links to open the lightbox do something like this:

      <a href="<?php $net->absolute($imageInfo->getLargeImage()) ?>" rel="lightbox[gallery]">CLick to enlarge</a>


