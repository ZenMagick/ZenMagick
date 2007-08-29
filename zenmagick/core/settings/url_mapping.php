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
     * Set up default URL mappings.
     *
     * <p>The reason for this being wrapped in a function is to make it possible
     * to include in <code>core.php</code>. Also, this leaves the option of
     * alternative storage to improve loading time.</p>
     *
     * @package net.radebatz.zenmagick.settings
     */
    function zm_set_default_url_mappings() {
    global $zm_urlMapper;

        if (!isset($zm_urlMapper)) {
            $zm_urlMapper = new ZMUrlMapper();
        }

        // global
        $zm_urlMapper->setMapping(null, 'error', 'error');
        $zm_urlMapper->setMapping(null, 'missing_page', 'error');
        $zm_urlMapper->setMapping(null, 'index', 'index');
        $zm_urlMapper->setMapping(null, 'login', 'login', 'RedirectView', 'secure="true');
        $zm_urlMapper->setMapping(null, 'cookie_usage', 'cookie_usage', 'RedirectView');

        // checkout states
        $zm_urlMapper->setMapping(null, 'empty_cart', 'shopping_cart', 'RedirectView', 'secure=true');
        $zm_urlMapper->setMapping(null, 'cart_not_ready', 'shopping_cart', 'RedirectView', 'secure=true');
        $zm_urlMapper->setMapping(null, 'cart_is_virtual', 'checkout_billing', 'RedirectView', 'secure=true');


        // popups
        $zm_urlMapper->setMapping(null, 'popup_search_help', 'popup_search_help', 'PopupView');

        // address_book_process
        $zm_urlMapper->setMapping('address_book_process', 'address_book_create', 'address_book_create');
        $zm_urlMapper->setMapping('address_book_process', 'address_book_edit', 'address_book_edit');
        $zm_urlMapper->setMapping('address_book_process', 'address_book_delete', 'address_book_delete');
        $zm_urlMapper->setMapping('address_book_process', 'success', 'address_book', 'RedirectView', 'secure=true');

        // contact_us
        $zm_urlMapper->setMapping('contact_us');
        $zm_urlMapper->setMapping('contact_us', 'success', 'contact_us_success', 'RedirectView');

        // discount_coupon
        $zm_urlMapper->setMapping('discount_coupon');
        $zm_urlMapper->setMapping('discount_coupon', 'info', 'discount_coupon_info');

        // gv_send
        $zm_urlMapper->setMapping('gv_send');
        $zm_urlMapper->setMapping('gv_send', 'confirm', 'gv_send_confirm', 'RedirectView', 'secure=true');
        $zm_urlMapper->setMapping('gv_send', 'success', 'account', 'RedirectView', 'secure=true');

        // index
        $zm_urlMapper->setMapping('index', 'category', 'category');
        $zm_urlMapper->setMapping('index', 'category_list', 'category_list');
        $zm_urlMapper->setMapping('index', 'manufacturer', 'manufacturer');
        // index is configured global

        // login
        $zm_urlMapper->setMapping('login');
        $zm_urlMapper->setMapping('login', 'success', 'account', 'RedirectView', 'secure=true');
        $zm_urlMapper->setMapping('login', 'account', 'account', 'RedirectView', 'secure=true');

        // logoff
        $zm_urlMapper->setMapping('logoff');
        $zm_urlMapper->setMapping('logoff', 'success', 'logoff', 'RedirectView');

        // password_forgotten
        $zm_urlMapper->setMapping('password_forgotten');
        $zm_urlMapper->setMapping('password_forgotten', 'success', 'login', 'RedirectView', 'secure=true');

        // guest checkout
        $zm_urlMapper->setMapping('checkout_guest', 'login');
        $zm_urlMapper->setMapping('checkout_guest', 'guest_checkout_disabled', 'login', 'RedirectView', 'secure=true');
        $zm_urlMapper->setMapping('checkout_guest', 'success', 'checkout_shipping_address', 'RedirectView', 'secure=true');

        // guest history
        $zm_urlMapper->setMapping('guest_history');
        $zm_urlMapper->setMapping('guest_history', 'success', 'account_history_info');

        // product_info
        $zm_urlMapper->setMapping('product_info');
        $zm_urlMapper->setMapping('product_info', 'product_music_info', 'product_music_info');
        $zm_urlMapper->setMapping('product_info', 'document_general_info', 'document_general_info');
        $zm_urlMapper->setMapping('product_info', 'document_product_info', 'document_product_info');
        $zm_urlMapper->setMapping('product_info', 'product_free_shipping_info', 'product_free_shipping_info');

        // account_edit
        $zm_urlMapper->setMapping('account_edit');
        $zm_urlMapper->setMapping('account_edit', 'success', 'account', 'RedirectView', 'secure=true');

        // account_password
        $zm_urlMapper->setMapping('account_password');
        $zm_urlMapper->setMapping('account_password', 'success', 'account', 'RedirectView', 'secure=true');

        // shopping_cart
        $zm_urlMapper->setMapping('shopping_cart');
        $zm_urlMapper->setMapping('shopping_cart', 'empty_cart', 'empty_cart');

        // create_account
        $zm_urlMapper->setMapping('create_account');
        $zm_urlMapper->setMapping('create_account', 'success', 'account', 'RedirectView', 'secure=true');

        // tell_a_friend
        $zm_urlMapper->setMapping('tell_a_friend');
        $zm_urlMapper->setMapping('tell_a_friend', 'success', 'product_info', 'RedirectView');

        // product_reviews_write
        $zm_urlMapper->setMapping('product_reviews_write');
        $zm_urlMapper->setMapping('product_reviews_write', 'success', 'product_reviews', 'RedirectView');

        // account_newsletters
        $zm_urlMapper->setMapping('account_newsletters');
        $zm_urlMapper->setMapping('account_newsletters', 'success', 'account', 'RedirectView', 'secure=true');

        // account_notifications
        $zm_urlMapper->setMapping('account_notifications');
        $zm_urlMapper->setMapping('account_notifications', 'success', 'account', 'RedirectView', 'secure=true');

        // shipping address
        $zm_urlMapper->setMapping('checkout_shipping_address');
        $zm_urlMapper->setMapping('checkout_shipping_address', 'success', 'checkout_shipping', 'RedirectView', 'secure=true');
    }

?>
