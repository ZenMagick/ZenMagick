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
 * Request controller for account newsletter subscription page.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id: ZMAccountNotificationsController.php 2348 2009-06-29 03:04:18Z dermanomann $
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
     * {@inheritDoc}
     */
    public function handleRequest() { 
        ZMCrumbtrail::instance()->addCrumb("Account", ZMToolbox::instance()->net->url(FILENAME_ACCOUNT, '', true, false));
        ZMCrumbtrail::instance()->addCrumb('Product Notifications');
    }

    /**
     * {@inheritDoc}
     */
    public function processGet() {
        return $this->findView(null, array('zm_account' => ZMRequest::getAccount()));
    }

    /**
     * {@inheritDoc}
     */
    public public function processPost() {
        $globalProductSubscriber = ZMLangUtils::asBoolean(ZMRequest::getParameter('product_global', 0));

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
        return $this->findView('success', array('zm_account' => ZMRequest::getAccount()));
    }

}

?>
