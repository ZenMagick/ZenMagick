<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
     * @package org.zenmagick.store
     */
    function zm_set_default_sacs_mappings() {
        $sacsManager = ZMSacsManager::instance();

        // default access settings
        $sacsManager->setMapping('account', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('account_edit', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('account_history', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('account_history_info', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('account_newsletters', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('account_notifications', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('account_password', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('address_book', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('address_book_process', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('checkout_process', ZMZenCartUserSacsHandler::GUEST);
        $sacsManager->setMapping('checkout_confirmation', ZMZenCartUserSacsHandler::GUEST);
        $sacsManager->setMapping('checkout_payment', ZMZenCartUserSacsHandler::GUEST);
        $sacsManager->setMapping('checkout_payment_address', ZMZenCartUserSacsHandler::GUEST);
        $sacsManager->setMapping('checkout_shipping', ZMZenCartUserSacsHandler::GUEST);
        $sacsManager->setMapping('checkout_shipping_address', ZMZenCartUserSacsHandler::GUEST);
        $sacsManager->setMapping('gv_redeem', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('gv_send', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('gv_send_confirm', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('product_reviews_write', ZMZenCartUserSacsHandler::REGISTERED);
        $sacsManager->setMapping('login', ZMZenCartUserSacsHandler::ANONYMOUS);
        $sacsManager->setMapping('create_account', ZMZenCartUserSacsHandler::ANONYMOUS);

        if (!ZMSettings::get('isTellAFriendAnonymousAllow')) {
            $sacsManager->setMapping('tell_a_friend', ZMZenCartUserSacsHandler::REGISTERED);
        }
    }

?>
