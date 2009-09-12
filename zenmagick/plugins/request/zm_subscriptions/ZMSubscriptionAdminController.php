<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_subscriptions
 * @version $Id$
 */
class ZMSubscriptionAdminController extends ZMPluginPageController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('subscription_admin', zm_l10n_get('Subscriptions'), 'zm_subscriptions');
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
    public function processGet($request) {
        $page = parent::processGet($request);

        $context = array();

        // process...
        $canceled = ZMLangUtils::asBoolean($request->getParameter('canceled', false));
        // get all subscription orders
        $sql = "SELECT orders_id FROM " . TABLE_ORDERS . "
                WHERE  is_subscription = :subscription
                ORDER BY subscription_next_order DESC";
        $results = Runtime::getDatabase()->query($sql, array('subscription' => true), TABLE_ORDERS);
        $orderIds = array();
        foreach ($results as $result) {
            if (null != ($order = ZMOrders::instance()->getOrderForId($result['orderId']))) {
                $orderIds[] = $order;
            }
        }

        $resultSource = ZMLoader::make('ArrayResultSource', 'ZMOrder', $orderIds);
        $resultList = ZMLoader::make('ResultList');
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->getPageIndex());

        $context['zm_resultList'] = $resultList;

        $page->setContents($this->getPageContents($context));
        return $page;
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $page = self::processGet($request);

        $orderId = $request->getOrderId();
        $cancel = $request->getParameter('cancel');
        $hard = ZMLangUtils::asBoolean($request->getParameter('hard'), false);
        if (0 != $orderId && 'cancel' == $cancel) {
            $sql = "UPDATE " . TABLE_ORDERS . "
                    SET is_subscription_canceled = :subscriptionCanceled, is_subscription = :subscription
                    WHERE orders_id = :orderId";
            ZMRuntime::getDatabase()->update($sql, array('orderId' => $orderId, 'subscriptionCanceled' => true, 'subscription' => !$hard), TABLE_ORDERS);
            ZMMessages::instance()->success(zm_l10n_get("Subscription canceled!"));
        }

        $order = ZMOrders::instance()->getOrderForId($orderId);
        $emailTemplate = ZMSettings::get('plugins.zm_subscriptions.email.templates.cancel', 'subscription_cancel');
        $email = $order->getAccount()->getEmail();
        if (!ZMLangUtils::isEmpty($email)) {
            $this->sendCancelEmail($order, $emailTemplate, $email);
        }

        $page->setRefresh(true);

        return $page;
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
        zm_mail(zm_l10n_get("%s: Order Subscription Canceled", ZMSettings::get('storeName')), $template, $context, 
            $email, ZMSettings::get('storeEmail'), null);
        $email = ZMSettings::get('storeEmail');
    }

}

?>
