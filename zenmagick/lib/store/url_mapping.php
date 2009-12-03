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
     * Set up default URL mappings.
     *
     * <p>The reason for this being wrapped in a function is to make it possible
     * to include in <code>core.php</code>. Also, this leaves the option of
     * alternative storage to improve loading time.</p>
     *
     * @package org.zenmagick.store
     */
    function zm_set_default_url_mappings() {
        $urlMapper = ZMUrlMapper::instance();

        // mappings for shared views and error pages
        $urlMapper->setMappingInfo(null, array('viewId' => 'error'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'missing_page', 'template' => 'error'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'category_not_found', 'template' => 'error'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'product_not_found', 'template' => 'product_not_found'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'index'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'login', 'viewDefinition' => 'RedirectView'));

        // checkout states
        $urlMapper->setMappingInfo(null, array('viewId' => 'empty_cart', 'template' => 'shopping_cart', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'cart_not_ready', 'template' => 'shopping_cart', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'cart_is_virtual', 'template' => 'checkout_payment', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'check_cart', 'template' => 'shopping_cart', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'low_stock"', 'template' => 'shopping_cart', 'viewDefinition' => 'RedirectView'));

        // popups
        $urlMapper->setMappingInfo(null, array('viewId' => 'popup_search_help', 'template' => 'popup/search_help'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'popup_shipping_estimator', 'template' => 'popup/shipping_estimator'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'popup_cvv_help', 'template' => 'popup/cvv_help'));
        $urlMapper->setMappingInfo(null, array('viewId' => 'popup_coupon_help', 'template' => 'popup/coupon_help'));

        // address_book_edit [form]
        $urlMapper->setMappingInfo('address_book_edit', array('formDefinition' => 'Address', 'formId' => 'address'));
        $urlMapper->setMappingInfo('address_book_edit', array('viewId' => 'success', 'template' => 'account', 'viewDefinition' => 'RedirectView', 'formDefinition' => 'Address', 'formId' => 'address'));

        // address_book_add [form]
        $urlMapper->setMappingInfo('address_book_add', array('template' => 'address_book_create', 'formDefinition' => 'Address', 'formId' => 'address'));
        $urlMapper->setMappingInfo('address_book_add', array('viewId' => 'success', 'template' => 'account', 'viewDefinition' => 'RedirectView', 'formDefinition' => 'Address', 'formId' => 'address'));

        // address_book_delete
        $urlMapper->setMappingInfo('address_book_delete', array('template' => 'address_book_delete'));
        $urlMapper->setMappingInfo('address_book_delete', array('viewId' => 'success', 'template' => 'account', 'viewDefinition' => 'RedirectView'));


        // index; support for old style category URLs using index
        $urlMapper->setMappingInfo('index', array('viewId' => 'category', 'template' => 'category', 'viewDefinition' => 'ForwardView'));

        // discount_coupon
        $urlMapper->setMappingInfo('discount_coupon', array('viewId' => 'info', 'template' => 'discount_coupon_info'));

        // parameter dependant category views
        $urlMapper->setMappingInfo('category', array('viewId' => 'category_list', 'template' => 'category_list'));
        $urlMapper->setMappingInfo('category', array('viewId' => 'manufacturer', 'template' => 'manufacturer'));
        $urlMapper->setMappingInfo('category', array('viewId' => 'product_info', 'template' => 'product_info', 'viewDefinition' => 'ForwardView'));

        // login [form]
        $urlMapper->setMappingInfo('login'); // needed to avoid recursive redirects
        $urlMapper->setMappingInfo('login', array('viewId' => 'success', 'template' => 'account', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo('login', array('viewId' => 'account', 'template' => 'account', 'viewDefinition' => 'RedirectView'));

        // logoff
        $urlMapper->setMappingInfo('logoff', array('viewId' => 'success', 'template' => 'logoff', 'viewDefinition' => 'RedirectView'));

        // password_forgotten [form]
        $urlMapper->setMappingInfo('password_forgotten', array('viewId' => 'success', 'template' => 'login', 'viewDefinition' => 'RedirectView'));

        // guest checkout
        $urlMapper->setMappingInfo('checkout_guest', array('template' => 'login'));
        $urlMapper->setMappingInfo('checkout_guest', array('viewId' => 'checkout_guest', 'template' => 'login'));
        $urlMapper->setMappingInfo('checkout_guest', array('viewId' => 'guest_checkout_disabled', 'template' => 'login', 'RedirectView'));
        $urlMapper->setMappingInfo('checkout_guest', array('viewId' => 'success', 'template' => 'checkout_shipping', 'viewDefinition' => 'RedirectView'));

        // guest history
        $urlMapper->setMappingInfo('guest_history', array('viewId' => 'success', 'template' => 'account_history_info'));

        // product_info
        $urlMapper->setMappingInfo('product_info', array('viewId' => 'product_music_info', 'template' => 'product_music_info'));
        //TODO: these should be set dynamically
        $urlMapper->setMappingInfo('product_info', array('viewId' => 'document_general_info', 'template' => 'document_general_info'));
        $urlMapper->setMappingInfo('product_info', array('viewId' => 'document_product_info', 'template' => 'document_product_info'));
        $urlMapper->setMappingInfo('product_info', array('viewId' => 'product_free_shipping_info', 'template' => 'product_free_shipping_info'));

        // gv_redeem
        $urlMapper->setMappingInfo('gv_redeem', array('formDefinition' => 'GVRedeem', 'formId' => 'gvRedeem'));
        $urlMapper->setMappingInfo('gv_faq', array('formDefinition' => 'GVRedeem', 'formId' => 'gvRedeem'));

        // gv_send
        $urlMapper->setMappingInfo('gv_send', array('formDefinition' => 'GVReceiver', 'formId' => 'gvReceiver'));
        $urlMapper->setMappingInfo('gv_send', array('viewId' => 'success', 'template' => 'gv_send_confirm', 'formDefinition' => 'GVReceiver', 'formId' => 'gvReceiver'));
        $urlMapper->setMappingInfo('gv_send_confirm', array('formDefinition' => 'GVReceiver', 'formId' => 'gvReceiver'));
        $urlMapper->setMappingInfo('gv_send_confirm', array('viewId' => 'edit', 'template' => 'gv_send', 'viewDefinition' => 'ForwardView', 'formDefinition' => 'GVReceiver', 'formId' => 'gvReceiver'));
        $urlMapper->setMappingInfo('gv_send_confirm', array('viewId' => 'success', 'template' => 'account', 'viewDefinition' => 'RedirectView', 'formDefinition' => 'GVReceiver', 'formId' => 'gvReceiver'));

        // create account [form]
        $urlMapper->setMappingInfo('create_account', array('template' => 'create_account', 'formDefinition' => 'RegistrationForm', 'formId' => 'registration'));
        $urlMapper->setMappingInfo('create_account', array('viewId' => 'success', 'template' => 'create_account_success', 'viewDefinition' => 'RedirectView', 'formDefinition' => 'RegistrationForm', 'formId' => 'registration'));

        // create account [form]
        $urlMapper->setMappingInfo('account_edit', array('formDefinition' => 'AccountForm', 'formId' => 'account'));
        $urlMapper->setMappingInfo('account_edit', array('viewId' => 'success', 'template' => 'account', 'viewDefinition' => 'RedirectView', 'formDefinition' => 'AccountForm', 'formId' => 'account'));

        // account [forms]
        $urlMapper->setMappingInfo('account_password', array('viewId' => 'success', 'template' => 'account', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo('account_newsletters', array('viewId' => 'success', 'template' => 'account', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo('account_notifications', array('viewId' => 'success', 'template' => 'account', 'viewDefinition' => 'RedirectView'));


        // checkout shipping [form]
        $urlMapper->setMappingInfo('checkout_shipping', array('viewId' => 'success', 'template' => 'checkout_payment', 'viewDefinition' => 'RedirectView'));

        // checkout shipping address [form]
        $urlMapper->setMappingInfo('checkout_shipping_address', array('template' => 'checkout_shipping_address', 'formDefinition' => 'Address', 'formId' => 'shippingAddress', 'controllerDefinition' => 'CheckoutAddressController#mode=shipping'));
        $urlMapper->setMappingInfo('checkout_shipping_address', array('viewId' => 'success', 'template' => 'checkout_shipping', 'viewDefinition' => 'RedirectView'));

        // checkout payment [form]
        $urlMapper->setMappingInfo('checkout_payment', array('viewId' => 'success', 'template' => 'checkout_confirmation', 'viewDefinition' => 'RedirectView'));

        // checkout payment address [form]
        $urlMapper->setMappingInfo('checkout_payment_address', array('template' => 'checkout_payment_address', 'formDefinition' => 'Address', 'formId' => 'billingAddress', 'controllerDefinition' => 'CheckoutAddressController#mode=billing'));
        $urlMapper->setMappingInfo('checkout_payment_address', array('viewId' => 'success', 'template' => 'checkout_payment', 'viewDefinition' => 'RedirectView'));

        // redirect
        $urlMapper->setMappingInfo('redirect', array('viewId' => 'success', 'template' => 'index', 'viewDefinition' => 'RedirectView'));
        $urlMapper->setMappingInfo('redirect', array('viewId' => 'error', 'template' => 'index', 'viewDefinition' => 'ForwardView'));

        // review [form]
        $urlMapper->setMappingInfo('product_reviews_write', array('template' => 'product_reviews_write', 'formDefinition' => 'Review', 'formId' => 'newReview'));
        $urlMapper->setMappingInfo('product_reviews_write', array('viewId' => 'success', 'template' => 'product_reviews', 'viewDefinition' => 'RedirectView'));

        // contact us [form]
        $urlMapper->setMappingInfo('contact_us', array('template' => 'contact_us', 'formDefinition' => 'ContactInfo', 'formId' => 'contactUs'));

        // tell a friend [form]
        $urlMapper->setMappingInfo('tell_a_friend', array('template' => 'tell_a_friend', 'formDefinition' => 'EmailMessage', 'formId' => 'tellAFriend'));
        $urlMapper->setMappingInfo('tell_a_friend', array('viewId' => 'success', 'template' => 'product_info', 'viewDefinition' => 'RedirectView'));

        // search [forms]
        $urlMapper->setMappingInfo('search', array('formDefinition' => 'SearchCriteria', 'formId' => 'searchCriteria'));
        $urlMapper->setMappingInfo('advanced_search', array('template' => 'advanced_search', 'controllerDefinition' => 'SearchController#autoSearch=false', 'formDefinition' => 'SearchCriteria', 'formId' => 'searchCriteria'));

        // shopping cart
        $urlMapper->setMappingInfo('shopping_cart', array('viewId' => 'empty_cart', 'template' => 'empty_cart'));
    }

?>
