<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Request controller for account newsletter subscription page.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMAccountNotificationsController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMAccountNotificationsController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMAccountNotificationsController();
    }

    /**
     * Default d'tor.
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
    function processGet() {
    global $zm_request, $zm_crumbtrail;

        $zm_crumbtrail->addCrumb("Account", zm_secure_href(FILENAME_ACCOUNT, '', false));
        $zm_crumbtrail->addCrumb(zm_title(false));

        return $this->findView();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
    global $zm_request, $zm_accounts, $zm_messages;

        $globalProductSubscriber = zm_boolean($zm_request->getParameter('product_global', 0));

        $account = $zm_request->getAccount();
        $isGlobalUpdate = false;
        if ($globalProductSubscriber != $account->isGlobalProductSubscriber()) {
            $account->setGlobalProductSubscriber($globalProductSubscriber);
            $zm_accounts->setGlobalProductSubscriber($account->getId(), $globalProductSubscriber);
            $isGlobalUpdate = true;
        }

        if (!$isGlobalUpdate) {
            // if global update is on, products are not listed in the form,
            // therefore, they would all be removed if updated!
            $subscribedProducts = $zm_request->getParameter('notify', array());
            $account = $zm_accounts->setSubscribedProductIds($account, $subscribedProducts);
        }

        $zm_messages->success('Your product subscriptions have been updated.');
        return $this->findView('success');
    }

}

?>
