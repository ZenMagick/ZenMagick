<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * Request controller for guest history lookup.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMGuestHistoryController extends ZMController {

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
        $request->getToolbox()->crumbtrail->addCrumb('Guest Order');
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (!$this->validate($request, 'guest_history')) {
            return $this->findView();
        }

        $orderId = $request->getParameter('orderId', 0);
        $email = $request->getParameter('email', 0);

        // default
        $account = null;
        // find order first
        $order = $this->container->get('orderService')->getOrderForId($orderId, $request->getSession()->getLanguageId());

        if (null != $order) {
            $accountId = $order->getAccountId();
            if (null != $accountId) {
                $account = $this->container->get('accountService')->getAccountForId($accountId);
            }
        }

        if (null != $account && null != $order && ZMAccount::GUEST == $account->getType() && $account->getEmail() == $email) {
            $request->getToolbox()->crumbtrail->addCrumb("Order # ".$order->getId());
            return $this->findView('success', array('currentOrder' => $order));
        } else {
            $this->messageService->warn(_zm('No order information found'));
            return $this->findView();
        }
    }

}
