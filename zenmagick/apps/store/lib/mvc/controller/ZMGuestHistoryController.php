<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 * Request controller for guest history lookup.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id$
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
        $order = ZMOrders::instance()->getOrderForId($orderId, $request->getSession()->getLanguageId());

        if (null != $order) {
            $accountId = $order->getAccountId();
            if (null != $accountId) {
                $account = ZMAccounts::instance()->getAccountForId($accountId);
            }
        }

        if (null != $account && null != $order && ZMAccount::GUEST == $account->getType() && $account->getEmail() == $email) {
            $request->getToolbox()->crumbtrail->addCrumb("Order # ".$order->getId());
            return $this->findView('success', array('currentOrder' => $order));
        } else {
            ZMMessages::instance()->warn(zm_l10n_get('No order information found'));
            return $this->findView();
        }
    }

}
