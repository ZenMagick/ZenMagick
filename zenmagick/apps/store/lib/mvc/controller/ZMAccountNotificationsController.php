<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
    public function preProcess($request) { 
        $request->getToolbox()->crumbtrail->addCrumb("Account", $request->url(FILENAME_ACCOUNT, '', true));
        $request->getToolbox()->crumbtrail->addCrumb('Product Notifications');
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        return $this->findView(null, array('currentAccount' => $request->getAccount()));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $globalProductSubscriber = ZMLangUtils::asBoolean($request->getParameter('product_global', false));

        $account = $request->getAccount();
        $isGlobalUpdate = false;
        if ($globalProductSubscriber != $account->isGlobalProductSubscriber()) {
            $account->setGlobalProductSubscriber($globalProductSubscriber);
            ZMAccounts::instance()->setGlobalProductSubscriber($account->getId(), $globalProductSubscriber);
            $isGlobalUpdate = true;
        }

        if (!$isGlobalUpdate) {
            // if global update is on, products are not listed in the form,
            // therefore, they would all be removed if updated!
            $subscribedProducts = $request->getParameter('notify', array());
            $account = ZMAccounts::instance()->setSubscribedProductIds($account, $subscribedProducts);
        }

        ZMMessages::instance()->success(_zm('Your product subscriptions have been updated.'));
        return $this->findView('success', array('currentAccount' => $request->getAccount()));
    }

}
