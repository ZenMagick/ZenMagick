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
 * Request controller for account password page.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMAccountPasswordController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMAccountPasswordController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMAccountPasswordController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
    global $zm_crumbtrail;

        $zm_crumbtrail->addCrumb("Account", zm_secure_href(FILENAME_ACCOUNT, '', false));
        $zm_crumbtrail->addCrumb(zm_title(false));

        return $this->findView();
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
            return $this->findView('login');
        }

        if ($session->isAnonymous()) {
            // not logged in
            return $this->findView('login');
        }

        if (!$this->validate('account_password')) {
            return $this->findView();
        }

        $account = $zm_request->getAccount();
        if (null == $account) {
            // TODO:
            die('could not access session account');
        }

        $oldPassword = $zm_request->getParameter('password_current');
        $newPassword = $zm_request->getParameter('password_new');
        $confirmPassword = $zm_request->getParameter('password_confirmation');

        if (!zm_validate_password($oldPassword, $account->getPassword())) {
            $zm_messages->error('Your current password did not match the password in our records. Please try again.');
            return $this->findView();
        }

        // update password
        $newEncrpytedPassword = zm_encrypt_password($newPassword);
        $zm_accounts->_setAccountPassword($account->getId(), $newEncrpytedPassword);

        zm_bb_change_password($account->getNickname(), $newPassword);

        $zm_messages->success('Your password has been updated.');

        return $this->findView('success');
    }

}

?>
