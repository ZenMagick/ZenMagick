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
 * Request controller for guest checkout.
 *
 * @author DerManoMann
 * @package zenmagick.store.sf.mvc.controller
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
     * {@inheritDoc}
     */
    public function preProcess($request) { 
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (!ZMSettings::get('isGuestCheckout')) {
            ZMMessages::instance()->warn(_zm('Guest checkout not allowed at this time'));
            return $this->findView('guest_checkout_disabled');
        }

        // our session
        $session = $request->getSession();

        if (!$session->isAnonymous()) {
            // already logged in either way
            return $this->findView('success');
        }

        if (!$this->validate($request, 'checkout_guest')) {
            return $this->findView();
        }

        // create anonymous account
        $account = ZMBeanUtils::getBean("Account");
        $account->setEmail($request->getParameter('email_address'));
        $account->setPassword('');
        $account->setDob(ZMDatabase::NULL_DATETIME);
        $account->setType(ZMAccount::GUEST);
        $account = ZMAccounts::instance()->createAccount($account);

        // update session with valid account
        $session->recreate();
        $session->setAccount($account);

        return $this->findView('success');
    }

}
