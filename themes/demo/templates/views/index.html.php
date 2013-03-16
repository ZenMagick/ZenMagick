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
<h1>Welcome to the ZenMagick demo store</h1>

<p>Running on top of a standard <a href="http://www.zen-cart.com">zen-cart</a> installation,
  <a href="http://www.zenmagick.org">ZenMagick</a> provides access to most of zen-cart's data via an object oriented
    <a href="http://wiki.zenmagick.org/index.php/ZenMagick_API">API</a>.</p>
  <p>Also included is a replacement of the zen-cart storefront templating system
    (<a href="http://en.wikipedia.org/wiki/Model-view-controller">MVC</a>),
    build on top of that API.</p>

<p><strong>In addition to the default ZenMagick features, the following non default options and plugins are installed:</strong></p>

<h2>Ajax</h2>
<ul>
  <li>The <a href="<?php echo $net->generate('ajax_demo') ?>">Ajax demo page</a> shows a few Ajax things possible with ZenMagick.</li>
  <li>The <a href="<?php echo $net->generate('ajax_demo2') ?>">New Shipping Estimator demo page</a> shows an Ajax based estimator using new shipping code.</li>
  <li>Drag/Drop Ajax cart demo in <a href="<?php echo $net->generate('category', array('cPath' => 22)) ?>">category list pages</a>
    (drag the product image onto the shopping cart on ther right...)</li>
</ul>

<h2>Plugins</h2>
<ul>
  <li><a href="http://wiki.zenmagick.org/index.php/Plugins::themeSwitcher">Theme switching</a> - see the theme options at the top of this page</li>
  <li><a href="http://wiki.zenmagick.org/index.php/Plugins::pageCache">Page caching</a> - Check the HTML source - if the content came from the cache, there should be something like this as the last line: <em>&lt;!-- pageCache stats: page: 0.3247 sec.; lastModified: 1228186954 --&gt;</em></li>
  <li><a href="http://wiki.zenmagick.org/index.php/Plugins::googleAnalytics">Google analytics</a> - automatically injected into the returned HTML</li>
  <li><a href="http://wiki.zenmagick.org/index.php/Plugins::googleStoreLocator">Google Maps Store Locator</a> - there is a <a href="<?php echo $net->generate('store_locator') ?>">demo</a> right here!</li>
  <li><a href="http://wiki.zenmagick.org/index.php/Plugins::zmpage_stats">Page Stats</a> - at the bottom of the page </li>
  <li><a href="http://wiki.zenmagick.org/index.php/Plugins::unitTests">Unit Tests</a> - check out this <a href="<?php echo $net->generate('tests') ?>">demo version</a> </li>
</ul>


<h2>Other</h2>
<ul>
  <li>RSS sidebox</li>
  <li>[Experimental] product filter (alpha and price range)</li>
  <li>A customized default controller to illustrate extending controller code; in this example, the crubtrail is modified. This affects
    all pages handled by the default controller, for example the <a href="<?php echo $net->generate('site_map') ?>">sitemap</a>)</li>
  <li>Programmatical theme switching - the <a href="<?php echo $net->generate('contact_us') ?>">contact us page</a> will always use the default theme</li>
  <li>Field specific error messages; Try an invalid email or blank password (using the demo theme!) to see field specific error messages
     being displayed [needs JavaScript disabled]</li>
  <li>Check out the actual used template files (
     <a href="<?php echo $net->generate('source_view', array('template_name' => $view->getLayout())) ?>">layout</a>
      and
     <a href="<?php echo $net->generate('source_view', array('view_name' => $request->getRequestId())) ?>">view</a>
    ) on this site! (there are links in the footer on all other pages)</li>
</ul>
