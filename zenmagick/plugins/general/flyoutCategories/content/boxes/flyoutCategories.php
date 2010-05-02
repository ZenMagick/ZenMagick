<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
<link rel="stylesheet" type="text/css" href="<?php echo $this->asUrl('stylesheet_categories_menu.css') ?>">
<?php /* TODO: $utils->cssFile('stylesheet_categories_menu.css'); */ ?>
<div class="box flyoutCategories" style="overflow:visible;"> <!-- re-enable overflow as disabled in default theme on .box -->
    <div id="nav-cat">
    <?php
        $generator = ZMLoader::make('FlyoutCategoriesGenerator');
        echo $generator->buildTree(true);
    ?>
    <?php
        /*** this is not really supported by ZenMagick but will work for now ***/
        $content = '';
        if (SHOW_CATEGORIES_BOX_SPECIALS == 'true' || SHOW_CATEGORIES_BOX_PRODUCTS_ALL == 'true') {
          $content .= '';  // insert a blank line/box in the menu
          if (SHOW_CATEGORIES_BOX_SPECIALS == 'true') {
            $content .= '<ul class="level1"><li><a href="' . $net->url(FILENAME_SPECIALS) . '">' . zm_l10n_get('Specials...') . '</a></li></ul>';
          }
          if (SHOW_CATEGORIES_BOX_PRODUCTS_NEW == 'true') {
            $content .= '<ul class="level1"><li><a href="' . $net->url(FILENAME_PRODUCTS_NEW) . '">' . zm_l10n_get('New Products...') . '</a></li></ul>';
          }
          /*
          if (SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS == 'true') {
            $show_this = $db->Execute("select products_id from " . TABLE_FEATURED . " where status= 1 limit 1");
            if ($show_this->RecordCount() > 0) {
              $content .= '<ul class="level1"><li><a href="' . $net->url(FILENAME_FEATURED_PRODUCTS) . '">' . zm_l10n_get('Featured...') . '</a></li></ul>';
            }
          }
          */
          if (SHOW_CATEGORIES_BOX_PRODUCTS_ALL == 'true') {
            $content .= '<ul class="level1"><li><a href="' . $net->url(FILENAME_PRODUCTS_ALL) . '">' . zm_l10n_get('All Products...') . '</a></li></ul>';
          }
        }
        // May want to add ............onfocus="this.blur()"...... to each A HREF to get rid of the dotted-box around links when they're clicked.
        // just parse the $content string and insert it into each A HREF tag
    ?>
    </div>
</div>
