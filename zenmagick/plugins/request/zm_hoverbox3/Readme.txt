======================================================
This is the ZenMagick plugin for Hover Box3 support
======================================================


Hover Box3
==========
HB3 is a zen-cart add-on and can be downloaded at http://zen-cart-templates.ichoze.net/.
This plugin includes the full HB3 code, adapted for ZenMagick.


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure ImageHandler2 options
4) Copy the themes/hover3 folder into your theme's content directory.


Template changes
================
1) Copy the hover3 folder from the theme_files directory into you theme's content directory.
   After that the file 'ic_hoverbox3.js' should have the path 'zenmagick/themes/[YOUR_THEME]/content/hover3/ic_hoverbox3.js'

2) All required JavaScript and CSS will be included in the page dynamically as required, so no changes to your layout are required.

3) To use Hover Box3 in your templates, use the function hover3_product_image_link(..). It expects two parameter (optionally three):
    - The product (a ZMProduct instance, usually this should be $zm_product)
    - A ZMImageInfo instance.
    - A flag to indicate whether to show the 'Larger Image' image. Default is true (usually false would be used for additional images).

   This function may be used anywhere in your templates. Just be careful to avoid displaying the same product more than once, as
   that might create slideshows with duplicate images.
   (Images are grouped based on the product id, so it is possible to have multiple slideshows for different products on a single page)

4) If you need to change any JavaScript defaults manually you may want to check/edit ic_hoverbox_config.tpl.


Template code examples
======================
1) Product info view:

Replace the line (around line #34):

      <a href="<?php zm_absolute_href($imageInfo->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><?php zm_image($imageInfo, PRODUCT_IMAGE_MEDIUM) ?></a>

with this call to hover3_product_image_link():

      <?php hover3_product_image_link($product, $imageInfo) ?>


For additional images, change the line ( around line #71):

      <a href="<?php zm_absolute_href($addImg->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><img src="<?php zm_absolute_href($addImg->getDefaultImage()) ?>" alt="" title="" /></a>

with this call:

      <?php hover3_product_image_link($product, $addImg, false) ?>


2) Reviews sidebox:

Replace the line:

      <p><a href="<?php zm_product_href($review->getProductId()) ?>"><?php zm_image($review->getProductImageInfo(), false) ?></a></p>

with:

      <?php $product = ZMProducts::instance()->getProductForId($review->getProductId()); ?>
      <p><?php hover3_product_image_link($product), $review->getProductImageInfo(), false) ?></p>
