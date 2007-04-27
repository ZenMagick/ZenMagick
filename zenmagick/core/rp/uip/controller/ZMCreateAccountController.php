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

        return $this->findView('create_account');
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function XprocessPost() {
    global $zm_request, $zm_messages, $zm_accounts, $zm_addresses;

        $account =& $this->create("Account");
        $account->populate();
        $address =& $this->create("Address");
        $address->populate();

        $valid = true;
        if ($zm_request->getRequestParameter('action') != 'process') {
            $zm_messages->warn(zm_l10n_get("Incomplete request."));
            $valid = false;
        }

        // validate; *always* execute both to create all error messages
        if (!$account->isValid() | !$address->isValid()) {
            $valid = false;
        }

        if (zm_setting('isPrivacyMessage') && null == $zm_request->getRequestParameter('privacy_conditions')) {
            $zm_messages->error(zm_l10n_get("You must confirm the privacy statement in order to register."));
            $valid = false;
        }

        if ($account->getPassword() != $zm_request->getRequestParameter('confirmation')) {
            $zm_messages->error(zm_l10n_get("The Password Confirmation must match your Password."));
            $valid = false;
        }

        if ($valid) {
            // process
            $account = $zm_accounts->createAccount($account);
            echo $account->getId();
            // TODO: the rest :)
        }

        $this->exportGlobal("zm_account", $account);
        $this->exportGlobal("zm_address", $address);

        // TODO
        return true;
    }

}

?>
