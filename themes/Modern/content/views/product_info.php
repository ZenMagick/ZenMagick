<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2012 zenmagick.org
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
 */
?>

<?php $crumbtrail->addCategoryPath($request->getCategoryPathArray())->addManufacturer($request->getManufacturerId())->addProduct($currentProduct->getId()) ?>
<?php $manufacturer = $currentProduct->getManufacturer() ?>
<?php echo $form->addProduct($currentProduct->getId()) ?>
	<div id="productTopInfoBox">
	  <?php $imageInfo = $currentProduct->getImageInfo() ?>
	  <div id="productMainImage" class="back">
	      <?php if ($imageInfo->hasLargeImage()) { ?>
	          <a href="<?php echo $request->absoluteUrl($imageInfo->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><?php echo $html->image($imageInfo, ZMProducts::IMAGE_MEDIUM) ?></a>
	      <?php } else { ?>
	          <?php echo $html->image($imageInfo, ZMProducts::IMAGE_MEDIUM) ?>
	      <?php } ?>
	  </div>

	  <div id="productPrices" class="forward">
		<h1 id="productName"><?php echo $html->encode(null != $manufacturer ? $manufacturer->getName() : '') ?> <?php echo $html->encode($currentProduct->getName()) ?></h1>
		 <?php if (null != $manufacturer) { ?>
		 	<div id="productDescriptionDetail" class="biggerText">
		 		<ul>
		 			<li><?php _vzm("Model") ?>: <?php echo $html->encode($currentProduct->getModel()) ?></li>
	        		<li><?php _vzm("Producer") ?>: <?php echo $html->encode($manufacturer->getName()); ?></li>
	        	</ul>
	        </div>
	      <?php } ?>
			<div class="itemNormalPrice"><?php _vzm("Price") ?>: <?php echo $macro->productPrice($currentProduct) ?></div>

			<div id="cartInfo">
				<div id="cartAdd">
          <?php $minMsg = ""; if (1 < $currentProduct->getMinOrderQty()) { $minMsg = _vzm(" (Order minimum: %s)", $currentProduct->getMinOrderQty()); } ?>
	      			<label for="cart_quantity"><?php _vzm("Quantity") ?><?php echo $minMsg; ?></label>
	      			<input type="text" id="cart_quantity" name="cart_quantity" value="1" maxlength="6" size="4" />
              <input type="image" value="<?php _vzm("Add to cart") ?>" src="<?php echo $this->asUrl('images/button_in_cart.gif') ?>" />
				</div>

				<div id="tellAFriendInfo">
					<ul>
						<?php if ($currentProduct->getTypeSetting('tell_a_friend')) { ?>
			            	<li id="tellAFriend"><a class="btn" href="<?php echo $net->url('tell_a_friend', "products_id=".$currentProduct->getId()) ?>"><?php _vzm("Tell a friend") ?></a></li>
			          	<?php } ?>
						<?php if ($currentProduct->getTypeSetting('reviews')) { ?>
						  	<li id="writeReview"><a class="btn" href="<?php echo $net->url('product_reviews_write', "products_id=".$currentProduct->getId()) ?>"><?php _vzm("Write a Review") ?></a></li>
						<?php } ?>
						<?php if ($currentProduct->hasReviews()) { ?>
							<li id="readReview"><a class="btn" href="<?php echo $net->url('product_reviews', "products_id=".$currentProduct->getId()) ?>"><?php _vzm("Read Reviews") ?></a></li>
						<?php } ?>

					</ul>
				</div>
			</div>
	  </div>
	  <div class="clearBoth"></div>
  </div>

  <div id="productDescription" class="biggerText"><?php echo $currentProduct->getDescription() ?></div>

  <?php $attributes = $macro->productAttributes($currentProduct); ?>
  <?php foreach ($attributes as $attribute) { ?>
      <fieldset>
          <legend><?php echo $html->encode($attribute['name']) ?></legend>
          <?php foreach ($attribute['html'] as $option) { ?>
            <p><?php echo $option ?></p>
          <?php } ?>
      </fieldset>
  <?php } ?>

  <?php $addImgList = $currentProduct->getAdditionalImages(); ?>
  <?php if (0 < count($addImgList)) { ?>
      <fieldset>
          <legend><?php _vzm("Additional Images") ?></legend>
          <?php foreach ($addImgList as $addImg) { ?>
              <?php if ($addImg->hasLargeImage()) { ?>
                  <a href="<?php echo $request->absoluteUrl($addImg->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><img src="<?php echo $request->absoluteUrl($addImg->getDefaultImage()) ?>" alt="" title="" /></a>
              <?php } else { ?>
                  <img src="<?php echo $request->absoluteUrl($addImg->getDefaultImage()) ?>" alt="" title="" />
              <?php } ?>
          <?php } ?>
      </fieldset>
  <?php } ?>

</form>
