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
 * Request controller for checkout billing address change page.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMCheckoutPaymentAddressController extends ZMController {

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
     * Check cart state.
     *
     * @return ZMView A <code>ZMView</code>  or <code>null</code>.
     */
    function checkCart() {
        $shoppingCart = ZMRequest::getShoppingCart();
        if ($shoppingCart->isEmpty()) {
            return $this->findView("empty_cart");
        }

        if (!$shoppingCart->readyForCheckout()) {
            return $this->findView("cart_not_ready");
        }

        return null;
    }


    /**
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    function process() { 
        ZMCrumbtrail::instance()->addCrumb("Checkout", ZMToolbox::instance()->net->url(FILENAME_CHECKOUT_PAYMENT, '', true, false));
        ZMCrumbtrail::instance()->addCrumb(ZMToolbox::instance()->utils->getTitle(null, false));

        $shoppingCart = ZMRequest::getShoppingCart();
        $this->exportGlobal("zm_cart", $shoppingCart);

        return parent::process();
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
        if (null !== ($view = $this->checkCart())) {
            return $view;
        }

        $addressList = ZMAddresses::instance()->getAddressesForAccountId(ZMRequest::getAccountId());
        $this->exportGlobal("zm_addressList", $addressList);

        $address = ZMLoader::make("Address");
        $address->populate();
        $address->setPrimary(0 == count($addressList));
        $this->exportGlobal("zm_address", $address);

        return $this->findView();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
        if (null !== ($view = $this->checkCart())) {
            return $view;
        }

        // if address field in request, it's a select; otherwise a new address
        $addressId = ZMRequest::getParameter('address', null);

        $shoppingCart = ZMRequest::getShoppingCart();
        if (null !== $addressId) {
            $shoppingCart->setBillingAddressId($addressId);
        } else {
            // TODO: create business objects to share logic...
            // use address book controller to process
            $abc = ZMLoader::make("AddressBookProcessController");
            $view = $abc->createAddress();
            $address = $abc->getGlobal('zm_address');
            if (0 == $address->getId()) {
                $this->exportGlobal("zm_address", $address);
                $addressList = ZMAddresses::instance()->getAddressesForAccountId(ZMRequest::getAccountId());
                $this->exportGlobal("zm_addressList", $addressList);
                return $this->findView();
            }
            // new address
            $address = $abc->getGlobal('zm_address');
            $shoppingCart->setBillingAddressId($address->getId());
        }

        return $this->findView('success');
    }

}

?>
