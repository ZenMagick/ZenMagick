<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Fixes that are event driven.
 *
 * @author DerManoMann
 * @package org.zenmagick.misc
 * @version $Id$
 */
class ZMEventFixes extends ZMObject {

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
     * Generic zen-cart event observer.
     *
     * <p>Implemented to generate some ZenMagick events triggered by zen-cart events.</p>
     */
    public function update($eventId, $args) {
        if (!ZMsettings::get('isEnableZMThemes')) {
            if (0 === strpos($eventId, 'NOTIFY_HEADER_START_')) {
                $controllerId = str_replace('NOTIFY_HEADER_START_', '', $eventId);
                ZMEvents::instance()->fireEvent($this, ZMEvents::CONTROLLER_PROCESS_START, array('controllerId' => $controllerId));
            } else if (0 === strpos($eventId, 'NOTIFY_HEADER_END_')) {
                $controllerId = str_replace('NOTIFY_HEADER_END_', '', $eventId);
                ZMEvents::instance()->fireEvent($this, ZMEvents::CONTROLLER_PROCESS_END, array('controllerId' => $controllerId));
            }
        }
    }

    /**
     * Fake theme resolved event if using zen-cart templates.
     */
    public function onZMInitDone() {
        if (!ZMsettings::get('isEnableZMThemes')) {
            ZMEvents::instance()->fireEvent(null, ZMEvents::THEME_RESOLVED, array('themeId' => ZMThemes::instance()->getZCThemeId()));
        }
    }

    /**
     * Validate addresses for guest checkout.
     */
    public function onNotifyHeaderEndCheckoutConfirmation() {
        $session = ZMRequest::getSession();
        $shoppingCart = ZMRequest::getShoppingCart();
        if ($session->isGuest()) {
            // check for address
            if (!$shoppingCart->hasShippingAddress() && !$shoppingCart->isVirtual()) {
                ZMMessages::instance()->error(zm_l10n_get('Please provide a shipping address'));
                ZMRequest::redirect(ZMToolbox::instance()->net->url(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', true, false));
            }

            if (null == $shoppingCart->getBillingAddress()) {
                ZMMessages::instance()->error(zm_l10n_get('Please provide a billing address'));
                ZMRequest::redirect(ZMToolbox::instance()->net->url(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', true, false));
            }
        }
    }

    /**
     * Remove ajax requests from navigation history.
     */
    public function onZMDispatchStart() {
        if (false !== strpos(ZMRequest::getPageName(), 'ajax')) {
            $_SESSION['navigation']->remove_current_page();
        }
    }


    /**
     * Fix email context for various emails.
     */
    public function onZMGenerateEmail($args=array()) {
        $context = $args['context'];
        $template = $args['template'];
        $controller = $args['controller'];

        if (ZMSettings::get('isAdmin') && 'send_email_to_user' == ZMRequest::getParameter('action')) {
            // gv mail
            if ($context['GV_REDEEM']) {
                if (1 == preg_match('/.*strong>(.*)<\/strong.*/', $context['GV_REDEEM'], $matches)) {
                    $couponCode = trim($matches[1]);
                    $coupon = ZMCoupons::instance()->getCouponForCode($couponCode);
                    if (null == $coupon) {
                        // coupon gets created only *after* the email is sent!
                        $coupon = ZMLoader::make('Coupon', 0, $couponCode, ZMCoupons::TYPPE_GV);
                        $currency = ZMCurrencies::instance()->getCurrencyForCode(ZMSettings::get('defaultCurrency'));
                        // XXX: update with setter
                        $coupon->amount_ = $currency->parse($context['GV_WORTH']);
                    }
                    $controller->exportGlobal('zm_coupon', $coupon);
                }

                $controller->exportGlobal('message', ZMRequest::getParameter('message', ''));
                $controller->exportGlobal('htmlMessage', ZMRequest::getParameter('message_html', '', false));
            }
        }

        if ('checkout' == $template) {
            $order = ZMOrders::instance()->getOrderForId($context['INTRO_ORDER_NUMBER']);
            $shippingAddress = $order->getShippingAddress();
            $billingAddress = $order->getBillingAddress();
            $paymentType = $order->getPaymentType();

            $controller->exportGlobal('order', $order);
            $controller->exportGlobal('shippingAddress', $shippingAddress);
            $controller->exportGlobal('billingAddress', $billingAddress);
            $controller->exportGlobal('paymentType', $paymentType);
        }

        if ('order_status' == $template) {
            $newOrderStatus = $context['EMAIL_TEXT_NEW_STATUS'];
            preg_match('/[^:]*:(.*)/ms', $context['EMAIL_TEXT_STATUS_COMMENTS'], $matches);
            $comment = strip_tags(trim($matches[1]));

            $controller->exportGlobal('newOrderStatus', $newOrderStatus);
            $controller->exportGlobal('comment', $comment);

            // from zc_fixes
            if (null !== ZMRequest::getParameter("oID") && 'update_order' == ZMRequest::getParameter("action")) {
                $orderId = ZMRequest::getParameter("oID");
                $order = ZMOrders::instance()->getOrderForId($orderId);
                $controller->exportGlobal('zm_order', $order);
                $account = ZMAccounts::instance()->getAccountForId($order->getAccountId());
                $controller->exportGlobal('zm_account', $account);
            }
        }

        if ('gv_queue' == $template) {
            $queueId = ZMRequest::getParameter('gid');
            $couponQueue = ZMCoupons::instance()->getCouponQueueEntryForId($queueId);
            $controller->exportGlobal('zm_couponQueue', $couponQueue);
            $account = ZMAccounts::instance()->getAccountForId($couponQueue->getAccountId());
            $controller->exportGlobal('zm_account', $account);
            $order = ZMOrders::instance()->getOrderForId($couponQueue->getOrderId());
            $controller->exportGlobal('zm_order', $order);
        }

        if ('coupon' == $template) {
            $couponId = ZMRequest::getParameter('cid');
            $coupon = ZMCoupons::instance()->getCouponForId($couponId);
            $controller->exportGlobal('zm_coupon', $coupon);
            $account = ZMAccounts::instance()->getAccountForId($context['accountId']);
            $controller->exportGlobal('zm_account', $account);
        }
    }

    /**
     * Create ZenMagick order created event that contains the order id.
     */
    public function onNotifyCheckoutProcessAfterOrderCreateAddProducts($args=array()) {
        ZMEvents::instance()->fireEvent(null, ZMEvents::CREATE_ORDER, array('orderId' => $_SESSION['order_number_created']));
    }

}

?>
