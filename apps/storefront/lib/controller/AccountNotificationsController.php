<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\storefront\controller;

use zenmagick\base\Toolbox;

/**
 * Request controller for account newsletter subscription page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AccountNotificationsController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        $request->getToolbox()->crumbtrail->addCrumb("Account", $request->url('account', '', true));
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
        $globalProductSubscriber = Toolbox::asBoolean($request->getParameter('product_global', false));

        $account = $request->getAccount();
        $isGlobalUpdate = false;
        if ($globalProductSubscriber != $account->isGlobalProductSubscriber()) {
            $account->setGlobalProductSubscriber($globalProductSubscriber);
            $this->container->get('accountService')->setGlobalProductSubscriber($account->getId(), $globalProductSubscriber);
            $isGlobalUpdate = true;
        }

        if (!$isGlobalUpdate) {
            // if global update is on, products are not listed in the form,
            // therefore, they would all be removed if updated!
            $subscribedProducts = $request->getParameter('notify', array());
            $account = $this->container->get('accountService')->setSubscribedProductIds($account, $subscribedProducts);
        }

        $this->messageService->success(_zm('Your product subscriptions have been updated.'));
        return $this->findView('success', array('currentAccount' => $request->getAccount()));
    }

}
