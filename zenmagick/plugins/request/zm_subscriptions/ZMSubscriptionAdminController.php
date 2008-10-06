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
    public function processGet() {
        $page = parent::processGet();

        $context = array();

        // process...
        $canceled = ZMTools::asBoolean(ZMRequest::getParameter('canceled', false));
        // get all subscription orders
        $sql = "SELECT orders_id FROM " . TABLE_ORDERS . "
                WHERE  is_subscription = :subscription AND is_subscription_canceled = :subscriptionCanceled
                ORDER BY date_purchased";
        $results = ZMRuntime::getDatabase()->query($sql, array('subscription' => true, 'subscriptionCanceled' => $canceled), TABLE_ORDERS);
        $orderIds = array();
        foreach ($results as $result) {
            if (null != ($order = ZMOrders::instance()->getOrderForId($result['orderId']))) {
                $orders[] = $order;
            }
        }

        /*
         *TODO: group count per subscription
         *
SELECT count( orders_id ) , customers_id
FROM `zen_orders`
GROUP BY customers_id
         */
        $resultSource = ZMLoader::make('ArrayResultSource', 'ZMOrder', $orders);
        $resultList = ZMLoader::make('ResultList');
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber(ZMRequest::getPageIndex());

        $context['zm_resultList'] = $resultList;

        $page->setContents($this->getPageContents($context));
        return $page;
    }

}

?>
