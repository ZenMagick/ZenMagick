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
 */
?>

<?php if (isset($recentlyViewedProducts) && 0 < count($recentlyViewedProducts)) { ?>
    <h3><?php _vzm('Recently Viewed') ?></h3>
    <div id="sb_recently_viewed" class="box">
        <ol>
        <?php foreach ($container->get('productService')->getProductsForIds($recentlyViewedProducts, true, $session->getLanguageId()) as $product) { ?>
            <li><a href="<?php echo $net->product($product->getId()) ?>"><?php echo $html->encode($html->more($product->getName(), 28)) ?></a></li>
        <?php } ?>
        </ol>
    </div>
<?php } ?>
