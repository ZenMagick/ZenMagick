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
     * NOTE: If controller name and view name correspond, no entry is required.
     */

    // global
    $zm_urlMapper->addMapping(null, 'error', 'error');
    $zm_urlMapper->addMapping(null, 'error', 'missing_page');
    $zm_urlMapper->addMapping(null, 'index', 'index');
    $zm_urlMapper->addMapping(null, 'login', 'login', false, true);
    $zm_urlMapper->addMapping(null, 'cookie_usage', 'cookie_usage', true, false);


    // address_book_process
    $zm_urlMapper->addMapping('address_book_process', 'address_book_create', 'address_book_create');
    $zm_urlMapper->addMapping('address_book_process', 'address_book_edit', 'address_book_edit');
    $zm_urlMapper->addMapping('address_book_process', 'address_book_delete', 'address_book_delete');
    $zm_urlMapper->addMapping('address_book_process', 'address_book', 'success', true, true);

    // contact_us
    $zm_urlMapper->addMapping('contact_us');
    $zm_urlMapper->addMapping('contact_us', 'contact_us_success', 'success', true, true);

    // discount_coupon
    $zm_urlMapper->addMapping('discount_coupon');
    $zm_urlMapper->addMapping('discount_coupon', 'discount_coupon_info', 'info');

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
    $zm_urlMapper->addMapping('login', 'account', 'account', true, true);

    // password_forgotten
    $zm_urlMapper->addMapping('password_forgotten');
    $zm_urlMapper->addMapping('password_forgotten', 'login', 'success', true, true);

    // product_info
    $zm_urlMapper->addMapping('product_info');
    $zm_urlMapper->addMapping('product_info', 'product_music_info', 'product_music_info');
    $zm_urlMapper->addMapping('product_info', 'document_general_info', 'document_general_info');
    $zm_urlMapper->addMapping('product_info', 'document_product_info', 'document_product_info');
    $zm_urlMapper->addMapping('product_info', 'product_free_shipping_info', 'product_free_shipping_info');

    // account_edit
    $zm_urlMapper->addMapping('account_edit');
    $zm_urlMapper->addMapping('account_edit', 'account', 'success', true, true);

    // account_password
    $zm_urlMapper->addMapping('account_password');
    $zm_urlMapper->addMapping('account_password', 'account', 'success', true, true);

    // shopping_cart
    $zm_urlMapper->addMapping('shopping_cart');
    $zm_urlMapper->addMapping('shopping_cart', 'empty_cart', 'empty_cart');

    // create_account
    $zm_urlMapper->addMapping('create_account');
    $zm_urlMapper->addMapping('create_account', 'account', 'success', true, true);

?>
