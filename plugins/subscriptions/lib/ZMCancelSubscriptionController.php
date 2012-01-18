<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
use zenmagick\base\Toolbox;

/**
 * Request controller to cancel a subscription.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.subscriptions
 */
class ZMCancelSubscriptionController extends ZMController {

    /**
     * {@inheritDoc}
     *
     * @todo allow cancel at any time
     */
    public function processGet($request) {
        if (!Toolbox::asBoolean($this->getPlugin()->get('customerCancel'))) {
            $this->messageService->error(_zm("Insufficient permission"));
            return $this->findView();
        }
        $orderId = $request->getOrderId();
        $order = $this->container->get('orderService')->getOrderForId($orderId, $request->getSession()->getLanguageId());
        $account = $order->getAccount();

        // make sure this is an allowed order
        if ($order->getAccountId() != $order->getAccountId()) {
            $this->messageService->error(_zm("Invalid order selected"));
            return $this->findView();
        }

        $plugin = $this->getPlugin();

        // check for number of scheduled orders
        $sql = "SELECT COUNT(orders_id) AS total FROM " . TABLE_ORDERS . "
                WHERE subscription_order_id = :subscriptionOrderId";
        $results = ZMRuntime::getDatabase()->querySingle($sql, array('subscriptionOrderId' => $orderId), TABLE_ORDERS, ZMDatabase::MODEL_RAW);

        if ($results['total'] < $plugin->get('minOrders')) {
            $this->messageService->error(sprintf(_zm("This subscription can only be canceled after a minimum of %s orders"), $plugin->get('minOrders')));
            return $this->findView();
        }

        $cancelDeadline = $plugin->get('cancelDeadline');
        if (0 < $cancelDeadline) {
            // this will return only a result if subscription_next_order is more than $cancelDeadline days in the future
            $sql = "SELECT orders_id
                    FROM " . TABLE_ORDERS . "
                    WHERE orders_id = :orderId
                      AND DATE_SUB(subscription_next_order, INTERVAL " . $cancelDeadline . " DAY) >= CURDATE()";
            $result = ZMRuntime::getDatabase()->querySingle($sql, array('orderId' => $orderId), TABLE_ORDERS, ZMDatabase::MODEL_RAW);
            if (null == $result) {
                $this->messageService->error(sprintf(_zm("Can't cancel less than %s days before next subscription"), $cancelDeadline));
                return $this->findView();
            }
        }

        $sql = "UPDATE " . TABLE_ORDERS . "
                SET is_subscription_canceled = :subscriptionCanceled
                WHERE orders_id = :orderId";
        ZMRuntime::getDatabase()->update($sql, array('orderId' => $orderId, 'subscriptionCanceled' => true), TABLE_ORDERS);
        $this->messageService->success(_zm("Subscription canceled!"));

        $emailTemplate = Runtime::getSettings()->get('plugins.subscriptions.email.templates.cancel', 'subscription_cancel');
        $this->sendCancelEmail($order, $emailTemplate, $account->getEmail());
        $adminEmail = $plugin->get('adminEmail');
        if (empty($adminEmail)) {
            $adminEmail = Runtime::getSettings()->get('storeEmail');
        }
        if (!ZMLangUtils::isEmpty($adminEmail)) {
            $this->sendCancelEmail($order, $cancelEmailTemplate, $adminEmail);
        }

        return $this->findView();
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

        $message = $this->container->get('messageBuilder')->createMessage($template, true, $request, $context);
        $message->setSubject(sprintf(_zm("%s: Order Subscription Canceled"), Runtime::getSettings()->get('storeName')))->setTo($email)->setFrom(Runtime::getSettings()->get('storeEmail'));
        $this->container->get('mailer')->send($message);
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return $this->container->get('pluginService')->getPluginForId('subscriptions');
    }

}
