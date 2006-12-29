<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * Request controller for checkout shipping address change page.
 *
 * @author mano
 * @package net.radebatz.zenmagick.uip.controller
 * @version $Id$
 */
class ZMCheckoutShippingAddressController extends ZMController {

    // create new instance
    function ZMCheckoutShippingAddressController() {
        parent::__construct();
    }

    // create new instance
    function __construct() {
        $this->ZMCheckoutShippingAddressController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // process a GET request
    function processGet() {
    global $zm_request, $zm_crumbtrail, $zm_cart;

        $zm_crumbtrail->addCrumb("Checkout", zm_secure_href(FILENAME_CHECKOUT_SHIPPING, '', false));
        $zm_crumbtrail->addCrumb(zm_title(false));

        $address =& $this->create("Address");
        $address->populateFromRequest();
        $this->exportGlobal("zm_address", $address);

        return new ZMThemeView('checkout_shipping_address');
    }

}

?>
