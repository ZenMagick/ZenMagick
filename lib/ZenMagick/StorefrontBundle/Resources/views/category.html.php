<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
<?php $view['slots']->set('crumbtrail', $crumbtrail->addCategoryPath()->addManufacturer()->addProduct()); ?>

<h2><?php echo $view->escape($currentCategory->getName()) ?></h2>

<?php if ($currentCategory->hasChildren()) { ?>
  <div class="subcats">
      <h3><?php _vzm("Available Sub-categories") ?></h3>
      <?php foreach ($currentCategory->getChildren() as $category) {
          $encName = $view->escape($category->getName());
          $catImage = $category->getImageInfo();
          $linkText = null == $catImage ? $encName : '<img src="'.$catImage->getDefaultImage().'" alt="'.$encName.'" title="'.$encName.'">';
          ?>
          <a href="<?php echo $view['router']->generate('category', array('cPath' => implode('_', $category->getPath()))) ?>"><?php echo $linkText ?></a>
      <?php } ?>
  </div>
<?php } ?>

<?php $featured = $view->container->get('productService')->getFeaturedProducts($currentCategory->getId(), 4, true, $view['request']->getLocaleId()); ?>

<?php if (0 < count($featured)) { ?>
    <h3>Featured Products</h3>
    <div id="featured">
      <?php foreach ($featured as $product) { ?>
        <div>
          <p><?php echo $html->productImageLink($product) ?></p>
          <p><a href="<?php echo $net->product($product->getId()) ?>"><?php echo $view->escape($product->getName()) ?></a></p>
          <?php $offers = $product->getOffers(); ?>
          <p><?php echo $utils->formatMoney($offers->getCalculatedPrice()) ?></p>
        </div>
      <?php } ?>
    </div>
<?php } ?>
