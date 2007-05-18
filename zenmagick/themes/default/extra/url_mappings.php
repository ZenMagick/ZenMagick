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
     */

    // global
    $zm_urlMapper->addMapping(null, 'error', 'error');
    $zm_urlMapper->addMapping(null, 'index', 'index');
    $zm_urlMapper->addMapping(null, 'login', 'login');


    // address_book_process
    $zm_urlMapper->addMapping('address_book_process', 'address_book_create', 'address_book_create');
    $zm_urlMapper->addMapping('address_book_process', 'address_book_edit', 'address_book_edit');
    $zm_urlMapper->addMapping('address_book_process', 'address_book_delete', 'address_book_delete');

    // advanced_search
    $zm_urlMapper->addMapping('advanced_search');

    // advanced_search_result
    $zm_urlMapper->addMapping('advanced_search_result');

    // checkout_confirmation
    $zm_urlMapper->addMapping('checkout_confirmation');

    // checkout_payment_address
    $zm_urlMapper->addMapping('checkout_payment_address');

    // checkout_payment
    $zm_urlMapper->addMapping('checkout_payment');

    // checkout_shipping_address
    $zm_urlMapper->addMapping('checkout_shipping_address');

    // checkout_shipping
    $zm_urlMapper->addMapping('checkout_shipping');

    // checkout_success
    $zm_urlMapper->addMapping('checkout_success');

    // contact_us
    $zm_urlMapper->addMapping('contact_us');
    //TODO: the redirect is in still in zen-cart code
    $zm_urlMapper->addMapping('contact_us_success');
    $zm_urlMapper->addMapping('contact_us', 'contact_us_success', 'success', true, true);

    // create_account
    $zm_urlMapper->addMapping('create_account');

    // discount_coupon
    $zm_urlMapper->addMapping('discount_coupon');
    $zm_urlMapper->addMapping('discount_coupon', 'discount_coupon_info', 'info');

    // featured_products
    $zm_urlMapper->addMapping('featured_products');

    // gv_send
    $zm_urlMapper->addMapping('gv_send');
    $zm_urlMapper->addMapping('gv_send', 'gv_send_confirm', 'confirm', true, true);
    $zm_urlMapper->addMapping('gv_send', 'account', 'success', true, true);

    // index
    $zm_urlMapper->addMapping('index', 'category', 'category');
    $zm_urlMapper->addMapping('index', 'category_list', 'category_list');
    $zm_urlMapper->addMapping('index', 'manufacturer', 'manufacturer');
    // index is configured global

    // login
    $zm_urlMapper->addMapping('login', 'login', null, false, true);
    $zm_urlMapper->addMapping('login', 'account', 'success', true, true);

    // logoff
    $zm_urlMapper->addMapping('logoff', 'logoff', 'success', false, true);

    // site_map
    $zm_urlMapper->addMapping('site_map');

    // page
    $zm_urlMapper->addMapping('page');

    // password_forgotten
    $zm_urlMapper->addMapping('password_forgotten');
    $zm_urlMapper->addMapping('password_forgotten', 'login', 'success', true, true);

    // product_comparison
    $zm_urlMapper->addMapping('product_comparison');

    // product_info
    $zm_urlMapper->addMapping('product_info');
    $zm_urlMapper->addMapping('product_info', 'product_music_info', 'product_music_info');
    $zm_urlMapper->addMapping('product_info', 'document_general_info', 'document_general_info');
    $zm_urlMapper->addMapping('product_info', 'document_product_info', 'document_product_info');
    $zm_urlMapper->addMapping('product_info', 'product_free_shipping_info', 'product_free_shipping_info');

    // product_reviews
    $zm_urlMapper->addMapping('product_reviews');

    // product_reviews_info
    $zm_urlMapper->addMapping('product_reviews_info');

    // product_reviews_write
    $zm_urlMapper->addMapping('product_reviews_write');

    // products_new
    $zm_urlMapper->addMapping('products_new');

    // products_new
    $zm_urlMapper->addMapping('specials');

    // reviews
    $zm_urlMapper->addMapping('reviews');

    // static
    $zm_urlMapper->addMapping('static');

    // tell_a_friend
    $zm_urlMapper->addMapping('tell_a_friend');

    // account
    $zm_urlMapper->addMapping('account');

    // account_edit
    $zm_urlMapper->addMapping('account_edit');
    $zm_urlMapper->addMapping('account_edit', 'account', 'success', true, true);

    // account_history
    $zm_urlMapper->addMapping('account_history');

    // account_history_info
    $zm_urlMapper->addMapping('account_history_info');

    // account_newsletters
    $zm_urlMapper->addMapping('account_newsletters');

    // account_notifications
    $zm_urlMapper->addMapping('account_notifications');

    // account_password
    $zm_urlMapper->addMapping('account_password');
    $zm_urlMapper->addMapping('account_password', 'account', 'success', true, true);

    // address_book
    $zm_urlMapper->addMapping('address_book');

    // shopping_cart
    $zm_urlMapper->addMapping('shopping_cart');
    $zm_urlMapper->addMapping('shopping_cart', 'empty_cart', 'empty_cart');

?>
