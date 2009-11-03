<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
<?php


/**
 * Request controller for shopping cart.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id: ZMShoppingCartController.php 2350 2009-06-29 04:22:59Z dermanomann $
 */
class ZMShoppingCartController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $request->getCrumbtrail()->addCrumb($request->getToolbox()->utils->getTitle(null, false));
        
        $shoppingCart = $request->getShoppingCart();

        $helper = ZMLoader::make('CheckoutHelper', $shoppingCart);

        $helper->checkStock();

        $statusMap = $helper->checkCartStatus();
        foreach ($statusMap as $status => $items) {
            foreach ($items as $item) {
                $product = $item->getProduct();
                switch ($status) {
                case ZMCheckoutHelper::CART_PRODUCT_STATUS:
                    ZMMessages::instance()->warn(sprintf('%s: We are sorry but this product has been removed from our inventory at this time.', $product->getName()));
                    break;
                case ZMCheckoutHelper::CART_PRODUCT_QUANTITY:
                    ZMMessages::instance()->warn(sprintf('%s: has a minimum quantity restriction; minimum order quantity is: %s', $product->getName(), $product->getMinOrderQty()));
                    break;
                case ZMCheckoutHelper::CART_PRODUCT_UNITS:
                    ZMMessages::instance()->warn(sprintf('%s: has a quantity units restriction; minimum order units: %s', $product->getName(), $product->getQtyOrderUnits()));
                    break;
                }
            }
        }

        return $this->findView($shoppingCart->isEmpty() ? 'empty_cart' : 'shopping_cart', array('zm_cart' => $shoppingCart));
    }

}

?>
