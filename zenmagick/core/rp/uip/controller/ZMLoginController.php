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
 * Request controller for logins.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMLoginController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMLoginController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMLoginController();
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

        $zm_crumbtrail->addCrumb(zm_title(false));

        return parent::process();
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
        // our session
        $session = new ZMSession();

        if (!$session->isAnonymous()) {
            // already logged in
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
    global $zm_request, $zm_accounts, $zm_messages;

        // our session
        $session = new ZMSession();

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

        $emailAddress = $zm_request->getParameter('email_address');
        $account = $zm_accounts->getAccountForEmailAddress($emailAddress);
        if (null === $account) {
            $zm_messages->error('Sorry, there is no match for that email address and/or password.');
            return $this->findView();
        }

        $password = $zm_request->getParameter('password');
        if (!zm_validate_password($password, $account->getPassword())) {
            $zm_messages->error('Sorry, there is no match for that email address and/or password.');
            return $this->findView();
        }

        // update session with valid account
        $session->recreate();
        $session->setAccount($account);

        // update login stats
        $zm_accounts->updateAccountLoginStats($account->getId());

        // restore cart contents
        $session->restoreCart();

        $followUpUrl = $session->getLoginFollowUp();
        return $this->findView('success', array('url' => $followUpUrl));
    }

}

?>
