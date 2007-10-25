<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Fixes that are event driven.
 *
 * @author mano
 * @package org.zenmagick.misc
 * @version $Id$
 */
class ZMEventFixes extends ZMObject {

    /**
     * Default c'tor.
     */
    function ZMEventFixes() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMEventFixes();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Validate addresses for guest checkout.
     */
    function onNotifyHeaderEndCheckoutConfirmation() {
    global $zm_request, $zm_cart, $zm_messages;

        $session = $zm_request->getSession();
        if ($session->isGuest()) {
            // check for address
            if (!$zm_cart->hasShippingAddress() && !$zm_cart->isVirtual()) {
                $zm_messages->error('Please provide a shipping address');
                zm_redirect(zm_secure_href(FILENAME_CHECKOUT_SHIPPING_ADDRESS));
            }

            if (null == $zm_cart->getBillingAddress()) {
                $zm_messages->error('Please provide a billing address');
                zm_redirect(zm_secure_href(FILENAME_CHECKOUT_PAYMENT_ADDRESS));
            }
        }
    }

}

?>
