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
 * Request controller for account newsletter subscription page.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMAccountNotificationsController extends ZMController {

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
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    function process() { 
        ZMCrumbtrail::instance()->addCrumb("Account", ZMToolbox::instance()->net->url(FILENAME_ACCOUNT, '', true, false));
        ZMCrumbtrail::instance()->addCrumb('Product Notifications');
        $this->exportGlobal("zm_account", ZMRequest::getAccount());

        return parent::process();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
        $globalProductSubscriber = ZMTools::asBoolean(ZMRequest::getParameter('product_global', 0));

        $account = ZMRequest::getAccount();
        $isGlobalUpdate = false;
        if ($globalProductSubscriber != $account->isGlobalProductSubscriber()) {
            $account->setGlobalProductSubscriber($globalProductSubscriber);
            ZMAccounts::instance()->setGlobalProductSubscriber($account->getId(), $globalProductSubscriber);
            $isGlobalUpdate = true;
        }

        if (!$isGlobalUpdate) {
            // if global update is on, products are not listed in the form,
            // therefore, they would all be removed if updated!
            $subscribedProducts = ZMRequest::getParameter('notify', array());
            $account = ZMAccounts::instance()->setSubscribedProductIds($account, $subscribedProducts);
        }

        ZMMessages::instance()->success(zm_l10n_get('Your product subscriptions have been updated.'));
        return $this->findView('success');
    }

}

?>
