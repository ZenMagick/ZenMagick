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

use zenmagick\base\Runtime;

/**
 * Request controller for checkout success page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMCheckoutSuccessController extends ZMController {

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
    public function processGet($request) {
        $request->getToolbox()->crumbtrail->addCrumb("Checkout", $request->url('checkout', '', true));
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());

        // see: onViewDone()
        Runtime::getEventDispatcher()->listen($this);

        $orders = $this->container->get('orderService')->getOrdersForAccountId($request->getAccountId(), $request->getSession()->getLanguageId(), 1);
        $data = array('currentOrder' => $orders[0], 'currentAccount' => $request->getAccount());

        return $this->findView(null, $data);
    }

    /**
     * Event handler to logout guest users only *after* the view is done.
     */
    public function onViewDone($event) {
        $request = $event->get('request');
        if (Runtime::getSettings()->get('isLogoffGuestAfterOrder') && $request->isGuest()) {
            $session = $request->getSession();
            $session->clear();
        }
    }

}
