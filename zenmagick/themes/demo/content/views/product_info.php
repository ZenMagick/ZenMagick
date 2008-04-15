<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * $Id: product_info.php 215 2007-07-16 05:17:44Z DerManoMann $
 */
?>
<script type="text/javascript" src="<?php $zm_theme->themeURL("lightbox/prototype.js") ?>"></script>
<script type="text/javascript" src="<?php $zm_theme->themeURL("lightbox/scriptaculous.js?load=effects") ?>"></script>
<script type="text/javascript" src="<?php $zm_theme->themeURL("lightbox/lightbox.js") ?>"></script>

<?php $manufacturer = $zm_product->getManufacturer() ?>
<h2><?php echo null != $manufacturer ? $manufacturer->getName() : '' ?> <?php echo $zm_product->getName(); ?></h2>

<?php $_t->form->addProduct($zm_product->getId()) ?>
  <?php $imageInfo = $zm_product->getImageInfo() ?>
  <div>
      <?php if ($imageInfo->hasLargeImage()) { ?>
          <a href="<?php $_t->net->absolute($imageInfo->getLargeImage()) ?>" rel="lightbox" title="<?php $_t->html->encode($zm_product->getName()) ?>"><?php $_t->html->image($imageInfo, PRODUCT_IMAGE_MEDIUM) ?></a>
      <?php } else { ?>
          <?php $_t->html->image($imageInfo, PRODUCT_IMAGE_MEDIUM) ?>
      <?php } ?>
      <div id="desc"><?php $_t->html->encode($zm_product->getDescription()) ?></div>
      <?php if (null != $manufacturer) { ?>
        <?php zm_l10n("Producer") ?>: <?php $_t->html->encode($manufacturer->getName()); ?><br />
      <?php } ?>
      <p id="price"><?php $_t->html->encode($zm_product->getModel()) ?>: <?php zm_fmt_price($zm_product) ?></p>
  </div>

  <?php $attributes = zm_build_attribute_elements($zm_product); ?>
  <?php foreach ($attributes as $attribute) { ?>
      <fieldset>
          <legend><?php echo $attribute['name'] ?></legend>
          <?php foreach ($attribute['html'] as $option) { ?>
            <p><?php echo $option ?></p>
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
                  <a href="<?php $_t->net->absolute($addImg->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><img src="<?php $_t->net->absolute($addImg->getDefaultImage()) ?>" alt="" title="" /></a>
              <?php } else { ?>
                  <img src="<?php $_t->net->absolute($addImg->getDefaultImage()) ?>" alt="" title="" />
              <?php } ?>
          <?php } ?>
      </fieldset>
  <?php } ?>
  <?php if ($zm_product->hasReviews() || $zm_product->getTypeSetting('reviews') || $zm_product->getTypeSetting('tell_a_friend')) { ?>
      <fieldset>
          <legend><?php zm_l10n("Other Options") ?></legend>
          <?php if ($zm_product->hasReviews()) { ?>
              <a class="btn" href="<?php $_t->net->url(FILENAME_PRODUCT_REVIEWS, "products_id=".$zm_product->getId()) ?>"><?php zm_l10n("Read Reviews") ?></a>
          <?php } ?>
          <?php if ($zm_product->getTypeSetting('reviews')) { ?>
              <a class="btn" href="<?php $_t->net->url(FILENAME_PRODUCT_REVIEWS_WRITE, '') ?>"><?php zm_l10n("Write a Review") ?></a>
          <?php } ?>
          <?php if ($zm_product->getTypeSetting('tell_a_friend')) { ?>
              <a class="btn" href="<?php $_t->net->url(FILENAME_TELL_A_FRIEND, "products_id=".$zm_product->getId()) ?>"><?php zm_l10n("Tell a friend about this product") ?></a>
          <?php } ?>
      </fieldset>
  <?php } ?>
</form>
