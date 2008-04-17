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
        $emailAddress = ZMRequest::getParameter('email_address');
        $account = ZMAccounts::instance()->getAccountForEmailAddress($emailAddress);
        if (null === $account || ZM_ACCOUNT_TYPE_REGISTERED != $account->getType()) {
            ZMMessages::instance()->error(zm_l10n_get("Sorry, there is no account with the email address '%s'.", $emailAddress));
            return $this->findView();
        }

        $newPassword = zm_new_password();
        $newEncrpytedPassword = zm_encrypt_password($newPassword);

        // update account password (encrypted)
        ZMAccounts::instance()->_setAccountPassword($account->getId(), $newEncrpytedPassword);

        // send email (clear text)
        $context = array('newPassword' => $newPassword);
        zm_mail(zm_l10n_get("Forgotten Password - %s", ZMSettings::get('storeName')), 'password_forgotten', $context, $emailAddress, $account->getFullName());

        // report success
        ZMMessages::instance()->success(zm_l10n_get('A new password has been sent to your email address.'));

        return $this->findView('success');
    }

}

?>
