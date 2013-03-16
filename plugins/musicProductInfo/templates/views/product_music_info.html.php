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
?>
<?php $view->extend('StorefrontBundle::default_layout.html.php'); ?>
<?php
use ZenMagick\StoreBundle\Services\Products;
?>

<?php $crumbtrail->addCategoryPath()->addManufacturer()->addProduct($currentProduct->getId()) ?>

<?php $manufacturer = $currentProduct->getManufacturer() ?>
<h2><?php echo $html->encode(null != $manufacturer ? $manufacturer->getName() : '') ?> <?php echo $html->encode($currentProduct->getName()) ?></h2>

<?php echo $form->addProduct($currentProduct->getId()) ?>
  <?php $imageInfo = $currentProduct->getImageInfo() ?>
  <div>
      <?php if ($imageInfo->hasLargeImage()) { ?>
          <a href="<?php echo $net->absoluteUrl($imageInfo->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><?php echo $html->image($imageInfo, Products::IMAGE_MEDIUM) ?></a>
      <?php } else { ?>
          <?php echo $html->image($imageInfo, Products::IMAGE_MEDIUM) ?>
      <?php } ?>
      <div id="desc"><?php echo $currentProduct->getDescription() ?></div>
      <?php if (null != $manufacturer) { ?>
        <?php _vzm("Producer") ?>: <?php echo $html->encode($manufacturer->getName()); ?><br />
      <?php } ?>
      <p id="artist">
          <?php if ($artist->hasUrl()) { ?>
              <?php _vzm('Artist:') ?> <a href="<?php echo $net->trackLink('url', $artist->getUrl()) ?>"><?php echo $artist->getName() ?></a>
          <?php } else { ?>
              <?php echo sprintf(_zm('Artist: %s'), $artist->getName()) ?>
          <?php } ?>
      </p>
      <p id="genre"><?php echo sprintf(_zm('Genre: %s'), $artist->getGenre()) ?></p>
      <p id="price"><?php echo $html->encode($currentProduct->getModel()) ?>: <?php echo $macro->productPrice($currentProduct) ?></p>
  </div>

  <fieldset>
      <legend><?php _vzm("Additional Info") ?></legend>
      <p id="artist">
          <?php if ($artist->hasUrl()) { ?>
              <?php _vzm('Artist:') ?> <a href="<?php echo $net->trackLink('url', $artist->getUrl()) ?>"><?php echo $artist->getName() ?></a>
          <?php } else { ?>
              <?php echo sprintf(_zm('Artist: %s'), $artist->getName()) ?>
          <?php } ?>
      </p>
      <p id="genre"><?php echo sprintf(_zm('Genre: %s'), $artist->getGenre()) ?></p>
  </fieldset>

  <?php if (0 < count($collections)) { ?>
      <fieldset>
          <legend><?php _vzm("Media Collections") ?></legend>
          <?php foreach ($collections as $collection) { ?>
              <div class="mcol">
                  <h4><?php echo $collection->getName() ?></h4>
                  <ul>
                      <?php foreach ($collection->getItems() as $media) { $type = $media->getType(); ?>
                      <li><a href="<?php echo $net->absoluteUrl($musicProductInfo->mediaUrl($mediaItem->getFilename())) ?>"><?php echo $media->getFilename() ?></a> (<?php echo $type->getName() ?>)</li>
                      <?php } ?>
                  </ul>
              </div>
          <?php } ?>
      </fieldset>
  <?php } ?>

  <?php $productAttributes = $macro->productAttributes($currentProduct); ?>
  <?php foreach ($productAttributes as $details) { ?>
      <fieldset>
          <legend><?php echo $html->encode($details['name']) ?></legend>
          <?php foreach ($details['html'] as $option) { ?>
            <p><?php echo $option ?></p>
          <?php } ?>
      </fieldset>
  <?php } ?>

  <fieldset>
      <legend><?php _vzm("Shopping Options") ?></legend>
      <?php $minMsg = ""; if (1 < $currentProduct->getMinOrderQty()) { $minMsg = sprintf(_zm(" (Order minimum: %s)"), $currentProduct->getMinOrderQty()); } ?>
      <label for="cart_quantity"><?php _vzm("Quantity") ?><?php echo $minMsg; ?></label>
      <input type="text" id="cart_quantity" name="cart_quantity" value="1" maxlength="6" size="4" />
      <input type="submit" class="btn" value="<?php _vzm("Add to cart") ?>" />
  </fieldset>

  <?php $addImgList = $currentProduct->getAdditionalImages(); ?>
  <?php if (0 < count($addImgList)) { ?>
      <fieldset>
          <legend><?php _vzm("Additional Images") ?></legend>
          <?php foreach ($addImgList as $addImg) { ?>
              <?php if ($addImg->hasLargeImage()) { ?>
                  <a href="<?php echo $net->absoluteUrl($addImg->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><img src="<?php echo $net->absoluteUrl($addImg->getDefaultImage()) ?>" alt="" title="" /></a>
              <?php } else { ?>
                  <img src="<?php echo $net->absoluteUrl($addImg->getDefaultImage()) ?>" alt="" title="" />
              <?php } ?>
          <?php } ?>
      </fieldset>
  <?php } ?>
  <?php if ($currentProduct->hasReviews() || $currentProduct->getTypeSetting('reviews')) { ?>
      <fieldset>
          <legend><?php _vzm("Other Options") ?></legend>
          <?php if ($currentProduct->hasReviews()) { ?>
              <a class="btn" href="<?php echo $net->generate('product_reviews', array('productId' => $currentProduct->getId())) ?>"><?php _vzm("Read Reviews") ?></a>
          <?php } ?>
          <?php if ($currentProduct->getTypeSetting('reviews')) { ?>
              <a class="btn" href="<?php echo $net->generate('product_reviews_write', array('productId' => $currentProduct->getId())) ?>"><?php _vzm("Write a Review") ?></a>
          <?php } ?>
      </fieldset>
  <?php } ?>
</form>

<?php $productService = $container->get('productService'); ?>
<h3><?php _vzm('People that bought "%s" also bought:', '<em>'.$currentProduct->getName().'</em>') ?></h3>
<div id="similar">
  <?php foreach ($currentProduct->getProductAssociations('similarOrder') as $assoc) { $assocProduct = $productService->getProductForId($assoc->getProductId(), $languageId) ; ?>
    <div>
      <p><?php echo $html->productImageLink($assocProduct) ?></p>
      <p><a href="<?php echo $net->product($assocProduct->getId()) ?>"><?php echo $html->encode($assocProduct->getName()) ?></a></p>
      <?php $offers = $assocProduct->getOffers(); ?>
      <p><?php echo $utils->formatMoney($offers->getCalculatedPrice()) ?></p>
    </div>
  <?php } ?>
</div>
