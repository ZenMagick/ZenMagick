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
namespace apps\store\bundles\ZenCartBundle\utils;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Fix email context.
 *
 * @author DerManoMann
 * @package apps.store.bundles.ZenCartBundle.utils
 */
class EmailEventHandler extends ZMObject {

    /**
     * Fix email context for various emails.
     */
    public function onGenerateEmail($event) {
        $context = $event->get('context');
        $template = $event->get('template');
        $request =  $event->get('request');

        $settingsService = $this->container->get('settingsService');
        // set for all
        $language = $request->getSelectedLanguage();
        $context['language'] = $language;

        $orderService = $this->container->get('orderService');

        if ($settingsService->get('isAdmin') && 'send_email_to_user' == $request->getParameter('action')) {
            // gv mail
            if ($context['GV_REDEEM']) {
                if (1 == preg_match('/.*strong>(.*)<\/strong.*/', $context['GV_REDEEM'], $matches)) {
                    $couponCode = trim($matches[1]);
                    $coupon = $this->container->get('couponService')->getCouponForCode($couponCode, $language->getId());
                    if (null == $coupon) {
                        // coupon gets created only *after* the email is sent!
                        $coupon = Runtime::getContainer()->get('ZMCoupon');
                        $coupon->setCode($couponCode);
                        $coupon->setType(\ZMCoupons::TYPPE_GV);
                        $currency = $this->container->get('currencyService')->getCurrencyForCode($settingsService->get('defaultCurrency'));
                        $coupon->setAmount($currency->parse($context['GV_AMOUNT']));
                    }
                    $context['currentCoupon'] = $coupon;
                }

                $context['message'] = $request->getParameter('message', '');
                $context['htmlMessage'] = $request->getParameter('message_html', '', false);
            }
        }

        if ('checkout' == $template) {
            $order = $orderService->getOrderForId($context['INTRO_ORDER_NUMBER'], $language->getId());
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
                $order = $orderService->getOrderForId($orderId, $language->getId());
                $context['currentOrder'] = $order;
                $account = $this->container->get('accountService')->getAccountForId($order->getAccountId());
                $context['currentAccount'] = $account;
            }
        }

        if ('gv_queue' == $template) {
            $queueId = $request->getParameter('gid');
            $couponQueue = $this->container->get('couponService')->getCouponQueueEntryForId($queueId);
            $context['couponQueue'] = $couponQueue;
            $account = $this->container->get('accountService')->getAccountForId($couponQueue->getAccountId());
            $context['currentAccount'] = $account;
            $order = $orderService->getOrderForId($couponQueue->getOrderId(), $language->getId());
            $context['currentOrder'] = $order;
        }

        if ('coupon' == $template) {
            $couponId = $request->getParameter('cid');
            $coupon = $this->container->get('couponService')->getCouponForId($couponId, $language->getId());
            $context['currentCoupon'] = $coupon;
            $account = $this->container->get('accountService')->getAccountForId($context['accountId']);
            $context['currentAccount'] = $account;
        }

        if ('password_forgotten_admin' == $template) {
            $context['adminName'] = $context['EMAIL_CUSTOMERS_NAME'];
            $context['htmlMessage'] = $context['EMAIL_MESSAGE_HTML'];
            $context['textMessage'] = $context['text_msg'];
        }

        if ('product_notification' == $template) {
            $account = new \ZMAccount();
            $account->setFirstName($context['EMAIL_FIRST_NAME']);
            $account->setLastName($context['EMAIL_LAST_NAME']);
            $context['currentAccount'] = $account;
            $context['message'] = $context['text_msg'];
            $context['htmlMessage'] = $context['EMAIL_MESSAGE_HTML'];
        }

        $event->set('context', $context);
    }

}
