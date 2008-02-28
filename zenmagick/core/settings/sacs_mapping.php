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
 *
 * $Id$
 */
?><?php

    /**
     * Set up default SACS mappings.
     */
    function zm_set_default_sacs_mappings() {
    global $zm_loader, $zm_sacsMapper;

        if (!isset($zm_sacsMapper)) {
            $zm_sacsMapper = $zm_loader->create("SacsMapper");
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
        $zm_sacsMapper->setMapping('checkout_process', ZM_ACCOUNT_TYPE_GUEST);
        $zm_sacsMapper->setMapping('checkout_confirmation', ZM_ACCOUNT_TYPE_GUEST);
        $zm_sacsMapper->setMapping('checkout_payment', ZM_ACCOUNT_TYPE_GUEST);
        $zm_sacsMapper->setMapping('checkout_payment_address', ZM_ACCOUNT_TYPE_GUEST);
        $zm_sacsMapper->setMapping('checkout_shipping', ZM_ACCOUNT_TYPE_GUEST);
        $zm_sacsMapper->setMapping('checkout_shipping_address', ZM_ACCOUNT_TYPE_GUEST);
        $zm_sacsMapper->setMapping('gv_redeem');
        $zm_sacsMapper->setMapping('gv_send');
        $zm_sacsMapper->setMapping('gv_send_confirm');
        $zm_sacsMapper->setMapping('product_reviews_write');
        $zm_sacsMapper->setMapping('login', ZM_ACCOUNT_TYPE_ANONYMOUS);

        if (!zm_setting('isTellAFriendAnonymousAllow')) {
            $zm_sacsMapper->setMapping('tell_a_friend');
        }
    }

?>
