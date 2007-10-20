<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>

<?php $manufacturer = $zm_product->getManufacturer() ?>
<h2><?php echo null != $manufacturer ? $manufacturer->getName() : '' ?> <?php echo $zm_product->getName(); ?></h2>

<?php zm_add_product_form($zm_product->getId()) ?>
  <?php $imageInfo = $zm_product->getImageInfo() ?>
  <div>
      <?php if ($imageInfo->hasLargeImage()) { ?>
          <a href="<?php zm_absolute_href($imageInfo->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><?php zm_image($imageInfo, PRODUCT_IMAGE_MEDIUM) ?></a>
      <?php } else { ?>
          <?php zm_image($imageInfo, PRODUCT_IMAGE_MEDIUM) ?>
      <?php } ?>
      <div id="desc"><?php echo $zm_product->getDescription() ?></div>
      <?php if (null != $manufacturer) { ?>
        <?php zm_l10n("Producer") ?>: <?php zm_htmlencode($manufacturer->getName()); ?><br />
      <?php } ?>
      <p id="price"><?php zm_htmlencode($zm_product->getModel()) ?>: <?php zm_fmt_price($zm_product) ?></p>
  </div>

  <h3><?php $offers =  $product->getOffers(); echo "special: ".$offers->getSpecialPrice() . ", sale: ".$offers->getSalePrice().", base: ".$offers->getBasePrice().", calculated: ".$offers->getCalculatedPrice() ?></h3>
  <?php $attributes = zm_build_attribute_elements($zm_product); ?>
  <?php foreach ($attributes as $attribute) { ?>
      <fieldset>
          <legend><?php echo $attribute['name'] ?></legend>
          <?php foreach ($attribute['html'] as $option) { ?>
            <p><?php echo $option ?></p>
          <?php } ?>
      </fieldset>
  <?php } ?>

  <?php $features = $zm_product->getFeatures(); ?>
  <?php if (0 < count($features)) { ?>
      <fieldset>
          <legend><?php zm_l10n("Features") ?></legend>
          <?php foreach ($features as $feature) { ?>
          <?php echo $feature->getName() ?>: <?php zm_list_values($feature->getValues()) ?> <?php zm_htmlencode($feature->getDescription()) ?><br />
          <?php } ?>
      </fieldset>
  <?php } ?>

  <fieldset>
      <legend><?php zm_l10n("Shopping Options") ?></legend>
      <?php $minMsg = ""; if (1 < $zm_product->getMinOrderQty()) { $minMsg = zm_l10n_get(" (Order minimum: %s)", $zm_product->getMinOrderQty()); } ?>
      <label for="cart_quantity"><?php zm_l10n("Quantity") ?><?php echo $minMsg; ?></label>
      <input type="text" id="cart_quantity" name="cart_quantity" value="1" maxlength="6" size="4" />
      <input type="submit" class="btn" value="<?php zm_l10n("Add to cart") ?>" />
  </fieldset>

  <?php $addImgList = $zm_product->getAdditionalImages(); ?>
  <?php if (0 < count($addImgList)) { ?>
      <fieldset>
          <legend><?php zm_l10n("Additional Images") ?></legend>
          <?php foreach ($addImgList as $addImg) { ?>
              <?php if ($addImg->hasLargeImage()) { ?>
                  <a href="<?php zm_absolute_href($addImg->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><img src="<?php zm_absolute_href($addImg->getDefaultImage()) ?>" alt="" title="" /></a>
              <?php } else { ?>
                  <img src="<?php zm_absolute_href($addImg->getDefaultImage()) ?>" alt="" title="" />
              <?php } ?>
          <?php } ?>
      </fieldset>
  <?php } ?>
  <?php if ($zm_product->hasReviews() || $zm_product->getTypeSetting('reviews') || $zm_product->getTypeSetting('tell_a_friend')) { ?>
      <fieldset>
          <legend><?php zm_l10n("Other Options") ?></legend>
          <?php if ($zm_product->hasReviews()) { ?>
              <a class="btn" href="<?php zm_href(FILENAME_PRODUCT_REVIEWS_INFO, "products_id=".$zm_product->getId()) ?>"><?php zm_l10n("Read Reviews") ?></a>
          <?php } ?>
          <?php if ($zm_product->getTypeSetting('reviews')) { ?>
              <a class="btn" href="<?php zm_href(FILENAME_PRODUCT_REVIEWS_WRITE, null) ?>"><?php zm_l10n("Write a Review") ?></a>
          <?php } ?>
          <?php if ($zm_product->getTypeSetting('tell_a_friend')) { ?>
              <a class="btn" href="<?php zm_href(FILENAME_TELL_A_FRIEND, "products_id=".$zm_product->getId()) ?>"><?php zm_l10n("Tell a friend about this product") ?></a>
          <?php } ?>
      </fieldset>
  <?php } ?>
</form>
