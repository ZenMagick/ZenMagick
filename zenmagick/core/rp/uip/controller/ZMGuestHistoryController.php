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
 * Request controller for guest history lookup.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMGuestHistoryController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMGuestHistoryController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMGuestHistoryController();
    }

    /**
     * Default d'tor.
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
    global $zm_crumbtrail;

        $zm_crumbtrail->addCrumb('Guest Order');

        return parent::process();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
    global $zm_request, $zm_accounts, $zm_orders, $zm_messages, $zm_crumbtrail;

        if (!$this->validate('guest_history')) {
            return $this->findView();
        }

        $orderId = $zm_request->getParameter('orderId', 0);
        $email = $zm_request->getParameter('email', 0);

        // default
        $account = null;
        // find order first 
        $order = $zm_orders->getOrderForId($orderId);

        if (null != $order) {
            $accountId = $order->getAccountId();
            if (null != $accountId) {
                $account = $zm_accounts->getAccountForId($accountId);
            }
        }

        if (null != $account && null != $order && ZM_ACCOUNT_TYPE_GUEST == $account->getType() && $account->getEmail()) {
            $zm_crumbtrail->addCrumb("Order # ".$order->getId());
            $this->exportGlobal("zm_order", $order);
            return $this->findView('success');
        } else {
            $zm_messages->warn('No order information found');
            return $this->findView();
        }
    }

}

?>
