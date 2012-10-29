<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
namespace ZenMagick\StorefrontBundle\Controller;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;

/**
 * Request controller for checkout shipping page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 *
 * @todo implement referral coupon logic ?
 * @todo hide shipping address edit if specified by alterShippingEditButton() method
 * @todo hide payment address edit if specified by flagDisablePaymentAddressChange property
 *
 */
class CheckoutConfirmationController extends \ZMController
{
    public function getViewData($request)
    {
        $orderFormContent =  '';
        $orderFormUrl = $this->get('netTool')->url('checkout_process', '', true);

        $shoppingCart = $this->get('shoppingCart');
        if (null != ($paymentType = $shoppingCart->getSelectedPaymentType())) {
            $orderFormContent = $paymentType->getOrderFormContent($request);
            $orderFormUrl = $paymentType->getOrderFormUrl($request);
        }

        return array('shoppingCart' => $shoppingCart, 'orderFormContent' => $orderFormContent, 'orderFormUrl' => $orderFormUrl);
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        $shoppingCart = $this->get('shoppingCart');
        $checkoutHelper = $shoppingCart->getCheckoutHelper();
        $settingsService = $this->container->get('settingsService');

        if (!$checkoutHelper->verifyHash($request)) {
            return $this->findView('check_cart');
        }

        if ('free_free' == $_SESSION['shipping']) { // <johnny> When does this actually happen?
            Runtime::getLogging()->warn('fixing free_free shipping method info');
            $_SESSION['shipping'] = array('title' => _zm('Free Shipping'), 'cost' => 0, 'id' => 'free_free');
        }

        if (null !== ($viewId = $checkoutHelper->validateCheckout($request, false))) {
            return $this->findView($viewId);
        }
        if (null !== ($viewId = $checkoutHelper->validateAddresses($request, true))) {
            return $this->findView($viewId);
        }

        if (null != ($comments = $request->request->get('comments'))) {
            $shoppingCart->setComments($comments);
        }

        if ($settingsService->get('isConditionsMessage') && !Toolbox::asBoolean($request->request->get('conditions'))) {
            $this->messageService->error(_zm('Please confirm the terms and conditions bound to this order by ticking the box below.'));

            return $this->findView();
        }

        if (null != ($paymentMethod = $request->request->get('payment'))) {
            $request->getSession()->set('payment', $paymentMethod);
        }

        return $this->processGet($request);
    }
}
