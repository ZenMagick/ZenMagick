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
  <a href="http://www.zenmagick.org">ZenMagick</a> provides access to most of zen-cart's data via an object oriented
    <a href="http://wiki.zenmagick.org/index.php/ZenMagick_API">API</a>.</p>
  <p>Also included is a replacement of the zen-cart storefront templating system 
    (<a href="http://en.wikipedia.org/wiki/Model-view-controller">MVC</a>),
    build on top of that API.</p>
 
<p><strong>In addition to the default ZenMagick features, the following non default options and plugins are installed:</strong></p>

<h2>Ajax</h2>
<ul>
  <li>The <a href="<?php $net->url('ajax_demo') ?>">Ajax demo page</a> shows a few Ajax things possible with ZenMagick.</li>
  <li>Drag/Drop Ajax cart demo in <a href="<?php $net->url(ZM_FILENAME_CATEGORY, 'cPath=22') ?>">category list pages</a> 
    (drag the product image onto the shopping cart on ther right...)</li>
</ul>

<h2>Plugins</h2>
<ul>
  <li>Theme switching; </li>
  <li>Page caching; </li>
  <li>Google analytics; </li>
  <li>Google Maps Store Locator; </li>
  <li>Page Stats; </li>
  <li>Unit Tests; </li>
</ul>


<h2>Other</h2>
<ul>
  <li>[Experimental] product filter (alpha and price range)</li>
  <li>A customized default controller to illustrate extending controller code; in this example, the crubtrail is modified. This affects
    all pages handled by the default controller, for example the <a href="<?php $net->url(FILENAME_SITE_MAP) ?>">sitemap</a>)</li>
  <li>Programmatical theme switching - the <a href="<?php $net->url(FILENAME_CONTACT_US) ?>">contact us page</a> will always use the default theme</li>
  <li>Field specific error messages; Try an invalid email or blank password (using the demo theme!) to see field specific error messages
     being displayed [needs JavaScript disabled]</li>
</ul>
