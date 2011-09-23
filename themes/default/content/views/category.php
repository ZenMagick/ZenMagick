<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
<h2><?php echo $html->encode($currentCategory->getName()) ?></h2>

<?php if ($currentCategory->hasChildren()) { ?>
  <div class="subcats">
      <h3><?php _vzm("Available Sub-categories") ?></h3>
      <?php foreach ($currentCategory->getChildren() as $category) {
          $encName = $html->encode($category->getName());
          $catImage = $category->getImageInfo();
          $linkText = null == $catImage ? $encName : '<img src="'.$catImage->getDefaultImage().'" alt="'.$encName.'" title="'.$encName.'">';
          ?>
          <a href="<?php echo $net->url('category', $category->getPath()) ?>"><?php echo $linkText ?></a>
      <?php } ?>
  </div>
<?php } ?>

<?php $featured = $container->get('productService')->getFeaturedProducts($currentCategory->getId(), 4, true, $session->getLanguageId()); ?>

<?php if (0 < count($featured)) { ?>
    <h3>Featured Products</h3>
    <div id="featured">
      <?php foreach ($featured as $product) { ?>
        <div>
          <p><?php echo $html->productImageLink($product) ?></p>
          <p><a href="<?php echo $net->product($product->getId()) ?>"><?php echo $html->encode($product->getName()) ?></a></p>
          <?php $offers = $product->getOffers(); ?>
          <p><?php echo $utils->formatMoney($offers->getCalculatedPrice()) ?></p>
        </div>
      <?php } ?>
    </div>
<?php } ?>
