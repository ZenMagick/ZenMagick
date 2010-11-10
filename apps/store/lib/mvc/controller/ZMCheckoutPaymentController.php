<?php
/*
 * ZenMagick - Smart e-commerce
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
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMCheckoutPaymentController extends ZMController {

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
        $request->getToolbox()->crumbtrail->addCrumb("Checkout", $request->url(FILENAME_CHECKOUT_PAYMENT, '', true));
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

        if (null !== ($viewId = $checkoutHelper->validateCheckout($request, false))) {
            return $this->findView($viewId);
        }
        if (null !== ($viewId = $checkoutHelper->validateAddresses($request, true))) {
            return $this->findView($viewId);
        }

        if (!$checkoutHelper->verifyHash($request)) {
            return $this->findView('check_cart');
        }

        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        //TODO: move processing here!
    }

}
