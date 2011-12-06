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
        $request->getToolbox()->crumbtrail->addCrumb("Checkout", $request->url('checkout_payment', '', true));
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

        if (!$checkoutHelper->verifyHash($request)) {
            return $this->findView('check_cart');
        }

        if (null !== ($viewId = $checkoutHelper->validateCheckout($request, false))) {
            return $this->findView($viewId);
        }
        if (null !== ($viewId = $checkoutHelper->validateAddresses($request, true))) {
            return $this->findView($viewId);
        }

        // TODO: add check if payment is needed at all (if subtotal is zero we don't need payment?)

        return $this->findView();
    }

    /**
     * {@inheritDoc}
     *
     * <p><strong>NOTE: This is currently not used as the payments form points to checkout_confirmation</strong>.</p>
     */
    public function processPost($request) {
        $shoppingCart = $request->getShoppingCart();
        $checkoutHelper = Runtime::getContainer()->get('ZMCheckoutHelper');
        $checkoutHelper->setShoppingCart($shoppingCart);

        if (!$checkoutHelper->verifyHash($request)) {
            return $this->findView('check_cart');
        }

        if (null !== ($viewId = $checkoutHelper->validateCheckout($request, false))) {
            return $this->findView($viewId);
        }
        if (null !== ($viewId = $checkoutHelper->validateAddresses($request, true))) {
            return $this->findView($viewId);
        }

        if (null != ($comments = $request->getParameter('comments'))) {
            $shoppingCart->setComments($comments);
        }

        if (ZMSettings::get('isConditionsMessage') && !ZMLangUtils::asBoolean($request->getParameter('conditions'))) {
            $this->messageService->error(_zm('Please confirm the terms and conditions bound to this order by ticking the box below.'));
            return $this->findView();
        }

        // TODO: check if credit/gv covers total (currently in order_total::pre_confirmation_check)

        if (null == ($paymentTypeId = $request->getParameter('payment'))) {
            $this->messageService->error(_zm('Please select a payment type.'));
            return $this->findView();
        }

        if (null == ($paymentType = $this->container->get('paymentTypeService')->getPaymentTypeForId($paymentTypeId))) {
            $this->messageService->error(_zm('Please select a valid payment type.'));
            return $this->findView();
        }

        $shoppingCart->setSelectedPaymentType($paymentType);

        // TODO: update customer referral discount coupon (??)
        // TODO: add support for custom 'edit shipping url' in confirmation page (should be in ZMCheckoutConfirmation:processGet(), I guess) [implemented but inactive in paypalpww]
        //       [also needed in ZMCheckoutShippingController -> checkout helper?]
        // TODO: add support for 'flag disable address payment' [again, implemented (and used, this time) in paypalpww]

        // checkout_confirmations header_php.php needs conditions=1 in $_POST!! - see also the fix in ZMEventFixes/onZMInitDone
        return $this->findView('success', array(), array('parameter' => 'conditions=1'));
    }

}
