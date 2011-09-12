<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
?><?php

if (isset($liftSuggest)) {
    if ('product_info' == $request->getRequestId()) {
        $productId = $request->getProductId();
    } else if ('shopping_cart' == $request->getRequestId()) {
        $productIdList = array();
        foreach ($shoppingCart->getItems() as $item) {
            $productIdList[] = $item->getProductId();
        }
        $productId = implode(',', $productIdList);
    }

    $recommendations = $liftSuggest->getProductRecommendations($productId);
    ?>
    <div class="lift-recommend">
        <h2><?php echo _zm(sprintf('%s%% of the customers who bought above product(s) also bought these:', (int)$recommendations->getPopularity())) ?></h2>
        <?php foreach ($recommendations->getProductDetails() as $productDetails) { $product = $productDetails['product']; ?>
            <div class="lift-product">
                <p><?php echo $html->productImageLink($product) ?></p>
                <p><a href="<?php echo $net->product($product->getId()) ?>"><?php echo $html->encode($product->getName()) ?></a></p>
                <?php $offers = $product->getOffers(); ?>
                <p class="price"><?php echo $utils->formatMoney($offers->getCalculatedPrice()) ?></p>
            </div>
        <?php } ?>
    </div>
<?php } ?>
