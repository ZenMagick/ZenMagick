<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
    // TODO: review and move into plugin somewhere
    $recoProds = $session->get('reco_prods', array());
    if (('product_info' == $request->getRequestId()) && isset($currentProduct)) {
        $productId = $currentProduct->getId();
        if (in_array($productId, $recoProds)) {
            $ls_rev_css = "liftsuggest {act:'prodview', sku:'".$productId."', reco:'R'}";
        } else {
            $ls_rev_css = "liftsuggest {act:'prodview', sku:'".$productId."', reco:'N'}";
        }
    } elseif ('shopping_cart' == $request->getRequestId()) {
        $productIdList = array();
        foreach ($shoppingCart->getItems() as $item) {
            $productIdList[] = $item->getProductId();
        }
            $ls_rev_css = "";
        foreach ($shoppingCart->getItems() as $item) {
            if (in_array($item->getProductId(), $recoProds)) {
                $ls_rev_css = "liftsuggest {act:'prodview', sku:'".$productId."', reco:'R'}";
                break;
            } else {
                $ls_rev_css = "";
            }
        }
        $productId = implode(',', $productIdList);
    }

    /* added lifsuggest class to enable measure of recommendations performance by SP_TATVIC:20111009*/
    if (null != ($recommendations = $liftSuggest->getProductRecommendations($productId))) { ?>
        <div class="lift-recommend <?php echo $ls_rev_css ?>">
            <h2><?php echo sprintf(_zm('%s%% of the customers who bought above product(s) also bought these1:'), (int) $recommendations->getPopularity()) ?></h2>
            <?php foreach ($recommendations->getProductDetails() as $productDetails) { $product = $productDetails['product']; ?>
          <div class="lift-product lsrecommendations {act:'prodview', sku:'<?php echo $product->getId() ;?>', reco:'R'}">
                    <p><?php echo $html->productImageLink($product) ?></p>
                    <p><a href="<?php echo $net->product($product->getId()) ?>"><?php echo $html->encode($product->getName()) ?></a></p>
                    <?php $offers = $product->getOffers(); ?>
                    <p class="price"><?php echo $utils->formatMoney($offers->getCalculatedPrice()) ?></p>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
<?php } ?>
