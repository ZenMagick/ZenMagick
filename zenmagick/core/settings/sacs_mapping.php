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
 *
 * $Id$
 */
?><?php

    /**
     * Set up default SACS mappings.
     */
    function zm_set_default_sacs_mappings() {
    global $zm_sacsMapper;

        if (!isset($zm_sacsMapper)) {
            $zm_sacsMapper = new ZMSacsMapper();
        }

        // default access settings
        $zm_sacsMapper->setMapping('account');
        $zm_sacsMapper->setMapping('account_edit');
        $zm_sacsMapper->setMapping('account_history');
        $zm_sacsMapper->setMapping('account_history_info');
        $zm_sacsMapper->setMapping('account_newsletter');
        $zm_sacsMapper->setMapping('account_notifications');
        $zm_sacsMapper->setMapping('account_password');
        $zm_sacsMapper->setMapping('address_book');
        $zm_sacsMapper->setMapping('address_book_process');
        $zm_sacsMapper->setMapping('checkout_process');
        $zm_sacsMapper->setMapping('checkout_confirmation');
        $zm_sacsMapper->setMapping('checkout_payment');
        $zm_sacsMapper->setMapping('checkout_payment_address');
        $zm_sacsMapper->setMapping('checkout_shipping');
        $zm_sacsMapper->setMapping('checkout_shipping_address');
        $zm_sacsMapper->setMapping('gv_redeem');
        $zm_sacsMapper->setMapping('gv_send');
        $zm_sacsMapper->setMapping('password_forgotten');
        $zm_sacsMapper->setMapping('product_reviews_write');

        if (!zm_setting('isTellAFriendGuestAllow')) {
            $zm_sacsMapper->setMapping('tell_a_friend');
        }
    }

?>
