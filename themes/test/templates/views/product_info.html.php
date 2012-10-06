<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 */
use ZenMagick\StoreBundle\Services\Products;
?>

<?php $crumbtrail->addCategoryPath()->addManufacturer()->addProduct($currentProduct->getId()) ?>
<?php echo get_class($container->get('productService')) ?>
<?php $manufacturer = $currentProduct->getManufacturer() ?>
<h2><?php echo $html->encode(null != $manufacturer ? $manufacturer->getName() : '') ?> <?php echo $html->encode($currentProduct->getName()) ?></h2>

<?php echo $form->addProduct($currentProduct->getId()) ?>
  <?php $imageInfo = $currentProduct->getImageInfo() ?>
  <div>
      <?php if ($imageInfo->hasLargeImage()) { ?>
          <a href="<?php echo $net->absoluteUrl($imageInfo->getLargeImage()) ?>" rel="lightbox[gallery]" title="lightbox[gallery]"><?php echo $html->image($imageInfo, Products::IMAGE_MEDIUM) ?></a><br>
          |<a href="<?php echo $net->absoluteUrl($imageInfo->getLargeImage()) ?>" rel="lightbox[gallery]">Click to enlarge (lightbox[gallery]) - FTW!</a>|<br>
          |<?php if (function_exists('hover3_product_image_link')) { hover3_product_image_link($this, $currentProduct, $imageInfo); } ?>|
      <?php } else { ?>
          <?php echo $html->image($imageInfo, Products::IMAGE_MEDIUM) ?>
      <?php } ?>
      <div id="desc"><?php echo $currentProduct->getDescription() ?></div>
      <?php if (null != $manufacturer) { ?>
        <?php _vzm("Producer") ?>: <?php echo $html->encode($manufacturer->getName()); ?><br />
      <?php } ?>
        <?php _vzm("Avilable") ?>: <?php echo $locale->shortDate($currentProduct->getDateAvailable()) ?><br />
      <p id="price"><?php echo $html->encode($currentProduct->getModel()) ?>: <?php echo $macro->productPrice($currentProduct) ?></p>
  </div>

  <?php
      // Name of the attribute to allow multi qty on the product page
      define('MULTI_QUANTITY_NAME', 'Memory');

      $isMultiQty = false;
      $attributes = $currentProduct->getAttributes();
      foreach ($attributes as $attribute) {
          if (MULTI_QUANTITY_NAME == $attribute->getName()) {
              $isMultiQty = true;
              // this is required for the server code to know which attribute to use
              echo '<input type="hidden" name="'.MULTI_QUANTITY_ID.'" value="'.$attribute->getId().'">';

              // qty input fields for each attribute value
              foreach ($attribute->getValues() as $value) {
                  echo $value->getName() . ': ';
                  echo '<input type="text" name="id['.$attribute->getId().']['.$value->getId().']">';
              }
          }
      }

  ?>

  <?php $attributes = $macro->productAttributes($currentProduct); ?>
  <?php foreach ($attributes as $attribute) { /* ADDED: */ if (MULTI_QUANTITY_NAME == $attribute['name']) { continue; } ?>
      <fieldset>
          <legend><?php echo $html->encode($attribute['name']) ?></legend>
          <?php foreach ($attribute['html'] as $option) { ?>
            <p><?php echo $option ?></p>
          <?php } ?>
      </fieldset>
  <?php } ?>

  <fieldset>
      <legend><?php _vzm("Shopping Options") ?></legend>
      <?php $minMsg = ""; if (1 < $currentProduct->getMinOrderQty()) { $minMsg = sprintf(_zm(" (Order minimum: %s)"), $currentProduct->getMinOrderQty()); } ?>
      <?php if (!$isMultiQty) { ?>
          <label for="cart_quantity"><?php _vzm("Quantity") ?><?php echo $minMsg; ?></label>
          <input type="text" id="cart_quantity" name="cart_quantity" value="1" maxlength="6" size="4" />
      <?php } ?>
      <input type="submit" class="btn" value="<?php _vzm("Add to cart") ?>" />
  </fieldset>

  <?php $addImgList = $currentProduct->getAdditionalImages(); ?>
  <?php if (0 < count($addImgList)) { ?>
      <fieldset>
          <legend><?php _vzm("Additional Images") ?></legend>
          <?php foreach ($addImgList as $addImg) { ?>
              <?php if ($addImg->hasLargeImage()) { ?>
                  <a href="<?php echo $net->absoluteUrl($addImg->getLargeImage()) ?>" rel="lightbox[gallery]"><img src="<?php echo $net->absoluteUrl($addImg->getDefaultImage()) ?>" alt="" title="" /></a>
                  <br><?php if (function_exists('hover3_product_image_link')) { hover3_product_image_link($this, $currentProduct, $addImg, false); } ?>
              <?php } else { ?>
                  <img src="<?php echo $net->absoluteUrl($addImg->getDefaultImage()) ?>" alt="" title="" />
              <?php } ?>
          <?php } ?>
      </fieldset>
  <?php } ?>
  <?php if ($currentProduct->hasReviews() || $currentProduct->getTypeSetting('reviews') || $currentProduct->getTypeSetting('tell_a_friend')) { ?>
      <fieldset>
          <legend><?php _vzm("Other Options") ?></legend>
          <?php if ($currentProduct->hasReviews()) { ?>
              <a class="btn" href="<?php echo $net->url('product_reviews', "productId=".$currentProduct->getId()) ?>"><?php _vzm("Read Reviews") ?></a>
          <?php } ?>
          <?php if ($currentProduct->getTypeSetting('reviews')) { ?>
              <a class="btn" href="<?php echo $net->url('product_reviews_write', "productId=".$currentProduct->getId()) ?>"><?php _vzm("Write a Review") ?></a>
          <?php } ?>
          <?php if ($currentProduct->getTypeSetting('tell_a_friend')) { ?>
              <a class="btn" href="<?php echo $net->url('tell_a_friend', "productId=".$currentProduct->getId()) ?>"><?php _vzm("Tell a friend about this product") ?></a>
          <?php } ?>
      </fieldset>
  <?php } ?>
</form>

<?php ?>
<?php if ($this->exists('views/lift-suggestions.php')) { ?>
  <?php echo $this->fetch('views/lift-suggestions.html.php') ?>
<?php } ?>
