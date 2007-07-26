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
 * Request controller for account creation page.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMCreateAccountController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMCreateAccountController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMCreateAccountController();
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
    global $zm_request, $zm_crumbtrail;

        $zm_crumbtrail->addCrumb("Account", zm_secure_href(FILENAME_ACCOUNT, '', false));
        $zm_crumbtrail->addCrumb(zm_title(false));

        $account =& $this->create("Account");
        $account->populate();
        $address =& $this->create("Address");
        $address->populate();

        $this->exportGlobal("zm_account", $account);
        $this->exportGlobal("zm_address", $address);

        return $this->findView();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
    global $zm_request, $zm_messages, $zm_accounts, $zm_addresses;

        $account =& $this->create("Account");
        $account->populate();

        $address =& $this->create("Address");
        $address->populate();

        if (!$this->validate('create_account')) {
            $this->exportGlobal("zm_account", $account);
            $this->exportGlobal("zm_address", $address);
            return $this->findView();
        }

        // hen and egg...
        $account->setPassword(zm_encrypt_password($account->getPassword()));
        $account = $zm_accounts->createAccount($account);

        $address->setAccountId($account->getId());
        $address = $zm_addresses->createAddress($address);

        $account->setDefaultAddressId($address->getId());
        $zm_accounts->updateAccount($account);

        zm_bb_create_account($account->getNickName(), $account->getEmail(), $account->getPassword());

        $session = new ZMSession();
        $session->recreate();
        $session->setAccount($account);
        $session->restoreCart();

        // account email
        $context = array('zm_account' => $account);
        zm_mail(zm_l10n_get("Welcome to %s", zm_setting('storeName')), 'welcome', $context, $account->getEmail(), $account->getFullName());
        if (zm_setting('isEmailAdminCreateAccount')) {
            // store copy
            $context = array_merge($context, zm_email_copy_context($account, $session));
            zm_mail(zm_l10n_get("Welcome to %s", zm_setting('storeName')), 'welcome', $context, zm_setting('emailAdminCreateAccount'));
        }

        $this->exportGlobal("zm_account", $account);

        return $this->findView('success');
    }

}

?>
