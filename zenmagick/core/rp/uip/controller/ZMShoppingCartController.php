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
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
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
    public function processGet() {
        ZMCrumbtrail::instance()->addCrumb(ZMToolbox::instance()->utils->getTitle(null, false));
        
        $shoppingCart = ZMRequest::getShoppingCart();
        $this->exportGlobal("zm_cart", $shoppingCart);

        // some validation
        if (ZMSettings::get('isEnableStock') && $shoppingCart->hasOutOfStockItems()) {
            if (ZMSettings::get('isAllowLowStockCheckout')) {
                ZMMessages::instance()->warn('Products marked as "Out Of Stock" will be placed on backorder.');
            } else {
                ZMMessages::instance()->error('The shopping cart contains products currently out of stock. To checkout you may either lower the quantity or remove those products from the cart.');
            }
        }
        $helper = ZMLoader::make('CheckoutHelper', $shoppingCart);
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

        return $this->findView($shoppingCart->isEmpty() ? 'empty_cart' : 'shopping_cart');
    }

}

?>
