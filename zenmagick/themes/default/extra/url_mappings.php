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

    /*
     * The central place for mapping views to controller.
     *
     * Default id is 'page' for normal processing.
     */

    // global
    $zm_urlMapper->addMapping(null, 'error', 'error');
    $zm_urlMapper->addMapping(null, 'home', 'index');


    // address_book_process
    $zm_urlMapper->addMapping('address_book_process', 'create', 'address_book_create');
    $zm_urlMapper->addMapping('address_book_process', 'edit', 'address_book_edit');
    $zm_urlMapper->addMapping('address_book_process', 'delete', 'address_book_delete');

    // advanced_search
    $zm_urlMapper->addMapping('advanced_search', 'page', 'advanced_search');

    // advanced_search_result
    $zm_urlMapper->addMapping('advanced_search_result', 'page', 'advanced_search_result');

    // checkout_confirmation
    $zm_urlMapper->addMapping('checkout_confirmation', 'page', 'checkout_confirmation');

    // checkout_payment_address
    $zm_urlMapper->addMapping('checkout_payment_address', 'page', 'checkout_payment_address');

    // checkout_payment
    $zm_urlMapper->addMapping('checkout_payment', 'page', 'checkout_payment');

    // checkout_shipping_address
    $zm_urlMapper->addMapping('checkout_shipping_address', 'page', 'checkout_shipping_address');

    // checkout_shipping
    $zm_urlMapper->addMapping('checkout_shipping', 'page', 'checkout_shipping');

    // checkout_success
    $zm_urlMapper->addMapping('checkout_success', 'page', 'checkout_success');

    // contact_us
    $zm_urlMapper->addMapping('contact_us', 'page', 'contact_us');
    $zm_urlMapper->addMapping('contact_us', 'success', 'contact_us_success', true);

    // create_account
    $zm_urlMapper->addMapping('create_account', 'page', 'create_account');

    // discount_coupon
    $zm_urlMapper->addMapping('discount_coupon', 'page', 'discount_coupon');
    $zm_urlMapper->addMapping('discount_coupon', 'info', 'discount_coupon_info');

    // featured_products
    $zm_urlMapper->addMapping('featured_products', 'page', 'featured_products');

    // gv_send
    $zm_urlMapper->addMapping('gv_send', 'page', 'gv_send');
    $zm_urlMapper->addMapping('gv_send', 'confirm', 'gv_send_confirm');

    // index
    $zm_urlMapper->addMapping('index', 'category', 'category');
    $zm_urlMapper->addMapping('index', 'category_list', 'category_list');
    $zm_urlMapper->addMapping('index', 'manufacturer', 'manufacturer');
    // home is configured global

    // login
    $zm_urlMapper->addMapping('login', 'page', 'login');
    $zm_urlMapper->addMapping('login', 'success', 'account', true);

    // logoff
    $zm_urlMapper->addMapping('logoff', 'success', 'logoff', true);

    // page
    $zm_urlMapper->addMapping('page', 'page', 'page');

    // password_forgotten
    $zm_urlMapper->addMapping('password_forgotten', 'success', 'login', true);

    // product_comparison
    $zm_urlMapper->addMapping('product_comparison', 'page', 'product_comparison');

    // product_info
    $zm_urlMapper->addMapping('product_info', 'page', 'product_info');
    //TODO: product types

    // product_reviews
    $zm_urlMapper->addMapping('product_reviews', 'page', 'product_reviews');

    // product_reviews_info
    $zm_urlMapper->addMapping('product_reviews_info', 'page', 'product_reviews_info');

    // product_reviews_write
    $zm_urlMapper->addMapping('product_reviews_write', 'page', 'product_reviews_write');

    // products_new
    $zm_urlMapper->addMapping('products_new', 'page', 'products_new');

    // products_new
    $zm_urlMapper->addMapping('specials', 'page', 'specials');

    // reviews
    $zm_urlMapper->addMapping('reviews', 'page', 'reviews');

    // static
    $zm_urlMapper->addMapping('static', 'page', 'static');

    // tell_a_friend
    $zm_urlMapper->addMapping('tell_a_friend', 'page', 'tell_a_friend');

    // account
    $zm_urlMapper->addMapping('account', 'page', 'account');

    // account_edit
    $zm_urlMapper->addMapping('account_edit', 'page', 'account_edit');

    // account_history
    $zm_urlMapper->addMapping('account_history', 'page', 'account_history');

    // account_history_info
    $zm_urlMapper->addMapping('account_history_info', 'page', 'account_history_info');

    // account_newsletters
    $zm_urlMapper->addMapping('account_newsletters', 'page', 'account_newsletters');

    // account_notifications
    $zm_urlMapper->addMapping('account_notifications', 'page', 'account_notifications');

    // account_password
    $zm_urlMapper->addMapping('account_password', 'page', 'account_password');

    // address_book
    $zm_urlMapper->addMapping('address_book', 'page', 'address_book');

    // shopping_cart
    $zm_urlMapper->addMapping('shopping_cart', 'page', 'shopping_cart');
    $zm_urlMapper->addMapping('shopping_cart', 'empty', 'empty_cart');

?>
