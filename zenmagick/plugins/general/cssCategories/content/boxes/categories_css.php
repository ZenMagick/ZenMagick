<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id$
//

  $zen_CategoriesUL = new zen_categories_ul_generator;
  $menulist = $zen_CategoriesUL->buildTree(true);

  $content = '';

  // NOTE: CSS should be in the <head> element, this is not valid HTML
  // Load CSS file if this sidebox is enabled
  $resources->cssFile('categories_css.css');
  //$content .= '<link rel="stylesheet" type="text/css" href="' . $this->asUrl('categories_css.css', false) . '" />'."\n";

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
      $featured = ZMProducts::instance()->getFeaturedProducts(null, 1);
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
  $resources->jsFile('categories_css.js');
  //$content .= '<script type="text/javascript" src="'. $this->asUrl("categories_css.js", false) .'"></script>';
  // Preload menu images when page loads (won't affect IE, which never caches CSS images)
  $m = $this->asUrl("images/menu/", false);
  $content .= '<script type="text/javascript">addDOMEvent(window,"load",function() {preloadImages("'.$m.'branch.gif","'.$m.'leaf-end-on.gif","'.$m.'leaf-end.gif","'.$m.'leaf-on.gif","'.$m.'leaf.gif","'.$m.'node-end-on.gif","'.$m.'node-end.gif","'.$m.'node-on.gif","'.$m.'node-open-end-on.gif","'.$m.'node-open-end.gif","'.$m.'node-open-on.gif","'.$m.'node-open.gif","'.$m.'node.gif")}, false);</script>'."\n";

  echo $content;
?>
