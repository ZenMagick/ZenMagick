=====================================================
This is the ZenMagick plugin for HoverBox3 support
=====================================================


HoverBox3
=========
HoverBox3 is a zen-cart add-on and can be downloaded at http://zen-cart-templates.ichoze.net/.
This plugin includes the full HoverBox3 code, adapted for ZenMagick.


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure HoverBox3 options


Template changes
================
1) All required JavaScript and CSS will be included in the page dynamically as required, so no changes to your layout are required.

2) To use HoverBox3 in your templates, use the function hover3_product_image_link(..). It expects two parameter (optionally three):
    - The product (a ZMProduct instance, usually this should be $currentProduct)
    - A ZMImageInfo instance.
    - A flag to indicate whether to show the 'Larger Image' image. Default is true (usually false would be used for additional images).

   This function may be used anywhere in your templates. Just be careful to avoid displaying the same product more than once, as
   that might create slideshows with duplicate images.
   (Images are grouped based on the product id, so it is possible to have multiple slideshows for different products on a single page)

3) If you need to change any JavaScript defaults manually you may want to check/edit ic_hoverbox_config.tpl.


Template code examples
======================
1) Product info view:

Replace the line (around line #34):

      <a href="<?php echo $request->absoluteURL($imageInfo->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><?php echo $html->image($imageInfo, PRODUCT_IMAGE_MEDIUM) ?></a>

with this call to hover3_product_image_link():

      <?php hover3_product_image_link($this, $currentProduct, $imageInfo) ?>


For additional images, change the line (around line #71):

      <a href="<?php echo $request->absoluteURL($addImg->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><img src="<?php $request->absoluteURL($addImg->getDefaultImage()) ?>" alt="" title="" /></a>

with this call:

      <?php hover3_product_image_link($this, $currentProduct, $addImg, false) ?>


2) Reviews sidebox:

Replace the line:

      <p><a href="<?php $net->product($review->getProductId()) ?>"><?php $html->image($review->getProductImageInfo()) ?></a></p>

with:

      <?php $product = ZMProducts::instance()->getProductForId($review->getProductId()); ?>
      <p><?php hover3_product_image_link($this, $currentProduct), $review->getProductImageInfo(), false) ?></p>
