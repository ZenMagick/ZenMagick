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
 * Fixes and stuff that are (can be) event driven.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.utils
 */
class ZMEmailFixes extends ZMObject {

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
     * Fix email context for various emails.
     */
    public function onGenerateEmail($event) {
        $context = $event->get('context');
        $template = $event->get('template');
        $request =  $event->get('request');

        // set for all
        $language = $request->getSelectedLanguage();
        $context['language'] = $language;

        if (ZMSettings::get('isAdmin') && 'send_email_to_user' == $request->getParameter('action')) {
            // gv mail
            if ($context['GV_REDEEM']) {
                if (1 == preg_match('/.*strong>(.*)<\/strong.*/', $context['GV_REDEEM'], $matches)) {
                    $couponCode = trim($matches[1]);
                    $coupon = ZMCoupons::instance()->getCouponForCode($couponCode, $language->getId());
                    if (null == $coupon) {
                        // coupon gets created only *after* the email is sent!
                        $coupon = ZMLoader::make('ZMCoupon', 0, $couponCode, ZMCoupons::TYPPE_GV);
                        $currency = ZMCurrencies::instance()->getCurrencyForCode(ZMSettings::get('defaultCurrency'));
                        $coupon->setAmount($currency->parse($context['GV_AMOUNT']));
                    }
                    $context['currentCoupon'] = $coupon;
                }

                $context['message'] = $request->getParameter('message', '');
                $context['htmlMessage'] = $request->getParameter('message_html', '', false);
            }
        }

        if ('checkout' == $template) {
            $order = ZMOrders::instance()->getOrderForId($context['INTRO_ORDER_NUMBER'], $language->getId());
            $shippingAddress = $order->getShippingAddress();
            $billingAddress = $order->getBillingAddress();
            $paymentType = $order->getPaymentType();

            $context['order'] = $order;
            $context['shippingAddress'] = $shippingAddress;
            $context['billingAddress'] = $billingAddress;
            $context['paymentType'] = $paymentType;
        }

        if ('order_status' == $template) {
            $newOrderStatus = $context['EMAIL_TEXT_NEW_STATUS'];
            preg_match('/[^:]*:(.*)/ms', $context['EMAIL_TEXT_STATUS_COMMENTS'], $matches);
            $comment = strip_tags(trim($matches[1]));

            $context['newOrderStatus'] = $newOrderStatus;
            $context['comment'] = $comment;

            // from zc_fixes
            if (null !== $request->getParameter("oID") && 'update_order' == $request->getParameter("action")) {
                $orderId = $request->getParameter("oID");
                $order = ZMOrders::instance()->getOrderForId($orderId, $language->getId());
                $context['currentOrder'] = $order;
                $account = ZMAccounts::instance()->getAccountForId($order->getAccountId());
                $context['currentAccount'] = $account;
            }
        }

        if ('gv_queue' == $template) {
            $queueId = $request->getParameter('gid');
            $couponQueue = ZMCoupons::instance()->getCouponQueueEntryForId($queueId);
            $context['couponQueue'] = $couponQueue;
            $account = ZMAccounts::instance()->getAccountForId($couponQueue->getAccountId());
            $context['currentAccount'] = $account;
            $order = ZMOrders::instance()->getOrderForId($couponQueue->getOrderId(), $language->getId());
            $context['currentOrder'] = $order;
        }

        if ('coupon' == $template) {
            $couponId = $request->getParameter('cid');
            $coupon = ZMCoupons::instance()->getCouponForId($couponId, $language->getId());
            $context['currentCoupon'] = $coupon;
            $account = ZMAccounts::instance()->getAccountForId($context['accountId']);
            $context['currentAccount'] = $account;
        }

        if ('password_forgotten_admin' == $template) {
            $context['adminName'] = $context['EMAIL_CUSTOMERS_NAME'];
            $context['htmlMessage'] = $context['EMAIL_MESSAGE_HTML'];
            $context['textMessage'] = $context['text_msg'];
        }

        if ('product_notification' == $template) {
            $account = new ZMAccount();
            $account->setFirstName($context['EMAIL_FIRST_NAME']);
            $account->setLastName($context['EMAIL_LAST_NAME']);
            $context['currentAccount'] = $account;
            $context['message'] = $context['text_msg'];
            $context['htmlMessage'] = $context['EMAIL_MESSAGE_HTML'];
        }

        $event->set('context', $context);
    }

}
