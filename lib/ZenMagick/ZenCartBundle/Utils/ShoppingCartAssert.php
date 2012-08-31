<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
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
namespace ZenMagick\ZenCartBundle\Utils;

use ZenMagick\Base\ZMObject;

/**
 * Assertations for shopping cart.
 *
 * @author DerManoMann
 * @todo: merge with unit test?
 */
class ShoppingCartAssert extends ZMObject {

    public function assertCart($shoppingCart) {
        // total items
        $itemTotal = 0;
        $cart_ = $shoppingCart->cart_;
        foreach ($shoppingCart->getItems() as $item) {
            $itemTotals += $item->getQuantity();
        }
        if ($cart_->count_contents() != $itemTotals) {
            echo 'cart: item count diff! cart: ';var_dump($cart_->count_contents());echo 'my: ';var_dump($itemTotals);echo '<br>';
        }

        // weight
        if ($cart_->show_weight() != $shoppingCart->getWeight()) {
            echo 'cart: weight diff! cart: ';var_dump($cart_->show_weight());echo 'my: ';var_dump($shoppingCart->getWeight());echo '<br>';
        }

        $zenItems = $cart_->get_products();
        foreach ($shoppingCart->getItems() as $item) {
            $itemId = $item->getId();
            $product = $item->getProduct();
            if ($cart_->get_quantity($itemId) != $shoppingCart->getItemQuantityFor($itemId, false)) {
                echo 'cart: get_quantity diff! cart: ';var_dump($cart_->get_quantity($itemId));echo 'my: ';var_dump($shoppingCart->getItemQuantityFor($itemId, false));echo '<br>';
            }
            if ($cart_->in_cart_mixed($itemId) != $shoppingCart->getItemQuantityFor($itemId, $product->isQtyMixed())) {
                echo 'cart: in_cart_mixed diff! cart: ';var_dump($cart_->in_cart_mixed($itemId));echo 'my: ';var_dump($shoppingCart->getItemQuantityFor($itemId, $product->isQtyMixed()));echo '<br>';
            }

            // prices
            foreach ($zenItems as $zi) {
                if ($zi['id'] == $item->getId()) {
                    if (round($item->getItemPrice(false),2) != round($zi['final_price'],2)) {
                        echo 'cart: item price differ: '.$zi['id'].': ZM: '.$item->getItemPrice(false).', zc: '.$zi['final_price'].'<br>';
                    }
                    if (round($item->getOneTimeCharge(false),2) != round($zi['onetime_charges'],2)) {
                        echo 'cart: onetime charge differ: '.$zi['id'].': ZM: '.$item->getOneTimeCharge(false).', zc: '.$zi['onetime_charges'].'<br>';
                    }
                }
            }
        }

        // compare contents arrays
        $zcContents = $cart_->contents;
        $zmContents = $shoppingCart->getContents();
        if (count($zcContents) != count($zmContents)) {
            echo 'cart content count differs: zc: '.count($zcContents).'. zm: '.count($zmContents).'<br>';
        }
        foreach ($zcContents as $id => $zcitem) {
            if (!array_key_exists($id, $zmContents)) {
                echo 'cart content differs: item not in zm: '.$id.'<br>';
                continue;
            }
            $zmitem = $zmContents[$id];
            if (count($zcitem['attributes']) != count($zmitem['attributes'])) {
                echo 'cart content attr count differs: '.$id.' zc: '.count($zcitem['attributes']).'. zm: '.count($zmitem['attributes']).'<br>';
            }
        }
    }

}
