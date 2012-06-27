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
namespace zenmagick\apps\store\storefront\controller;

use zenmagick\base\Runtime;

/**
 * Request controller for checkout success page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CheckoutSuccessController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        // see: onViewDone()
        Runtime::getEventDispatcher()->listen($this);
        $account = $this->getUser();
        $orders = $this->container->get('orderService')->getOrdersForAccountId($account->getId(), $request->getSession()->getLanguageId(), 1);

        $currentOrder = $orders[0];
        $productsToSubscribe = array();
        if (!$account->isGlobalProductSubscriber()) {
            $subscribedProducts = $account->getSubscribedProducts();
            foreach ($currentOrder->getOrderItems() as $orderItem) {
                $productId = $orderItem->getProductId();
                if (in_array($productId, $subscribedProducts)) continue;
                $productsToSubscribe[$productId] = $orderItem->getName();
            }

        }
        $data = array('currentOrder' => $currentOrder, 'currentAccount' => $account, 'productsToSubscribe' => $productsToSubscribe);
        return $this->findView(null, $data);
    }

    public function processPost($request) {
        $notifyProducts = $request->request->get('notify', array());

        if (!empty($notifyProducts)) {
            $account = $this->getUser();
            $subscribedProducts = array_unique(array_merge($account->getSubscribedProducts(), $notifyProducts));
            if (!empty($subscribedProducts)) {
                $this->container->get('accountService')->setSubscribedProductIds($account, $subscribedProducts);
            }
            $this->messageService->success(_zm('Your product subscriptions have been updated.'));
        }

        return $this->processGet($request);
    }

    /**
     * Event handler to logout guest users only *after* the view is done.
     */
    public function onViewDone($event) {
        $request = $event->get('request');
        $session = $request->getSession();
        if (Runtime::getSettings()->get('isLogoffGuestAfterOrder') && $session->isGuest()) {
            $session->clear();
        }
    }

}
