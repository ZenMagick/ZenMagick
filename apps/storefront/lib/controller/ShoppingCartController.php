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
namespace zenmagick\apps\store\storefront\controller;

use ZMRequest;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\http\view\ModelAndView;
use zenmagick\apps\store\utils\CheckoutHelper;

/**
 * Request controller for shopping cart.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ShoppingCartController extends ZMObject {

    /**
     * Show cart.
     *
     * @param ZMRequest request The current request.
     */
    public function show(ZMRequest $request) {
        $shoppingCart = $request->getShoppingCart();
        $checkoutHelper = $shoppingCart->getCheckoutHelper();

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

        return new ModelAndView(null, array('shoppingCart' => $shoppingCart));
    }

protected function syncZC($session, $shoppingCart) {
                // sync back to ZenCart
                $cart = $session->getValue('cart');
                $cart = (null != $cart) ? $cart : new \shoppingCart;
                $cart->contents = $shoppingCart->getContents();

}

    /**
     * Add product.
     */
    public function addProduct(ZMRequest $request) {
        $shoppingCart = $request->getShoppingCart();
        $shoppingCart->addProduct($request->getProductId(), $request->getParameter('cart_quantity'), $request->getParameter('id'));
        $shoppingCart->getCheckoutHelper()->saveHash($request);
        // TODO: remove
        $this->syncZC($request->getSession(), $shoppingCart);
        // TODO: message/error handling

        // TODO: add support for redirect back to origin
        return new ModelAndView('success', array('shoppingCart' => $shoppingCart));
    }

    /**
     * Remove product.
     */
    public function removeProduct(ZMRequest $request) {
        $shoppingCart = $request->getShoppingCart();
        $shoppingCart->removeProduct($request->getParameter('product_id'));
        $shoppingCart->getCheckoutHelper()->saveHash($request);
        // TODO: remove
        $this->syncZC($request->getSession(), $shoppingCart);
        // TODO: message/error handling

        // TODO: add support for redirect back to origin
        return new ModelAndView('success', array('shoppingCart' => $shoppingCart));
    }

    /**
     * Update cart.
     * @todo: edit cart attributes
     */
    public function update(ZMRequest $request) {
        $shoppingCart = $request->getShoppingCart();
        $productIds = (array) $request->getParameter('products_id');
        $quantities = (array) $request->getParameter('cart_quantity');
        foreach ($productIds as $ii => $productId) {
            $shoppingCart->updateProduct($productId, $quantities[$ii]);
        }
        // TODO: remove
        $this->syncZC($request->getSession(), $shoppingCart);
        // TODO: message/error handling

        // TODO: add support for redirect back to origin
        return new ModelAndView('success', array('shoppingCart' => $shoppingCart));
    }

}
