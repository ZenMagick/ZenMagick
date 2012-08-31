<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\ZenCartBundle;

use ZenMagick\Base\Runtime;
use ZenMagick\apps\store\model\coupons\Coupon;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event Listener
 *
 * @author DerManoMann
 */
class ZenCartListener implements EventSubscriberInterface {
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * Handle things that require a request.
     */
    public function onRequestReady($event) {
        if (Runtime::isContextMatch('storefront')) {
            $autoLoader = $this->container->get('zenCartAutoLoader');
            $autoLoader->initCommon();
            $autoLoader->setGlobalValue('currencies', new \currencies);
        }
    }

    /**
     * Boot ZenCart template and language
     */
    public function onDispatchStart($event) {
        // @todo all this code should go somewhere else
        if (defined('DIR_WS_TEMPLATE') || !Runtime::isContextMatch('storefront')) return;
        $autoLoader = $this->container->get('zenCartAutoLoader');
        $themeId = $this->container->get('themeService')->getActiveThemeId();
        $autoLoader->setGlobalValue('template_dir', $themeId);
        define('DIR_WS_TEMPLATE', DIR_WS_TEMPLATES.$themeId.'/');
        define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_TEMPLATE.'images/');
        define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES.'icons/');

        // required for the payment,checkout,shipping modules
        $autoLoader->setErrorLevel();
        $autoLoader->includeFiles('includes/classes/db/mysql/define_queries.php');
        $autoLoader->includeFiles('includes/languages/%template_dir%/%language%.php');
        $autoLoader->includeFiles('includes/languages/%language%.php');
        $autoLoader->includeFiles(array(
            'includes/languages/%language%/extra_definitions/%template_dir%/*.php',
            'includes/languages/%language%/extra_definitions/*.php')
        );
        $autoLoader->restoreErrorLevel();
    }

    public function onViewStart($event) {
        $settingsService = $this->container->get('settingsService');
        if (Runtime::isContextMatch('admin')) {
            $settingsService->add('apps.store.admin.menus', 'lib/ZenMagick/ZenCartBundle/Resources/config/admin/menu.yaml');
            $settingsService->add('zenmagick.http.routing.addnRouteFiles', __DIR__.'/Resources/config/admin/routing.xml');
        }
    }

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

        if (Runtime::isContextMatch('admin') && 'send_email_to_user' == $request->query->get('action')) {
            // gv mail
            if ($context['GV_REDEEM']) {
                if (1 == preg_match('/.*strong>(.*)<\/strong.*/', $context['GV_REDEEM'], $matches)) {
                    $couponCode = trim($matches[1]);
                    $coupon = $this->container->get('couponService')->getCouponForCode($couponCode, $language->getId());
                    if (null == $coupon) {
                        // coupon gets created only *after* the email is sent!
                        $coupon = new Coupon();
                        $coupon->setCode($couponCode);
                        $coupon->setType(Coupons::TYPPE_GV);
                        $currency = $this->container->get('currencyService')->getCurrencyForCode($settingsService->get('defaultCurrency'));
                        $coupon->setAmount($currency->parse($context['GV_AMOUNT']));
                    }
                    $context['currentCoupon'] = $coupon;
                }

                $context['message'] = $request->request->get('message', '');
                $context['htmlMessage'] = $request->request->get('message_html', '');
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
            if (null !== $request->query->get("oID") && 'update_order' == $request->query->get("action")) {
                $orderId = $request->query->get("oID");
                $order = $orderService->getOrderForId($orderId, $language->getId());
                $context['currentOrder'] = $order;
                $account = $this->container->get('accountService')->getAccountForId($order->getAccountId());
                $context['currentAccount'] = $account;
            }
        }

        if ('gv_queue' == $template) {
            $queueId = $request->query->get('gid');
            $couponQueue = $this->container->get('couponService')->getCouponQueueEntryForId($queueId);
            $context['couponQueue'] = $couponQueue;
            $account = $this->container->get('accountService')->getAccountForId($couponQueue->getAccountId());
            $context['currentAccount'] = $account;
            $order = $orderService->getOrderForId($couponQueue->getOrderId(), $language->getId());
            $context['currentOrder'] = $order;
        }

        if ('coupon' == $template) {
            $couponId = $request->query->get('cid');
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

    public static function getSubscribedEvents() {
        return array(
            'request_ready' => array(array('onRequestReady', 100)),
            'dispatch_start' => array(array('onDispatchStart', 100)),
            'view_start' => array(array('onViewStart', 100)),
            'generate_email' => array(array('onGenerateEmail')),
        );
    }

}
