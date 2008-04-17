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
 * $Id$
 */
?>

<h1>Welcome to the ZenMagick demo store</h1>
<p>Running on top of a standard <a href="http://www.zen-cart.com">zen-cart</a> installation,
  <a href="http://www.zenmagick.org">ZenMagick</a> offers a replacement for the zen-cart templating system. Also included is an
  object oriented <a href="http://wiki.zenmagick.org/index.php/ZenMagick_API">API</a> to access all storefront relevant database
  data in a structured way.</p>
  
<p>The included demo theme, as seen here, illustrates a few of the many ZenMagick features. In particular there are:</p>
<ul>
  <li>The <a href="<?php $_t->net->url('ajax_demo') ?>">Ajax demo page</a> - Simple code that uses the ZenMagick Ajax controllers</li>
  <li>Custom CSS per page; for example, this page - the homepage - is modified by some custom CSS (main header text in <span style="color:red;">red</span>)</li>
  <li>The <code>extra/controller</code> folder contains a few custom controller that change the default ZenMagick behaviour or extend it; for example:
    <ul>
      <li>Additional product filter (experimental)</li>
      <li>Custom default controller that modifies the crumbtrail of all affected pages (for example, the <a href="<?php $_t->net->url(FILENAME_SITE_MAP) ?>">sitemap</a>)</li>
    </ul>
  </li>
  <li>Custom alpha and price-range filter</li>
  <li>Theme specific CSS that extends the default look&amp;fee (this is a feature of the main layout template)</li>
  <li>Request based Theme switching (implemented for the contact us page)</li>
  <li>Field specific error messages (login page)</li>
  <li><a href="http://www.huddletogether.com/projects/lightbox2/">Lightbox JS</a> integration</li>
  <li>Drag/Drop Ajax cart demo in <a href="<?php $_t->net->url(ZM_FILENAME_CATEGORY, 'cPath=22') ?>">category list pages</a> 
    (drag the product image onto the shopping cart on ther right...)</li>
  <li>A social bookmarking sidebox that lets you bookmark any page (based on the <a href="http://www.zen-cart.com/index.php?main_page=product_contrib_info&cPath=40_60&products_id=315">Social Bookmarking</a> mod).</li>
</ul>

<?php $zm_theme->staticPageContent("main_page") ?>
<?php $featured = ZMProducts::instance()->getFeaturedProducts(0, 4); ?>
<h3>Featured Products</h3>
<div id="featured">
  <?php foreach ($featured as $product) { ?>
    <div>
      <p><?php $_t->html->productImageLink($product) ?></p>
      <p><a href="<?php $_t->net->product($product->getId()) ?>"><?php $_t->html->encode($product->getName()) ?></a></p>
      <?php $offers = $product->getOffers(); ?>
      <p><?php zm_format_currency($offers->getCalculatedPrice()) ?></p>
    </div>
  <?php } ?>
</div>
