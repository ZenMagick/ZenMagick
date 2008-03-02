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
 * Request controller for checkout shipping page.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMCheckoutShippingController extends ZMController {

    /**
     * Create new instance.
     */
    function ZMCheckoutShippingController() {
        parent::__construct();
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->ZMCheckoutShippingController();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    function process() { 
    global $zm_request, $zm_cart, $zm_messages, $zm_crumbtrail;

        // do a bit of checking first...
        if ($zm_cart->isEmpty()) {
            return $this->findView('empty_cart');
        }

        if (!$zm_cart->readyForCheckout()) {
            $zm_messages->error(zm_l10n_get('Please update your order ...'));
            return $this->findView('check_cart');
        }

        // stock handling
        if (zm_setting('isEnableStock') && !zm_setting('isAllowLowStockCheckout')) {
            foreach ($zm_cart->getItems() as $item) {
                if (!$item->isStockAvailable()) {
                    $zm_messages->error(zm_l10n_get('Some items in your order are out of stock'));
                    return $this->findView('check_cart');
                }
            }
        }

        // set default address if required
        if (!$zm_cart->hasShippingAddress()) {
            $account = $zm_request->getAccount();
            $zm_cart->setShippingAddressId($account->getDefaultAddresssId());
        }

        $zm_crumbtrail->addCrumb("Checkout", zm_secure_href(FILENAME_CHECKOUT_SHIPPING, '', false));
        $zm_crumbtrail->addCrumb(zm_title(false));

        return parent::process();
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
    global $zm_request;

        $this->exportGlobal("zm_shipping", $this->create("Shipping"));

        return $this->findView();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {

    }

}

?>
