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
 *
 * $Id$
 */
?><?php

    /**
     * Set up default SACS mappings.
     *
     * <p>The reason for this being wrapped in a function is to make it possible
     * to include in <code>core.php</code>. Also, this leaves the option of
     * alternative storage to improve loading time.</p>
     *
     * @package org.zenmagick.settings
     */
    function zm_set_default_sacs_mappings() {
        $sacsMapper = ZMSacsMapper::instance();

        // default access settings
        $sacsMapper->setMapping('account');
        $sacsMapper->setMapping('account_edit');
        $sacsMapper->setMapping('account_history');
        $sacsMapper->setMapping('account_history_info');
        $sacsMapper->setMapping('account_newsletters');
        $sacsMapper->setMapping('account_notifications');
        $sacsMapper->setMapping('account_password');
        $sacsMapper->setMapping('address_book');
        $sacsMapper->setMapping('address_book_process');
        $sacsMapper->setMapping('checkout_process', ZMSacsMapper::GUEST);
        $sacsMapper->setMapping('checkout_confirmation', ZMSacsMapper::GUEST);
        $sacsMapper->setMapping('checkout_payment', ZMSacsMapper::GUEST);
        $sacsMapper->setMapping('checkout_payment_address', ZMSacsMapper::GUEST);
        $sacsMapper->setMapping('checkout_shipping', ZMSacsMapper::GUEST);
        $sacsMapper->setMapping('checkout_shipping_address', ZMSacsMapper::GUEST);
        $sacsMapper->setMapping('gv_redeem');
        $sacsMapper->setMapping('gv_send');
        $sacsMapper->setMapping('gv_send_confirm');
        $sacsMapper->setMapping('product_reviews_write');
        $sacsMapper->setMapping('login', ZMSacsMapper::ANONYMOUS);
        $sacsMapper->setMapping('create_account', ZMSacsMapper::ANONYMOUS);

        if (!ZMSettings::get('isTellAFriendAnonymousAllow')) {
            $sacsMapper->setMapping('tell_a_friend');
        }
    }

?>
