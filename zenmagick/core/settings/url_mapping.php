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
        $urlMapper->setMappingInfo(null, array('viewId' => 'error'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'missing_page', 'view' => 'error'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'category_not_found', 'view' => 'error'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'product_not_found', 'view' => 'product_not_found'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'index'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'login', 'viewDefinition' => 'RedirectView'));

        // checkout states
        $urlMapper->setMappingInfo(null, array('viewId' => 'empty_cart', 'view' => 'shopping_cart', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'cart_not_ready', 'view' => 'shopping_cart', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'cart_is_virtual', 'view' => 'checkout_billing', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'check_cart', 'view' => 'shopping_cart', 'viewDefinition' => 'RedirectView'));

        // popups
        $urlMapper->setMappingInfo(null, array('viewId' => 'popup_search_help', 'viewDefinition' => 'PageView#subdir=popup'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'popup_shipping_estimator', 'viewDefinition' => 'PageView#subdir=popup'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'popup_cvv_help', 'viewDefinition' => 'PageView#subdir=popup'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'popup_coupon_help', 'viewDefinition' => 'PageView#subdir=popup'));

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
        $urlMapper->setMappingInfo('login'); // needed to avoid recursive redirects
        $urlMapper->setMappingInfo('login', array('viewId' => 'success', 'view' => 'account', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo('login', array('viewId' => 'account', 'view' => 'account', 'viewDefinition' => 'RedirectView'));

        // logoff
        $urlMapper->setMappingInfo('logoff', array('viewId' => 'success', 'view' => 'logoff', 'viewDefinition' => 'RedirectView'));

        // password_forgotten [form]
        $urlMapper->setMapping('password_forgotten', 'success', 'login', 'RedirectView');

        // guest checkout
        $urlMapper->setMapping('checkout_guest', 'login');
        $urlMapper->setMapping('checkout_guest', 'checkout_guest', 'login');
        $urlMapper->setMapping('checkout_guest', 'guest_checkout_disabled', 'login', 'RedirectView');
        $urlMapper->setMapping('checkout_guest', 'success', 'checkout_shipping', 'RedirectView');

        // guest history
        $urlMapper->setMapping('guest_history', 'success', 'account_history_info');

        // product_info
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

        // review [form]
        $urlMapper->setMappingInfo('product_reviews_write', array('view' => 'product_reviews_write', 'formDefinition' => 'ZMReview', 'formId' => 'review'));
        $urlMapper->setMappingInfo('product_reviews_write', array('viewId' => 'success', 'view' => 'product_reviews', 'viewDefinition' => 'RedirectView'));

        // contact us [form]
        $urlMapper->setMappingInfo('contact_us', array('view' => 'contact_us', 'formDefinition' => 'ZMContactInfo', 'formId' => 'contactUs'));

        // tell a friend [form]
        $urlMapper->setMappingInfo('tell_a_friend', array('view' => 'tell_a_friend', 'formDefinition' => 'ZMEmailMessage', 'formId' => 'tellAFriend'));
        $urlMapper->setMappingInfo('tell_a_friend', array('viewId' => 'success', 'view' => 'product_info', 'viewDefinition' => 'RedirectView'));

        // search [forms]
        $urlMapper->setMappingInfo('search', array('formDefinition' => 'ZMSearchCriteria', 'formId' => 'searchCriteria'));
        $urlMapper->setMappingInfo('advanced_search', array('view' => 'advanced_search', 'controllerDefinition' => 'SearchController#autoSearch=false', 'formDefinition' => 'ZMSearchCriteria', 'formId' => 'searchCriteria'));
    }

?>
