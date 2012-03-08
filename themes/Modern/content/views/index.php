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

<div class="bgContent">
	<div id="indexDefine">
	<?php echo $utils->staticPageContent("main_page") ?>
	</div>
</div>

<?php $colInRow = 4;//Number column in a row ?>

<?php $whats_new = $container->get('productService')->getNewProducts(null, 4, false, $session->getLanguageId()); ?>
<div class="bgContent">
	<h2 class="index">New Products</h2>
	<div id="whatsNew">
	  <?php
	  	$count_i = count($whats_new);
	  	if($count_i < $colInRow){
	  		$precent = (100/$count_i);
	  		$colInRow = $count_i;
	  	}else{
	  		$precent = (100/$colInRow);
	  	}
	  	$i = 1;
	  	foreach ($whats_new as $product) {
	  ?>
	    <div class="centerBoxContentsProducts back" style="width: <?php echo $precent;?>%">
	      <div class="itemImage"><?php echo $html->productImageLink($product, '', '', 'small') ?></div>
	      <div class="itemTitle"><a href="<?php echo $net->product($product->getId()) ?>"><?php echo $html->encode($product->getName()) ?></a></div>
	      <?php $offers = $product->getOffers(); ?>
	      <div class="itemPrice"><?php echo $utils->formatMoney($offers->getCalculatedPrice()) ?></div>
        <div class="itemCartAdd">
          <?php echo $form->addProduct($product->getId()) ?>
	      			<input type="hidden" name="cart_quantity" value="1" />
              <input type="image" value="<?php _vzm("Add to cart") ?>" src="<?php echo $this->asUrl('images/button_buy_now.gif') ?>" />
          </form>
        </div>
	    </div>
	   <?php
	   	if($colInRow == $i){ $i = 0; ?>
	    <div class="clearBoth"></div>
	   <?php } $i++; ?>

	  <?php } ?>
	</div>
</div>

<?php $featured = $container->get('productService')->getFeaturedProducts(null, 4, false, $session->getLanguageId()); ?>
<div class="bgContent">
	<h2 class="index">Featured Products</h2>
	<div id="featured">
	  <?php
	  	$count_i = count($featured);
	  	if($count_i < $colInRow){
	  		$precent = (100/$count_i);
	  		$colInRow = $count_i;
	  	}else{
	  		$precent = (100/$colInRow);
	  	}
	  	$i = 1;
	  	foreach ($featured as $product) {
	  ?>
	    <div class="centerBoxContentsProducts back" style="width: <?php echo $precent;?>%">
	      <div class="itemImage"><?php echo $html->productImageLink($product, '', '', 'small') ?></div>
	      <div class="itemTitle"><a href="<?php echo $net->product($product->getId()) ?>"><?php echo $html->encode($product->getName()) ?></a></div>
	      <?php $offers = $product->getOffers(); ?>
	      <div class="itemPrice"><?php echo $utils->formatMoney($offers->getCalculatedPrice()) ?></div>
        <div class="itemCartAdd">
          <?php echo $form->addProduct($product->getId()) ?>
	      			<input type="hidden" name="cart_quantity" value="1" />
              <input type="image" value="<?php _vzm("Add to cart") ?>" src="<?php echo $this->asUrl('images/button_buy_now.gif') ?>" />
          </form>
        </div>
	    </div>
	   <?php
	   	if($colInRow == $i){ $i = 0; ?>
	    <div class="clearBoth"></div>
	   <?php } $i++; ?>

	  <?php } ?>
	</div>
</div>
