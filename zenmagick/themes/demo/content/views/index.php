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
  <li><a href="<?php zm_href('ajax_demo') ?>">Ajax demo page</a> - Simple code that uses the ZenMagick Ajax controllers</li>
  <li>Custom CSS per page; for example, this page has some custom CSS that is located under <code>content/css</code></li>
  <li>The <code>extra/controller</code> folder contains a few custom controller that change the default ZenMagick behaviour or extend it</li>
  <li>Custom alpha and price-range filter</li>
  <li>Custom colour schema using custom site wide CSS (theme.css) that builds on the default CSS (this is a feature of default_layout.php)</li>
  <li>Theme switching (implemented for the contact us page)</li>
  <li>Field specific error messages (login page)</li>
  <li><a href="http://www.huddletogether.com/projects/lightbox2/">Lightbox JS</a> integration</li>
  <li>Drag/Drop Ajax cart demo for category listing (drag listing image onto shopping cart sidebox)<br/>
    <strong>Note1:</strong> This demo does not use <code>json.js</code> as this is not compatible with the used <a href="http://interface.eyecon.ro">effects library</a>!<br/>
  </li>
  <li>A social bookmarking sidebox that lets you bookmark any page (based on the <a href="http://www.zen-cart.com/index.php?main_page=product_contrib_info&cPath=40_60&products_id=315">Social Bookmarking</a> mod).</li>
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
