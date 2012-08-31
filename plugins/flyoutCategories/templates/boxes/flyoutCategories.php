<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 */
if (isset($flyoutCategories) && class_exists('ZenMagick\plugins\flyoutCategories\FlyoutCategoriesGenerator')) { ?>
<?php $resources->cssFile('css/categories_menu.css'); ?>
<div class="box flyoutCategories" style="overflow:visible;"> <!-- re-enable overflow as disabled in default theme on .box -->
    <div id="nav-cat">
    <?php
        $generator = new \ZenMagick\plugins\flyoutCategories\FlyoutCategoriesGenerator($request);
        echo $generator->buildTree(true);
    ?>
    <?php
        /*** this is not really supported by ZenMagick but will work for now ***/
        $content = '';
        if (SHOW_CATEGORIES_BOX_SPECIALS == 'true' || SHOW_CATEGORIES_BOX_PRODUCTS_ALL == 'true') {
          $content .= '';  // insert a blank line/box in the menu
          if (SHOW_CATEGORIES_BOX_SPECIALS == 'true') {
            $content .= '<ul class="level1"><li><a href="' . $net->url('specials') . '">' . _zm('Specials...') . '</a></li></ul>';
          }
          if (SHOW_CATEGORIES_BOX_PRODUCTS_NEW == 'true') {
            $content .= '<ul class="level1"><li><a href="' . $net->url('products_new') . '">' . _zm('New Products...') . '</a></li></ul>';
          }
          if (SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS == 'true') {
              $products = $this->container->get('productService')->getFeaturedProducts(0, 1, false, $session->getLanguageId());
            if (0 < count($products)) {
              $content .= '<ul class="level1"><li><a href="' . $net->url('featured_products') . '">' . _zm('Featured...') . '</a></li></ul>';
            }
          }
          if (SHOW_CATEGORIES_BOX_PRODUCTS_ALL == 'true') {
            $content .= '<ul class="level1"><li><a href="' . $net->url('products_all') . '">' . _zm('All Products...') . '</a></li></ul>';
          }
        }

        echo $content;
        // May want to add ............onfocus="this.blur()"...... to each A HREF to get rid of the dotted-box around links when they're clicked.
        // just parse the $content string and insert it into each A HREF tag
    ?>
    </div>
</div>
<?php } ?>
