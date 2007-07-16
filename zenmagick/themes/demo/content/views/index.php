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

<h1>Demo Theme Homepage</h1>
<p>This demo theme illustrates some of the many ZenMagick features. In particular there are:</p>
<ul>
  <li><a href="<?php zm_href('ajax_demo') ?>">Ajax demo page</a> - Simple code that uses and extends the ZenMagick Ajax controller</li>
  <li>Custom CSS per page; for example, this page has some custom CSS that is located under <code>content/css</code></li>
  <li>The <code>extra/controller</code> folder contains a few custom controller that change the default ZenMagick behaviour or extend it</li>
  <li>Custom alpha and price-range filter</li>
  <li>Custom colour schema using custom site wide CSS (theme.css) that builds on the default CSS (this is a feature of default_layout.php)</li>
</ul>

<?php $zm_theme->staticPageContent("main_page") ?>
<?php $featured = $zm_products->getFeaturedProducts(0, 4); ?>
<h3>Featured Products</h3>
<div id="featured">
  <?php foreach ($featured as $product) { ?>
    <div>
      <p><?php zm_product_image_link($product) ?></p>
      <p><a href="<?php zm_product_href($product->getId()) ?>"><?php echo $product->getName() ?></a></p>
      <?php $offers = $product->getOffers(); ?>
      <p><?php zm_format_currency($offers->getCalculatedPrice()) ?></p>
    </div>
  <?php } ?>
</div>
