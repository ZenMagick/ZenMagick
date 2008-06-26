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
     * Set up default URL mappings.
     *
     * <p>The reason for this being wrapped in a function is to make it possible
     * to include in <code>core.php</code>. Also, this leaves the option of
     * alternative storage to improve loading time.</p>
     *
     * @package org.zenmagick.settings
     */
    function zm_set_default_url_mappings() {
        $urlMapper = ZMUrlMapper::instance();

        // global
        $urlMapper->setMapping(null, 'error', 'error');
        $urlMapper->setMapping(null, 'missing_page', 'error');
        $urlMapper->setMapping(null, 'product_not_found', 'error');
        $urlMapper->setMapping(null, 'category_not_found', 'error');
        $urlMapper->setMapping(null, 'index', 'index');
        $urlMapper->setMapping(null, 'login', 'login', 'RedirectView', 'secure="true');

        // checkout states
        $urlMapper->setMapping(null, 'empty_cart', 'shopping_cart', 'RedirectView', 'secure=true');
        $urlMapper->setMapping(null, 'cart_not_ready', 'shopping_cart', 'RedirectView', 'secure=true');
        $urlMapper->setMapping(null, 'cart_is_virtual', 'checkout_billing', 'RedirectView', 'secure=true');
        $urlMapper->setMapping(null, 'check_cart', 'shopping_cart', 'RedirectView', 'secure=true');


        // popups
        $urlMapper->setMapping(null, 'popup_search_help', 'popup_search_help', 'PageView', 'subdir=popup');
        $urlMapper->setMapping(null, 'popup_shipping_estimator', 'popup_shipping_estimator', 'PageView', 'subdir=popup');
        $urlMapper->setMapping(null, 'popup_cvv_help', 'popup_cvv_help', 'PageView', 'subdir=popup');
        $urlMapper->setMapping(null, 'popup_coupon_help', 'popup_coupon_help', 'PageView', 'subdir=popup');

        // address_book_process
        $urlMapper->setMapping('address_book_process', 'address_book_create', 'address_book_create');
        $urlMapper->setMapping('address_book_process', 'address_book_edit', 'address_book_edit');
        $urlMapper->setMapping('address_book_process', 'address_book_delete', 'address_book_delete');
        $urlMapper->setMapping('address_book_process', 'success', 'address_book', 'RedirectView', 'secure=true');

        // contact_us
        $urlMapper->setMapping('contact_us');
        $urlMapper->setMapping('contact_us', 'success', 'contact_us_success', 'RedirectView');

        // discount_coupon
        $urlMapper->setMapping('discount_coupon');
        $urlMapper->setMapping('discount_coupon', 'info', 'discount_coupon_info');

        // gv_send
        $urlMapper->setMapping('gv_send');
        $urlMapper->setMapping('gv_send', 'success', 'gv_send_confirm');
        $urlMapper->setMapping('gv_send_confirm', 'edit', 'gv_send', 'ForwardView');
        $urlMapper->setMapping('gv_send_confirm', 'success', 'account', 'RedirectView', 'secure=true');

        // index
        $urlMapper->setMapping('index', 'category', 'category', 'ForwardView');

        // category
        $urlMapper->setMapping('category', 'category', 'category');
        $urlMapper->setMapping('category', 'category_list', 'category_list');
        $urlMapper->setMapping('category', 'manufacturer', 'manufacturer');
        $urlMapper->setMapping('category', 'product_info', 'product_info', 'ForwardView');

        // login
        $urlMapper->setMapping('login');
        $urlMapper->setMapping('login', 'success', 'account', 'RedirectView', 'secure=true');
        $urlMapper->setMapping('login', 'account', 'account', 'RedirectView', 'secure=true');

        // logoff
        $urlMapper->setMapping('logoff');
        $urlMapper->setMapping('logoff', 'success', 'logoff', 'RedirectView');

        // password_forgotten
        $urlMapper->setMapping('password_forgotten');
        $urlMapper->setMapping('password_forgotten', 'success', 'login', 'RedirectView', 'secure=true');

        // guest checkout
        $urlMapper->setMapping('checkout_guest', 'login');
        $urlMapper->setMapping('checkout_guest', 'checkout_guest', 'login');
        $urlMapper->setMapping('checkout_guest', 'guest_checkout_disabled', 'login', 'RedirectView', 'secure=true');
        $urlMapper->setMapping('checkout_guest', 'success', 'checkout_shipping_address', 'RedirectView', 'secure=true');

        // guest history
        $urlMapper->setMapping('guest_history');
        $urlMapper->setMapping('guest_history', 'success', 'account_history_info');

        // product_info
        $urlMapper->setMapping('product_info');
        $urlMapper->setMapping('product_info', 'error', 'product_not_found');
        $urlMapper->setMapping('product_info', 'product_music_info', 'product_music_info');
        $urlMapper->setMapping('product_info', 'document_general_info', 'document_general_info');
        $urlMapper->setMapping('product_info', 'document_product_info', 'document_product_info');
        $urlMapper->setMapping('product_info', 'product_free_shipping_info', 'product_free_shipping_info');

        // account_edit
        $urlMapper->setMapping('account_edit');
        $urlMapper->setMapping('account_edit', 'success', 'account', 'RedirectView', 'secure=true');

        // account_password
        $urlMapper->setMapping('account_password');
        $urlMapper->setMapping('account_password', 'success', 'account', 'RedirectView', 'secure=true');

        // shopping_cart
        $urlMapper->setMapping('shopping_cart');
        $urlMapper->setMapping('shopping_cart', 'empty_cart', 'empty_cart');

        // create_account
        $urlMapper->setMapping('create_account');
        $urlMapper->setMapping('create_account', 'success', 'account', 'RedirectView', 'secure=true');

        // tell_a_friend
        $urlMapper->setMapping('tell_a_friend');
        $urlMapper->setMapping('tell_a_friend', 'success', 'product_info', 'RedirectView');

        // product_reviews_write
        $urlMapper->setMapping('product_reviews_write');
        $urlMapper->setMapping('product_reviews_write', 'success', 'product_reviews', 'RedirectView');

        // account_newsletters
        $urlMapper->setMapping('account_newsletters');
        $urlMapper->setMapping('account_newsletters', 'success', 'account', 'RedirectView', 'secure=true');

        // account_notifications
        $urlMapper->setMapping('account_notifications');
        $urlMapper->setMapping('account_notifications', 'success', 'account', 'RedirectView', 'secure=true');

        // shipping 
        $urlMapper->setMapping('checkout_shipping');
        $urlMapper->setMapping('checkout_shipping', 'success', 'checkout_billing', 'RedirectView', 'secure=true');

        // shipping address
        $urlMapper->setMapping('checkout_shipping_address');
        $urlMapper->setMapping('checkout_shipping_address', 'success', 'checkout_shipping', 'RedirectView', 'secure=true');

        // billing address
        $urlMapper->setMapping('checkout_payment_address');
        $urlMapper->setMapping('checkout_payment_address', 'success', 'checkout_payment', 'RedirectView', 'secure=true');

        // redirect
        $urlMapper->setMapping('redirect', 'success', 'index', 'RedirectView');
        $urlMapper->setMapping('redirect', 'error', 'index', 'ForwardView');
    }

?>
