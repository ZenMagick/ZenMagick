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
 * Request controller for guest checkout.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMCheckoutGuestController extends ZMController {

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
        ZMCrumbtrail::instance()->addCrumb(ZMToolbox::instance()->utils->getTitle(null, false));

        return parent::process();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
        if (!ZMSettings::get('isGuestCheckout')) {
            ZMMessages::instance()->warn(zm_l10n_get('Guest checkout not allowed at this time'));
            return $this->findView('guest_checkout_disabled');
        }

        // our session
        $session = ZMRequest::getSession();

        if (!$session->isAnonymous()) {
            // already logged in either way
            return $this->findView('success');
        }

        if (!$this->validate('checkout_guest')) {
            return $this->findView();
        }

        // create anonymous account
        $account = ZMLoader::make("Account");
        $account->setEmail(ZMRequest::getParameter('email_address'));
        $account->setPassword('');
        $account->setDob(ZM_DB_NULL_DATETIME);
        $account->setType(ZMAccounts::GUEST);
        $account = ZMAccounts::instance()->createAccount($account);

        // update session with valid account
        $session->recreate();
        $session->setAccount($account);

        return $this->findView('success');
    }

}

?>
