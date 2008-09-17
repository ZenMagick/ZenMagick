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
 * Request controller to cancel a subscription.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_subscriptions
 * @version $Id$
 */
class ZMCancelSubscriptionController extends ZMController {

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
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processGet() {
        $orderId = ZMRequest::getOrderId();
        $plugin = $this->getPlugin();

        // check for number of scheduled orders
        $sql = "SELECT COUNT(orders_id) AS total FROM " . TABLE_ORDERS . "
                WHERE subscription_order_id = :subscriptionOrderId";
        $results = ZMRuntime::getDatabase()->querySingle($sql, array('subscriptionOrderId' => $orderId), TABLE_ORDERS, ZM_DB_MODEL_RAW);

        if ($results['total'] < $plugin->get('minOrders')) {
            ZMMessages::instance()->error(zm_l10n_get("This subscription can only be canceled after a minimum of %s orders", $plugin->get('minOrders')));
            return $this->findView();
        }

        //TODO: check min das before schedule (cancelDeadline)
        $cancelDeadline = $plugin->get('cancelDeadline');
        if (0 < $cancelDeadline) {
            // this will return only a result if subscription_next_order is more than $cancelDeadline days in the future
            $sql = "SELECT orders_id
                    FROM " . TABLE_ORDERS . "
                    WHERE orders_id = :orderId
                      AND DATE_SUB(subscription_next_order, INTERVAL " . $cancelDeadline . " DAY) >= CURDATE()";
            $result = ZMRuntime::getDatabase()->querySingle($sql, array('orderId' => $orderId), TABLE_ORDERS, ZM_DB_MODEL_RAW);
            if (null == $result) {
                ZMMessages::instance()->error(zm_l10n_get("Can't cancel less than %s days before next subscription", $cancelDeadline));
                return $this->findView();
            }
        }

        $sql = "UPDATE " . TABLE_ORDERS . "
                SET is_subscription = :subscription
                WHERE orders_id = :orderId";
        ZMRuntime::getDatabase()->update($sql, array('orderId' => $orderId, 'subscription' => false), TABLE_ORDERS);
        ZMMessages::instance()->success(zm_l10n_get("Subscription canceled!"));

        return $this->findView();
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return ZMPlugins::instance()->getPluginForId('zm_subscriptions');
    }

}

?>
