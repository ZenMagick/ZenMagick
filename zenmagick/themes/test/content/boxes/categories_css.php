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

<h3><?php zm_l10n("Categories") ?></h3>
<div id="sb_categories_css" class="box">
<?php

  $zen_CategoriesUL = new zen_categories_ul_generator($request);
  $menulist = $zen_CategoriesUL->buildTree(true);

  $content = '';

  // Load CSS file if this sidebox is enabled
  $utils->cssFile('categories_css.css');

  // Load containing UL and content
  $content .= '<ul class="bullet-menu" id="siteMenu">';
  // get the menu tree (see the modules/sideboxes/YOURTEMPLATE/categories_css.php), strip off containing UL
  $content .= preg_replace('%^\s*<ul>(.+)</ul>\s*$%sim', '\1', $menulist);
  if (SHOW_CATEGORIES_BOX_SPECIALS == 'true' or SHOW_CATEGORIES_BOX_PRODUCTS_NEW == 'true') {
    //$content .= '<hr />';  // insert a blank line/box in the menu
    if (SHOW_CATEGORIES_BOX_SPECIALS == 'true') {
      $content .= '  <li><a href="' . zen_href_link(FILENAME_SPECIALS) . '">' . CATEGORIES_BOX_HEADING_SPECIALS . '</a></li>'."\n";
    }
    if (SHOW_CATEGORIES_BOX_PRODUCTS_NEW == 'true') {
      $content .= '  <li><a href="' . zen_href_link(FILENAME_PRODUCTS_NEW) . '">' . CATEGORIES_BOX_HEADING_WHATS_NEW . '</a></li>'."\n";
    }
    if (SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS == 'true') {
      $featured = ZMProducts::instance()->getFeaturedProducts(null, 1, false, $session->getLanguageId());
      if (0 < count($featured)) {
        $content .= '  <li><a href="' . zen_href_link(FILENAME_FEATURED_PRODUCTS) . '">' . CATEGORIES_BOX_HEADING_FEATURED_PRODUCTS . '</a></li>'."\n";
      }
    }
    if (SHOW_CATEGORIES_BOX_PRODUCTS_ALL == 'true') {
      $content .= '  <li><a href="' . zen_href_link(FILENAME_PRODUCTS_ALL) . '">' . CATEGORIES_BOX_HEADING_PRODUCTS_ALL . '</a></li>'."\n";
    }
  }
  $content .= "</ul>\n";

  // Load JS file if this sidebox is enabled
  $content .= '<script type="text/javascript" src="'. $zm_theme->themeURL("categories_css.js", false) .'"></script>';
  // Preload menu images when page loads (won't affect IE, which never caches CSS images)
  $m = $zm_theme->themeURL("images/menu/", false);
  $content .= '<script type="text/javascript">addDOMEvent(window,"load",function() {preloadImages("'.$m.'branch.gif","'.$m.'leaf-end-on.gif","'.$m.'leaf-end.gif","'.$m.'leaf-on.gif","'.$m.'leaf.gif","'.$m.'node-end-on.gif","'.$m.'node-end.gif","'.$m.'node-on.gif","'.$m.'node-open-end-on.gif","'.$m.'node-open-end.gif","'.$m.'node-open-on.gif","'.$m.'node-open.gif","'.$m.'node.gif")}, false);</script>'."\n";

  echo $content;
?>
</div>
