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
 * Request controller for logins.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMLoginController extends ZMController {

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
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
        $session = ZMRequest::getSession();
        if ($session->isRegistered()) {
            // can't get any better than this!
            return $this->findView('index');
        }

        return parent::processGet();

    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
        $session = ZMRequest::getSession();
        if (!$session->isValid()) {
            return $this->findView('cookie_usage');
        }

        if ($session->isRegistered()) {
            // already logged in
            return $this->findView('account');
        }

        if (!$this->validate('login')) {
            return $this->findView();
        }

        $emailAddress = ZMRequest::getParameter('email_address');
        $account = ZMAccounts::instance()->getAccountForEmailAddress($emailAddress);
        if (null === $account) {
            ZMMessages::instance()->error(zm_l10n_get('Sorry, there is no match for that email address and/or password.'));
            return $this->findView();
        }

        $password = ZMRequest::getParameter('password');
        if (!zm_validate_password($password, $account->getPassword())) {
            ZMMessages::instance()->error(zm_l10n_get('Sorry, there is no match for that email address and/or password.'));
            return $this->findView();
        }

        if (ZM_ACCOUNT_AUTHORIZATION_BLOCKED == $account->getAuthorization()) {
            ZMMessages::instance()->error(zm_l10n_get('Access denied.'));
            return $this->findView();
        }

        // info only
        ZMEvents::instance()->fireEvent($this, ZM_EVENT_LOGIN_SUCCESS, array('controller' => $this, 'account' => $account));

        // update session with valid account
        $session->recreate();
        $session->setAccount($account);

        // update login stats
        ZMAccounts::instance()->updateAccountLoginStats($account->getId());

        // restore cart contents
        $session->restoreCart();

        $followUpUrl = $session->getLoginFollowUp();
        return $this->findView('success', array('url' => $followUpUrl));
    }

}

?>
