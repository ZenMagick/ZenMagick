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
namespace ZenMagick\plugins\subscriptions\Controller;

use ZenMagick\Base\Beans;
use ZenMagick\Base\Toolbox;
use ZenMagick\AdminBundle\Controller\PluginAdminController;

/**
 * Admin controller.
 *
 * @package org.zenmagick.plugins.subscriptions
 */
class SubscriptionAdminController extends PluginAdminController
{
    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct('subscriptions');
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        // get all subscription orders
        $sql = "SELECT orders_id FROM %table.orders%
                WHERE  is_subscription = :subscription
                ORDER BY subscription_next_order DESC";
        $results = \ZMRuntime::getDatabase()->fetchAll($sql, array('subscription' => true), 'orders');
        $orderIds = array();
        foreach ($results as $result) {
            if (null != ($order = $this->container->get('orderService')->getOrderForId($result['orderId'], $request->getSession()->getLanguageId()))) {
                $orderIds[] = $order;
            }
        }

        $resultSource = new \ZMArrayResultSource('ZenMagick\StoreBundle\Entity\Order\Order', $orderIds);
        $resultList = Beans::getBean('ZMResultList');
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->query->getInt('page'));

        return $this->findView(null,array('resultList' => $resultList));

    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        $orderId = $request->request->getInt('orderId');
        $cancel = $request->request->get('cancel');
        $hard = Toolbox::asBoolean($request->request->get('hard'), false);
        if (0 != $orderId && 'cancel' == $cancel) {
            $sql = "UPDATE %table.orders%
                    SET is_subscription_canceled = :subscriptionCanceled, is_subscription = :subscription
                    WHERE orders_id = :orderId";
            \ZMRuntime::getDatabase()->updateObj($sql, array('orderId' => $orderId, 'subscriptionCanceled' => true, 'subscription' => !$hard), 'orders');
            $this->get('session.flash_bag')->success($this->get('translator')->trans("Subscription canceled!"));
        }

        $order = $this->container->get('orderService')->getOrderForId($orderId, $request->getSession()->getLanguageId());
        $emailTemplate = $this->container->get('settingsService')->get('plugins.subscriptions.email.templates.cancel', 'subscription_cancel');
        $email = $order->getAccount()->getEmail();
        if (!Toolbox::isEmpty($email)) {
            $this->sendCancelEmail($order, $emailTemplate, $email);
        }

        return $this->findView('success');
    }

    /**
     * Send cancel email.
     *
     * @param ZenMagick\StoreBundle\Entity\Order\Order order The order.
     * @param string template The template.
     * @param string email The email address.
     */
    protected function sendCancelEmail($order, $template, $email)
    {
        $context = array();
        $context['order'] = $order;
        $context['plugin'] = $this->getPlugin();
        $settingsService = $this->container->get('settingsService');

        $message = $this->container->get('messageBuilder')->createMessage($template, true, $request, $context);
        $subject = $this->get('translator')->trans('%store_name%: Order Subscription Canceled', array('%store_name%' => $settingsService->get('storeName')));
        $message->setSubject($subject)->setTo($email)->setFrom($settingsService->get('storeEmail'));
        $this->container->get('mailer')->send($message);
    }

}
