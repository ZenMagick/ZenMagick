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

<h2>About ZenMagick</h2>
<p>Running on top of a standard <a href="http://www.zen-cart.com">zen-cart</a> installation,
  <a href="http://www.zenmagick.org">ZenMagick</a> is a replacement of the zen-cart templating system. Also included is an
  object oriented <a href="http://wiki.zenmagick.org/index.php/ZenMagick_API">API</a> to access all storefront relevant database
  data in a structured way.</p>
 
<h2>demo features</h2>
<p>In addition to the ZenMagick default theme, the following things are demo'ed here:</p>
<dl>
  <dt>Ajax</dt>
  <dd>
    <ul>
      <li>The <a href="<?php $net->url('ajax_demo') ?>">Ajax demo page</a> shows a few Ajax things possible with ZenMagick.</li>
      <li>Drag/Drop Ajax cart demo in <a href="<?php $net->url(ZM_FILENAME_CATEGORY, 'cPath=22') ?>">category list pages</a> 
        (drag the product image onto the shopping cart on ther right...)</li>
    </ul>
  </dd>

  <dt>Page specific CSS</dt>
  <dd>ZenMagick easily allows custom CSS per page; for example, this page - the homepage - is modified by custom CSS 
      (main header text in <span style="color:red;">red</span>)</dd>

  <dt>Coding examples</dt>
  <dd>
    The demo theme includes some custom classes (in the <code>extra</code> folder), that change the default ZenMagick behaviour or extend it;
    for example:
    <ul>
      <li>Additional product filter (alpha and price-range filter [experimental])</li>
      <li>Custom default controller that modifies the crumbtrail of all affected pages (for example,
          the <a href="<?php $net->url(FILENAME_SITE_MAP) ?>">sitemap</a>)</li>
    </ul>
  </dd>

  <dt>Theme switching</dt>
  <dd>
    This can be done either by:
    <ul>
      <li>Request based Theme switching  - the <a href="<?php $net->url(FILENAME_CONTACT_US) ?>">contact us page</a> will always use the default theme</li>
      <li>User theme switching; the new <code>zm_theme_switch</code> plugin allows users to switch themes (per session); see the theme switcher links at
          the top of the page</li>
    </ul>
  </dd>

  <dt>Field specific error messages</dt>
  <dd>Try an invalid email or blank password (using the demo theme!) to see field specific error messages being displayed [needs JavaScript disabled]</dd>

  <dt><a href="http://www.huddletogether.com/projects/lightbox2/">Lightbox JS</a></dt>
  <dd>Large image display using <a href="http://www.huddletogether.com/projects/lightbox2/">Lightbox JS</a></dd>

  <dt>Custom sideboxes</dt>
  <dd>A social bookmarking sidebox that lets you bookmark any page (based on the 
      <a href="http://www.zen-cart.com/index.php?main_page=product_contrib_info&amp;cPath=40_60&amp;products_id=315">Social Bookmarking</a> mod)</dd>
</dl>
