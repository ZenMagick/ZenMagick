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
namespace zenmagick\apps\storefront\controller;

use zenmagick\base\Runtime;
use zenmagick\apps\store\utils\CheckoutHelper;

/**
 * Request controller for shopping cart.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ShoppingCartController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $session = $request->getSession();
        $shoppingCart = $request->getShoppingCart();
        $checkoutHelper = Runtime::getContainer()->get('checkoutHelper');
        $checkoutHelper->setShoppingCart($shoppingCart);

        $checkoutHelper->checkStock();

        $statusMap = $checkoutHelper->checkCartStatus();
        foreach ($statusMap as $status => $items) {
            foreach ($items as $item) {
                $product = $item->getProduct();
                switch ($status) {
                case CheckoutHelper::CART_PRODUCT_STATUS:
                    $this->messageService->warn(sprintf('%s: We are sorry but this product has been removed from our inventory at this time.', $product->getName()));
                    break;
                case CheckoutHelper::CART_PRODUCT_QUANTITY:
                    $this->messageService->warn(sprintf('%s: has a minimum quantity restriction; minimum order quantity is: %s', $product->getName(), $product->getMinOrderQty()));
                    break;
                case CheckoutHelper::CART_PRODUCT_UNITS:
                    $this->messageService->warn(sprintf('%s: has a quantity units restriction; minimum order units: %s', $product->getName(), $product->getQtyOrderUnits()));
                    break;
                }
            }
        }

        return $this->findView(null, array('shoppingCart' => $shoppingCart));
    }

}
