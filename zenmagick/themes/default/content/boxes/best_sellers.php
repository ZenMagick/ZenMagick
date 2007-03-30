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

<?php $products = $zm_products->getBestSellers($zm_request->getCategoryId()); ?>
<?php if (0 < count($products)) { ?>
    <h3><?php zm_l10n("Best Sellers") ?></h3>
    <div id="sb_bestsellers" class="box">
        <ol>
        <?php foreach ($products as $product) { ?>
            <li><a href="<?php zm_product_href($product->getId()) ?>"><?php zm_htmlencode(zm_more($product->getName(), 28, false)) ?></a></li>
        <?php } ?>
        </ol>
    </div>
<?php } ?>
