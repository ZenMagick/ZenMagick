<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;

/**
 * Request controller for checkout shipping page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.sf.mvc.controller
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
        $request->getToolbox()->crumbtrail->addCrumb("Checkout", $request->url('checkout_shipping', '', true));
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());
    }

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        return array('shoppingCart' => $request->getShoppingCart());
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $shoppingCart = $request->getShoppingCart();
        $checkoutHelper = Runtime::getContainer()->get('ZMCheckoutHelper');
        $checkoutHelper->setShoppingCart($shoppingCart);

        // set cart hash
        if (!$checkoutHelper->saveHash($request)) {
            return $this->findView('check_cart');
        }

        if (null !== ($viewId = $checkoutHelper->validateCheckout($request, false)) && 'require_shipping' != $viewId) {
            return $this->findView($viewId);
        }
        if (null !== ($viewId = $checkoutHelper->validateAddresses($request, true))) {
            return $this->findView($viewId);
        }

        if ($checkoutHelper->isVirtual()) {
            $checkoutHelper->markCartFreeShipping();
            return $this->findView('skip_shipping');
        }

        //TODO: preselect shipping
        // a) something to preselect free shipping as per ot_freeshipper
        // b) is a preferred option configured via setting??
        // c) cheapest except storepickup

        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $shoppingCart = $request->getShoppingCart();
        $checkoutHelper = Runtime::getContainer()->get('ZMCheckoutHelper');
        $checkoutHelper->setShoppingCart($shoppingCart);

        if (!$checkoutHelper->verifyHash($request)) {
            return $this->findView('check_cart');
        }

        if (null !== ($viewId = $checkoutHelper->validateCheckout($request, false)) && 'require_shipping' != $viewId) {
            return $this->findView($viewId);
        }
        if (null !== ($viewId = $checkoutHelper->validateAddresses($request, true))) {
            return $this->findView($viewId);
        }

        if ($checkoutHelper->isVirtual()) {
            $checkoutHelper->markCartFreeShipping();
            return $this->findView('skip_shipping');
        }

        if (null != ($comments = $request->getParameter('comments'))) {
            $shoppingCart->setComments($comments);
        }

        // process selected shipping method
        $shipping = $request->getParameter('shipping');
        list($providerName, $methodName) = explode('_', $shipping);
        if (null != ($shippingProvider = $this->container->get('shippingProviderService')->getShippingProviderForId($providerName))) {
            $shippingMethod = $shippingProvider->getShippingMethodForId($methodName, $shoppingCart, $shoppingCart->getShippingAddress());
        }

        if (null == $shippingProvider || null == $shippingMethod) {
            $this->messageService->error(_zm('Please select a shipping method.'));
            return $this->findView();
        }

        $shoppingCart->setSelectedShippingMethod($shippingMethod);

        return $this->findView('success');
    }

}
