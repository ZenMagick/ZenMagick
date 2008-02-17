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
 * Request controller for forgotten passwords.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMPasswordForgottenController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMPasswordForgottenController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMPasswordForgottenController();
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
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
    global $zm_request, $zm_accounts, $zm_messages;

        $emailAddress = $zm_request->getParameter('email_address');
        $account = $zm_accounts->getAccountForEmailAddress($emailAddress);
        if (null === $account || ZM_ACCOUNT_TYPE_REGISTERED != $account->getType()) {
            $zm_messages->error(zm_l10n_get("Sorry, there is no account with the email address '%s'.", $emailAddress));
            return $this->findView();
        }

        $newPassword = zm_new_password();
        $newEncrpytedPassword = zm_encrypt_password($newPassword);

        // update account password (encrypted)
        $zm_accounts->_setAccountPassword($account->getId(), $newEncrpytedPassword);

        // send email (clear text)
        $context = array('newPassword' => $newPassword);
        zm_mail(zm_l10n_get("Forgotten Password - %s", zm_setting('storeName')), 'password_forgotten', $context, $emailAddress, $account->getFullName());

        // report success
        $zm_messages->success(zm_l10n_get('A new password has been sent to your email address.'));

        return $this->findView('success');
    }

}

?>
