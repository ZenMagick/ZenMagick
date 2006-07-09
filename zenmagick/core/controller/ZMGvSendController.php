<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * Request controller for gv send page.
 *
 * @author mano
 * @package net.radebatz.zenmagick.controller
 * @version $Id$
 */
class ZMGvSendController extends ZMRequestController {

    // create new instance
    function ZMGvSendController() {
        parent::__construct();
    }

    // create new instance
    function __construct() {
        $this->ZMGvSendController();
    }

    function __destruct() {
    }


    /** API implementation */

    // process a GET request
    function processGet() {
    global $zm_request, $zm_crumbtrail, $zm_accounts, $zm_messages;

        $zm_crumbtrail->addCrumb("Account", zm_secure_href(FILENAME_ACCOUNT, '', false));
        $zm_crumbtrail->addCrumb(zm_nice_page_name());

        $this->exportGlobal("zm_account", $zm_accounts->getAccountForId($zm_request->getAccountId()));
        $this->exportGlobal("zm_gvreceiver", new ZMGVReceiver());

        if ('doneprocess' == $zm_request->getRequestParameter('action')) {
            $zm_messages->add(zm_l10n_get("Gift Certificate successfully send."), 'msg');
            //$this->setResponseView(new ZMView('account', 'account'));
        }
        return true;
    }

    // process a POST request
    function processPost() {
    global $zm_request, $zm_crumbtrail, $zm_accounts, $zm_messages;
    // zen header stuff
    global $error_amount, $error_email;

        $zm_crumbtrail->addCrumb("Account", zm_secure_href(FILENAME_ACCOUNT, '', false));
        $zm_crumbtrail->addCrumb(zm_nice_page_name());

        $this->exportGlobal("zm_account", $zm_accounts->getAccountForId($zm_request->getAccountId()));
        $receiver = new ZMGVReceiver();
        $receiver->populateFromRequest();
        $this->exportGlobal("zm_gvreceiver", $receiver);

        // error handling
        if (zm_not_null($error_amount)) {
            $zm_messages->add(zm_l10n_get("Please enter a valid amount."));
        }
        if (zm_not_null($error_email)) {
            $zm_messages->add(zm_l10n_get("Please enter a valid email address."));
        }

        if ('send' == $zm_request->getRequestParameter('action') && !$zm_messages->hasMessages()) {
            $this->setResponseView(new ZMView('gv_send_confirm', 'gv_send_confirm'));
        }

        return true;
    }

}

?>
