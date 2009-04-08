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

        // address_book_edit [form]
        $urlMapper->setMappingInfo('address_book_edit', array('formDefinition' => 'Address', 'formId' => 'address'));
        $urlMapper->setMappingInfo('address_book_edit', array('viewId' => 'success', 'view' => 'account', 'viewDefinition' => 'RedirectView', 'formDefinition' => 'Address', 'formId' => 'address'));

        // address_book_add [form]
        $urlMapper->setMappingInfo('address_book_add', array('view' => 'address_book_create', 'formDefinition' => 'Address', 'formId' => 'address'));
        $urlMapper->setMappingInfo('address_book_add', array('viewId' => 'success', 'view' => 'account', 'viewDefinition' => 'RedirectView', 'formDefinition' => 'Address', 'formId' => 'address'));

        // address_book_delete
        $urlMapper->setMappingInfo('address_book_delete', array('view' => 'address_book_delete'));
        $urlMapper->setMappingInfo('address_book_delete', array('viewId' => 'success', 'view' => 'account', 'viewDefinition' => 'RedirectView'));


        // index; support for old style category URLs using index
        $urlMapper->setMappingInfo('index', array('viewId' => 'category', 'view' => 'category', 'viewDefinition' => 'ForwardView'));

        // discount_coupon
        $urlMapper->setMappingInfo('discount_coupon', array('viewId' => 'info', 'view' => 'discount_coupon_info'));

        // parameter dependant category views
        $urlMapper->setMappingInfo('category', array('viewId' => 'category_list', 'view' => 'category_list'));
        $urlMapper->setMappingInfo('category', array('viewId' => 'manufacturer', 'view' => 'manufacturer'));
        $urlMapper->setMappingInfo('category', array('viewId' => 'product_info', 'view' => 'product_info', 'viewDefinition' => 'ForwardView'));

        // login [form]
        $urlMapper->setMappingInfo('login'); // needed to avoid recursive redirects
        $urlMapper->setMappingInfo('login', array('viewId' => 'success', 'view' => 'account', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo('login', array('viewId' => 'account', 'view' => 'account', 'viewDefinition' => 'RedirectView'));

        // logoff
        $urlMapper->setMappingInfo('logoff', array('viewId' => 'success', 'view' => 'logoff', 'viewDefinition' => 'RedirectView'));

        // password_forgotten [form]
        $urlMapper->setMappingInfo('password_forgotten', array('viewId' => 'success', 'view' => 'login', 'viewDefinition' => 'RedirectView'));

        // guest checkout
        $urlMapper->setMappingInfo('checkout_guest', array('view' => 'login'));
        $urlMapper->setMappingInfo('checkout_guest', array('viewId' => 'checkout_guest', 'view' => 'login'));
        $urlMapper->setMappingInfo('checkout_guest', array('viewId' => 'guest_checkout_disabled', 'view' => 'login', 'RedirectView'));
        $urlMapper->setMappingInfo('checkout_guest', array('viewId' => 'success', 'view' => 'checkout_shipping', 'viewDefinition' => 'RedirectView'));

        // guest history
        $urlMapper->setMappingInfo('guest_history', array('viewId' => 'success', 'view' => 'account_history_info'));

        // product_info
        $urlMapper->setMappingInfo('product_info', array('viewId' => 'product_music_info', 'view' => 'product_music_info'));
        //TODO: these should be set dynamically
        $urlMapper->setMappingInfo('product_info', array('viewId' => 'document_general_info', 'view' => 'document_general_info'));
        $urlMapper->setMappingInfo('product_info', array('viewId' => 'document_product_info', 'view' => 'document_product_info'));
        $urlMapper->setMappingInfo('product_info', array('viewId' => 'product_free_shipping_info', 'view' => 'product_free_shipping_info'));

        // gv_redeem
        $urlMapper->setMappingInfo('gv_redeem', array('formDefinition' => 'GVRedeem', 'formId' => 'gvRedeem'));
        $urlMapper->setMappingInfo('gv_faq', array('formDefinition' => 'GVRedeem', 'formId' => 'gvRedeem'));

        // gv_send
        $urlMapper->setMappingInfo('gv_send', array('formDefinition' => 'GVReceiver', 'formId' => 'gvReceiver'));
        $urlMapper->setMappingInfo('gv_send', array('viewId' => 'success', 'view' => 'gv_send_confirm', 'formDefinition' => 'GVReceiver', 'formId' => 'gvReceiver'));
        $urlMapper->setMappingInfo('gv_send_confirm', array('formDefinition' => 'GVReceiver', 'formId' => 'gvReceiver'));
        $urlMapper->setMappingInfo('gv_send_confirm', array('viewId' => 'edit', 'view' => 'gv_send', 'viewDefinition' => 'ForwardView', 'formDefinition' => 'GVReceiver', 'formId' => 'gvReceiver'));
        $urlMapper->setMappingInfo('gv_send_confirm', array('viewId' => 'success', 'view' => 'account', 'viewDefinition' => 'RedirectView', 'formDefinition' => 'GVReceiver', 'formId' => 'gvReceiver'));

        // create account [form]
        $urlMapper->setMappingInfo('create_account', array('view' => 'create_account', 'formDefinition' => 'RegistrationForm', 'formId' => 'registration'));
        $urlMapper->setMappingInfo('create_account', array('viewId' => 'success', 'view' => 'create_account_success', 'viewDefinition' => 'RedirectView', 'formDefinition' => 'RegistrationForm', 'formId' => 'registration'));

        // create account [form]
        $urlMapper->setMappingInfo('account_edit', array('formDefinition' => 'AccountForm', 'formId' => 'account'));
        $urlMapper->setMappingInfo('account_edit', array('viewId' => 'success', 'view' => 'account', 'viewDefinition' => 'RedirectView', 'formDefinition' => 'AccountForm', 'formId' => 'account'));

        // account [forms]
        $urlMapper->setMappingInfo('account_password', array('viewId' => 'success', 'view' => 'account', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo('account_newsletters', array('viewId' => 'success', 'view' => 'account', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo('account_notifications', array('viewId' => 'success', 'view' => 'account', 'viewDefinition' => 'RedirectView'));


        // checkout shipping [form]
        $urlMapper->setMappingInfo('checkout_shipping', array('viewId' => 'success', 'view' => 'checkout_billing', 'viewDefinition' => 'RedirectView'));

        // checkout shipping address [form]
        $urlMapper->setMappingInfo('checkout_shipping_address', array('view' => 'checkout_shipping_address', 'formDefinition' => 'Address', 'formId' => 'shippingAddress', 'controllerDefinition' => 'CheckoutAddressController#mode=shipping'));
        $urlMapper->setMappingInfo('checkout_shipping_address', array('viewId' => 'success', 'view' => 'checkout_shipping', 'viewDefinition' => 'RedirectView'));

        // checkout payment [form]
        $urlMapper->setMappingInfo('checkout_payment', array('viewId' => 'success', 'view' => 'checkout_confirmation', 'viewDefinition' => 'RedirectView'));

        // checkout payment address [form]
        $urlMapper->setMappingInfo('checkout_payment_address', array('view' => 'checkout_payment_address', 'formDefinition' => 'Address', 'formId' => 'billingAddress', 'controllerDefinition' => 'CheckoutAddressController#mode=billing'));
        $urlMapper->setMappingInfo('checkout_payment_address', array('viewId' => 'success', 'view' => 'checkout_payment', 'viewDefinition' => 'RedirectView'));

        // redirect
        $urlMapper->setMappingInfo('redirect', array('viewId' => 'success', 'view' => 'index', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo('redirect', array('viewId' => 'error', 'view' => 'index', 'viewDefinition' => 'ForwardView'));

        // review [form]
        $urlMapper->setMappingInfo('product_reviews_write', array('view' => 'product_reviews_write', 'formDefinition' => 'Review', 'formId' => 'review'));
        $urlMapper->setMappingInfo('product_reviews_write', array('viewId' => 'success', 'view' => 'product_reviews', 'viewDefinition' => 'RedirectView'));

        // contact us [form]
        $urlMapper->setMappingInfo('contact_us', array('view' => 'contact_us', 'formDefinition' => 'ContactInfo', 'formId' => 'contactUs'));

        // tell a friend [form]
        $urlMapper->setMappingInfo('tell_a_friend', array('view' => 'tell_a_friend', 'formDefinition' => 'EmailMessage', 'formId' => 'tellAFriend'));
        $urlMapper->setMappingInfo('tell_a_friend', array('viewId' => 'success', 'view' => 'product_info', 'viewDefinition' => 'RedirectView'));

        // search [forms]
        $urlMapper->setMappingInfo('search', array('formDefinition' => 'SearchCriteria', 'formId' => 'searchCriteria'));
        $urlMapper->setMappingInfo('advanced_search', array('view' => 'advanced_search', 'controllerDefinition' => 'SearchController#autoSearch=false', 'formDefinition' => 'SearchCriteria', 'formId' => 'searchCriteria'));
    }

?>
