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

        // mappings for shared views and error pages
        $urlMapper->setMapping(null, 'error', 'error');
        $urlMapper->setMapping(null, 'missing_page', 'error');
        $urlMapper->setMapping(null, 'product_not_found', 'error');
        $urlMapper->setMapping(null, 'category_not_found', 'error');
        $urlMapper->setMapping(null, 'index', 'index');
        $urlMapper->setMapping(null, 'login', 'login', 'RedirectView');

        // checkout states
        $urlMapper->setMapping(null, 'empty_cart', 'shopping_cart', 'RedirectView');
        $urlMapper->setMapping(null, 'cart_not_ready', 'shopping_cart', 'RedirectView');
        $urlMapper->setMapping(null, 'cart_is_virtual', 'checkout_billing', 'RedirectView');
        $urlMapper->setMapping(null, 'check_cart', 'shopping_cart', 'RedirectView');

        // popups
        $urlMapper->setMapping(null, 'popup_search_help', 'popup_search_help', 'PageView', 'subdir=popup');
        $urlMapper->setMapping(null, 'popup_shipping_estimator', 'popup_shipping_estimator', 'PageView', 'subdir=popup');
        $urlMapper->setMapping(null, 'popup_cvv_help', 'popup_cvv_help', 'PageView', 'subdir=popup');
        $urlMapper->setMapping(null, 'popup_coupon_help', 'popup_coupon_help', 'PageView', 'subdir=popup');

        // address_book_process views
        $urlMapper->setMapping('address_book_process', 'address_book_create', 'address_book_create');
        $urlMapper->setMapping('address_book_process', 'address_book_edit', 'address_book_edit');
        $urlMapper->setMapping('address_book_process', 'address_book_delete', 'address_book_delete');
        $urlMapper->setMapping('address_book_process', 'success', 'address_book', 'RedirectView');

        // index; support for old style category URLs using index
        $urlMapper->setMapping('index', 'category', 'category', 'ForwardView');

        // discount_coupon
        $urlMapper->setMapping('discount_coupon', 'info', 'discount_coupon_info');

        // parameter dependant category views
        $urlMapper->setMapping('category', 'category', 'category');
        $urlMapper->setMapping('category', 'category_list', 'category_list');
        $urlMapper->setMapping('category', 'manufacturer', 'manufacturer');
        $urlMapper->setMapping('category', 'product_info', 'product_info', 'ForwardView');

        // login [form]
        $urlMapper->setMappingInfo('login', array('viewId' => 'success', 'view' => 'account', 'class' => 'RedirectView'));
        $urlMapper->setMappingInfo('login', array('viewId' => 'account', 'view' => 'account', 'class' => 'RedirectView'));

        // logoff
        $urlMapper->setMapping('logoff', 'success', 'logoff', 'RedirectView');

        // password_forgotten [form]
        $urlMapper->setMapping('password_forgotten', 'success', 'login', 'RedirectView');

        // guest checkout
        $urlMapper->setMapping('checkout_guest', 'login');
        $urlMapper->setMapping('checkout_guest', 'checkout_guest', 'login');
        $urlMapper->setMapping('checkout_guest', 'guest_checkout_disabled', 'login', 'RedirectView');
        $urlMapper->setMapping('checkout_guest', 'success', 'checkout_shipping_address', 'RedirectView');

        // guest history
        $urlMapper->setMapping('guest_history', 'success', 'account_history_info');

        // product_info
        $urlMapper->setMapping('product_info', 'error', 'product_not_found');
        $urlMapper->setMapping('product_info', 'product_music_info', 'product_music_info');
        $urlMapper->setMapping('product_info', 'document_general_info', 'document_general_info');
        $urlMapper->setMapping('product_info', 'document_product_info', 'document_product_info');
        $urlMapper->setMapping('product_info', 'product_free_shipping_info', 'product_free_shipping_info');

        // gv_send
        $urlMapper->setMapping('gv_send', 'success', 'gv_send_confirm');
        $urlMapper->setMapping('gv_send_confirm', 'edit', 'gv_send', 'ForwardView');
        $urlMapper->setMapping('gv_send_confirm', 'success', 'account', 'RedirectView');

        // account [forms]
        $urlMapper->setMapping('create_account', 'success', 'account', 'RedirectView');
        $urlMapper->setMapping('account_edit', 'success', 'account', 'RedirectView');
        $urlMapper->setMapping('account_password', 'success', 'account', 'RedirectView');
        $urlMapper->setMapping('account_newsletters', 'success', 'account', 'RedirectView');
        $urlMapper->setMapping('account_notifications', 'success', 'account', 'RedirectView');


        // checkout [forms]
        $urlMapper->setMapping('shopping_cart', 'empty_cart', 'empty_cart');
        $urlMapper->setMapping('checkout_shipping', 'success', 'checkout_billing', 'RedirectView');
        $urlMapper->setMapping('checkout_shipping_address', 'success', 'checkout_shipping', 'RedirectView');
        $urlMapper->setMapping('checkout_payment_address', 'success', 'checkout_payment', 'RedirectView');

        // redirect
        $urlMapper->setMapping('redirect', 'success', 'index', 'RedirectView');
        $urlMapper->setMapping('redirect', 'error', 'index', 'ForwardView');

        // misc [form]
        $urlMapper->setMapping('product_reviews_write', 'success', 'product_reviews', 'RedirectView');
        $urlMapper->setMapping('tell_a_friend', 'success', 'product_info', 'RedirectView');
        $urlMapper->setMapping('contact_us', 'success', 'contact_us_success', 'RedirectView');
    }

?>
