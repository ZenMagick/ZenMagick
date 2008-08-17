<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
        ZMCrumbtrail::instance()->addCrumb(ZMToolbox::instance()->utils->getTitle(null, false));
        
        $shoppingCart = ZMRequest::getShoppingCart();
        $this->exportGlobal("zm_cart", $shoppingCart);

        if (ZMSettings::get('isEnableStock') && $shoppingCart->hasOutOfStockItems()) {
            if (ZMSettings::get('isAllowLowStockCheckout')) {
                ZMMessages::instance()->warn('Products marked as "Out Of Stock" will be placed on backorder.');
            } else {
                ZMMessages::instance()->error('The shopping cart contains products currently out of stock. To checkout you may either lower the quantity or remove those products from the cart.');
            }
        }

        return $this->findView($shoppingCart->isEmpty() ? 'empty_cart' : 'shopping_cart');
    }

}

?>
