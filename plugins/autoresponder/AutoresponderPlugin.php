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
namespace zenmagick\plugins\autoresponder;

use Plugin;

/**
 * Plugin to automtically generate emails based on account events.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AutoresponderPlugin extends Plugin {
    private $cookieUpdated;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Autoresponder', 'Auto emails based on account events.', '${plugin.version}');
        $this->setContext('storefront');
    }


    /**
     * {@inheritDoc}
     */
    public function getDependencies() {
        return array('cron');
    }

    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        //add one hidden field to store al setups
        /*
    $obj = new ZMObject();
    $obj->setLive(false);
    $obj->setBaseSet('orders');
    $obj->setOrderStatus(3);
    $obj->setPostOrderStatus(3);
    $obj->setDelay(3);
    $obj->setSubscribed(true);
    $obj->setLocation('country:NZ');
    $obj->setSubject('What\'s up??');
    $obj->setProducts(array(1,2,3,4,5));
    $obj->setDiscountCoupon(false);

    var_dump($obj);
    var_dump(serialize($obj));
         */

        $default = serialize(array());
        $this->addConfigValue('Config', 'config', $default, 'Config data', 'widget@textFormWidget#name=config&hidden=true&default='.$default);

        /*
(NULL, 'Mode (1)', 'AUTO_MODE', 'test', '<br />Set mode <p />When in test mode, emails will be sent to store owner instead', @configuration_group_id, 3, NOW(), NULL, 'zen_cfg_select_option(array(''test'', ''live''), '),
(NULL, 'Query (1)', 'AUTO_STATE', 'order', '<br />Set query <p />Choose <b>order</b> for post-order emailing <p />Choose <b>account</b> for emailing all account creating customers<p />Choose <b>account-no-order</b> for emailing customers who have created an account but never ordered', @configuration_group_id, 4, NOW(), NULL, 'zen_cfg_select_option(array(''order'', ''account'', ''account-no-order''), '),
(NULL, 'Order Status ID (1)', 'AUTO_ORDER_STATUS_ID', '3', '<br />Emails only send for orders with the following status:<p />(ignore if in <b>account</b> state)<br />', @configuration_group_id, 5, NOW(), NULL, NULL),
(NULL, 'Post Order Status ID (1)', 'AUTO_POST_ORDER_STATUS_ID', '3', '<br />Afterwards, change order status to:<p />(If ID is the same as above, no change will be made)<p />(ignore if in <b>account</b> state)<br />', @configuration_group_id, 6, NOW(), NULL, NULL),
(NULL, 'Days After (1)', 'AUTO_DAYS_AFTER', '21', '<br />Amount of days before email is sent', @configuration_group_id, 7, NOW(), NULL, NULL),
(NULL, 'Subscribed (1)', 'AUTO_SUBSCRIBED', 'true', '<br />Must be subscribed to newsletter', @configuration_group_id, 8, NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''), '),
(NULL, 'Restrict Location (1)', 'AUTO_LOCATION_RESTRICT', 'no', '<br />Emails are restricted', @configuration_group_id, 9, NOW(), NULL, 'zen_cfg_select_option(array(''no'', ''to zone'', ''to country'', ''from zone'', ''from country''), '),
(NULL, 'Location to Restrict (1)', 'AUTO_LOCATION', 'Kent', '<br />Enter location to restrict to/from<p />(ignore if no restriction)<br />', @configuration_group_id, 10, NOW(), NULL, NULL),
(NULL, 'Subject (1)', 'AUTO_SUBJECT', 'Order Review', 'Enter email subject', @configuration_group_id, 11, NOW(), NULL, NULL),
(NULL, 'Restrict Product (1)', 'AUTO_PRODUCT_RESTRICT', '', '<br />Only send email when at least one of the following product IDs are ordered<p />E.g. 1, 2, 3<p />Otherwise leave blank<p />(ignore if in <b>account</b> state)<br />', @configuration_group_id, 15, NOW(), NULL, NULL),
(NULL, 'Include Discount Coupon (1)', 'AUTO_COUPON', '', '<br />Enter existing coupon code to replicate settings from<p />Otherwise leave blank<br />', @configuration_group_id, 16, NOW(), NULL, NULL),
         */
    }


    /**
     * Get the config data.
     *
     * @return array The config data.
     */
    public function getConfig() {
        return unserialize($this->get('config'));
    }

    /**
     * Set the config data.
     *
     * @param array config The config data.
     */
    public function setConfig($config) {
        $this->set('config', serialize($config));
    }

}
