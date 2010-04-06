<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 */
?>
<?php


/**
 * Request controller for checkout shipping page.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id$
 */
class ZMCheckoutShippingController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        $shoppingCart = $request->getShoppingCart();
        // set default address if required
        if (!$shoppingCart->hasShippingAddress()) {
            $account = $request->getAccount();
            $shoppingCart->setShippingAddressId($account->getDefaultAddresssId());
            // TODO: reset selected shipping method as address changed (if addressId set in session is invalid)
        }

        $request->getToolbox()->crumbtrail->addCrumb("Checkout", $request->url(FILENAME_CHECKOUT_SHIPPING, '', true));
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());
    }

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        return array(
          'shoppingCart' => $request->getShoppingCart(),
          'comments' => $request->getParameter('comments', $request->getSession()->getValue('comments'))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $shoppingCart = $request->getShoppingCart();
        $checkoutHelper = ZMLoader::make('CheckoutHelper', $shoppingCart);

        if (null !== ($viewId = $checkoutHelper->validateCheckout(false))) {
            return $this->findView($viewId);
        }

        if (!$checkoutHelper->verifyHash($request)) {
            return $this->findView('check_cart');
        }

        if ($checkoutHelper->isVirtual()) {
            // TODO: set free shipping
            $_SESSION['shipping'] = 'free_free';
            $_SESSION['sendto'] = false;
            return $this->findView('skip_shipping');
        }

        // already checked that cart is not virtual
        if (null == $shoppingCart->getShippingAddress()) {
            $shoppingCart->setShippingAddressId($request->getAccount()->getDefaultAddressId());
        }

        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $shoppingCart = $request->getShoppingCart();
        $checkoutHelper = ZMLoader::make('CheckoutHelper', $shoppingCart);

        if (null !== ($viewId = $checkoutHelper->validateCheckout(false))) {
            return $this->findView($viewId);
        }

        if (!$checkoutHelper->verifyHash($request)) {
            return $this->findView('check_cart');
        }

        if ($checkoutHelper->isVirtual()) {
            // TODO: set free shipping
            $_SESSION['shipping'] = 'free_free';
            $_SESSION['sendto'] = false;
            return $this->findView('skip_shipping');
        }

        $comments = $request->getSession()->getValue('comments');
        if (null != ($comments = $request->getParameter('comments'))) {
            $request->getSession()->setValue('comments', $comments);
        }

        // process selected shipping method
        $shipping = $request->getParameter('shipping');
        list($provider, $method) = explode('_', $request->getParameter('shipping'));
        $shippingProvider = ZMShippingProviders::instance()->getShippingProviderForId($method);
        $shippingMethod = $shippingProvider->getShippingMethodForId($method, $shoppingCart, $shoppingCart->getShippingAddress());

        if (empty($provider) || empty($method) || null == $shippingMethod) {
            ZMMessages::instance()->error(zm_l10n_get('Please select a shipping method.'));
        }

        $shoppingCart->setShippingMethod($shippingMethod);

        return $this->findView('success');
    }

}
