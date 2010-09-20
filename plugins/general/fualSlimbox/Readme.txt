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


Template changes
================
1) To use Fual Slimbox on images, add the rel attribute to all a elements pointing to large images or as required.
   In the example below, the images are all grouped by the ref 'gallery'. In order to avoid mixing images, it is recommented to use
   different group references for unrelated images (for example in sideboxes); example: rel="lightbox[featured]".

2) The Funky option of the 'Paranoia Mode' setting is not supported, in order to do that the template will nee to be changed manually by wrapping the image
   in a div with an id of 'slimboxWrapper'. See the examples for more details.


Template code examples
======================
1) Product info view:

          <a href="<?php $request->absoluteUrl($imageInfo->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><?php $html->image($imageInfo, ZMProducts::IMAGE_SMALL) ?></a>

2) For additional images (around line #71):

      <a href="<?php $request->absoluteUrl($addImg->getLargeImage()) ?>" rel="lightbox[gallery]"><img src="<?php $request->absoluteUrl($addImg->getDefaultImage()) ?>" alt="" title="" /></a>

3) To create additional links to open the lightbox do something like this:

      <a href="<?php $request->absoluteUrl($imageInfo->getLargeImage()) ?>" rel="lightbox[gallery]">Click to enlarge</a>

4) Image with funky display wrapper div:

      <div id="slimboxWrapper"><a href="<?php $request->absoluteUrl($imageInfo->getLargeImage()) ?>" rel="lightbox[gallery]"><?php $html->image($imageInfo, ZMProducts::IMAGE_MEDIUM) ?></a></div>

