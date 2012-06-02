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

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;

/**
 * Admin controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.subscriptions
 */
class ZMSubscriptionAdminController extends ZMPluginAdminController {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('subscriptions');
    }


    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        // get all subscription orders
        $sql = "SELECT orders_id FROM " . TABLE_ORDERS . "
                WHERE  is_subscription = :subscription
                ORDER BY subscription_next_order DESC";
        $results = ZMRuntime::getDatabase()->fetchAll($sql, array('subscription' => true), 'orders');
        $orderIds = array();
        foreach ($results as $result) {
            if (null != ($order = $this->container->get('orderService')->getOrderForId($result['orderId'], $request->getSession()->getLanguageId()))) {
                $orderIds[] = $order;
            }
        }

        $resultSource = new ZMArrayResultSource('ZMOrder', $orderIds);
        $resultList = Runtime::getContainer()->get('ZMResultList');
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->getPageIndex());

        return $this->findView(null,array('resultList' => $resultList));

    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $orderId = $request->getOrderId();
        $cancel = $request->getParameter('cancel');
        $hard = Toolbox::asBoolean($request->getParameter('hard'), false);
        if (0 != $orderId && 'cancel' == $cancel) {
            $sql = "UPDATE " . TABLE_ORDERS . "
                    SET is_subscription_canceled = :subscriptionCanceled, is_subscription = :subscription
                    WHERE orders_id = :orderId";
            ZMRuntime::getDatabase()->updateObj($sql, array('orderId' => $orderId, 'subscriptionCanceled' => true, 'subscription' => !$hard), 'orders');
            $this->messageService->success(_zm("Subscription canceled!"));
        }

        $order = $this->container->get('orderService')->getOrderForId($orderId, $request->getSession()->getLanguageId());
        $emailTemplate = Runtime::getSettings()->get('plugins.subscriptions.email.templates.cancel', 'subscription_cancel');
        $email = $order->getAccount()->getEmail();
        if (!Toolbox::isEmpty($email)) {
            $this->sendCancelEmail($order, $emailTemplate, $email);
        }

        return $this->findView('success');
    }

    /**
     * Send cancel email.
     *
     * @param ZMOrder order The order.
     * @param string template The template.
     * @param string email The email address.
     */
    protected function sendCancelEmail($order, $template, $email) {
        $context = array();
        $context['order'] = $order;
        $context['plugin'] = $this->getPlugin();

        $message = $this->container->get('messageBuilder')->createMessage($template, true, $request, $context);
        $message->setSubject(sprintf(_zm("%s: Order Subscription Canceled"), Runtime::getSettings()->get('storeName')))->setTo($email)->setFrom(Runtime::getSettings()->get('storeEmail'));
        $this->container->get('mailer')->send($message);
    }

}
