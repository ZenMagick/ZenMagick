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
 * Fixes that are event driven.
 *
 * @author mano
 * @package org.zenmagick.misc
 * @version $Id$
 */
class ZMEventFixes extends ZMObject {

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
     * Validate addresses for guest checkout.
     */
    function onNotifyHeaderEndCheckoutConfirmation() {
        $session = ZMRequest::getSession();
        $shoppingCart = ZMRequest::getShoppingCart();
        if ($session->isGuest()) {
            // check for address
            if (!$shoppingCart->hasShippingAddress() && !$shoppingCart->isVirtual()) {
                ZMMessages::instance()->error(zm_l10n_get('Please provide a shipping address'));
                ZMRequest::redirect(ZMToolbox::instance()->net->url(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', true, false));
            }

            if (null == $shoppingCart->getBillingAddress()) {
                ZMMessages::instance()->error(zm_l10n_get('Please provide a billing address'));
                ZMRequest::redirect(ZMToolbox::instance()->net->url(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', true, false));
            }
        }
    }

    /**
     * Remove ajax requests from navigation history.
     */
    function onZMDispatchStart() {
        if (false !== strpos(ZMRequest::getPageName(), 'ajax')) {
            $_SESSION['navigation']->remove_current_page();
        }
    }

}

?>
