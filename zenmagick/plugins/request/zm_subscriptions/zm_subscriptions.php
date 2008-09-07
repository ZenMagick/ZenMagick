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
 * Subscriptions.
 *
 * @package org.zenmagick.plugins.zm_subscriptions
 * @author DerManoMann
 * @version $Id: zm_token.php 1460 2008-08-26 01:37:31Z DerManoMann $
 */
class zm_subscriptions extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Subscriptions', 'All users to subscribe products/orders', '${plugin.version}');
        $this->setLoaderSupport('FOLDER');

        // the new prices and customer flag
        $customFields = array(
            'orders' => 'subscription;integer',
            'orders' => 'last_order;date;lastOrder',
            'orders' => 'subscription_schedule;string;schedule',
            'orders' => 'subscription_order_id;date;subscriptionOrderId'
        );
        foreach ($customFields as $table => $fields) {
            ZMSettings::append('sql.'.$table.'.customFields', $fields, ',');
        }
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
    function install() {
        parent::install();
        ZMDbUtils::executePatch(file($this->getPluginDir()."sql/install.sql"), $this->messages_);

        $this->addConfigValue('Qualifying amount', 'minAmount', '0', 'The minimum amoout to qualify for a subscription');
        $this->addConfigValue('Minimum orders', 'minOrders', '0', 'The minimum number of orders before the subscription can be canceled');
        $this->addConfigValue('Cancel dealline', 'cancelDeadline', '0', 'Days before the next order the user can cancel the subscription');
        $this->addConfigValue('Notification email template name', 'emailTemplate', '', 'Name of an email template to notify customers of new subscription orders; leave empty for none');
        $this->addConfigValue('Order status', 'orderStatus', '2', 'Order status for subscription orders', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name');
    }

    /**
     * {@inheritDoc}
     */
    function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file($this->getPluginDir()."sql/uninstall.sql"), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        $this->zcoSubscribe();

        // register tests
        if (null != ($tests = ZMPlugins::instance()->getPluginForId('zm_tests'))) {
            // add class path only now to avoid errors due to missing UnitTestCase
            ZMLoader::instance()->addPath($this->getPluginDir().'tests/');
            $tests->addTest('TestSubscriptions');
        }

    }

    /**
     * Event handler to pick up subscription cehckout options.
     */
    public function onZMInitDone($args=array()) {
        if ('checkout_shipping' == ZMRequest::getPageName() && 'POST' == ZMRequest::getMethod()) {
            if (ZMTools::asBoolean(ZMRequest::getParameter('subscription'))) {
                ZMRequest::getSession()->setValue('subscription_schedule', ZMRequest::getParameter('schedule'));
            } else {
                ZMRequest::getSession()->removeValue('subscription_schedule');
            }
        }
        if ('checkout_success' == ZMRequest::getPageName()) {
            ZMRequest::getSession()->removeValue('subscription_schedule');
        }
    }

    /**
     * Check if currently subscription is selected.
     *
     * @return string The subscription schedule key or <code>null</code>.
     */
    public function getSelectedSchedule() {
        $schedule = ZMRequest::getSession()->getValue('subscription_schedule');
        return empty($schedule) ? null : $schedule;
    }

    /**
     * Get all available schedules as map.
     * 
     * <p>This can be configured/changed via the setting <em>plugins.zm_subscriptions.schedules</em>.</p>
     *
     * @return array Hash map of schedule key => name.
     */
    public function getSchedules() {
        $defaults = array(
            '1w' => 'Weekly',
            '10d' => 'Every 10 days',
            '10d' => 'Every 10 days',
            '4w' => 'Every four weeks',
            '1m' => 'Once a month'
        );
        return ZMSettings::get('plugins.zm_subscriptions.schedules', $defaults);
    }

    /**
     * Order created event handler.
     */
    public function onZMCreateOrder($args=array()) {
        $orderId = $args['orderId'];
        if (null != ($schedule = $this->getSelectedSchedule())) {
            // TODO: update order to set up as proper subscription
        }
    }

}

?>
