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
 * Request controller for gv send page.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMGvSendController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMGvSendController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMGvSendController();
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
    global $zm_request, $zm_crumbtrail, $zm_messages;

        $zm_crumbtrail->addCrumb("Account", zm_secure_href(FILENAME_ACCOUNT, '', false));
        $zm_crumbtrail->addCrumb(zm_title(false));

        $this->exportGlobal("zm_account", $zm_request->getAccount());
        $this->exportGlobal("zm_gvreceiver", $this->create("GVReceiver"));

        $view =& $this->create("ThemeView", 'gv_send');
        if ('doneprocess' == $zm_request->getRequestParameter('action')) {
            $zm_messages->success(zm_l10n_get("Gift Certificate successfully send."));
            //$view =& new ZMRedirectView('account');
        }

        return $view;
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
    global $zm_request, $zm_crumbtrail, $zm_messages;
    // zen header stuff
    global $error_amount, $error_email;

        $zm_crumbtrail->addCrumb("Account", zm_secure_href(FILENAME_ACCOUNT, '', false));
        $zm_crumbtrail->addCrumb(zm_title(false));

        $this->exportGlobal("zm_account", $zm_request->getAccount());
        $receiver = $this->create("GVReceiver");
        $receiver->populate();
        $this->exportGlobal("zm_gvreceiver", $receiver);

        // error handling
        if (!zm_is_empty($error_amount)) {
            $zm_messages->error(zm_l10n_get("Please enter a valid amount."));
        }
        if (!zm_is_empty($error_email)) {
            $zm_messages->error(zm_l10n_get("Please enter a valid email address."));
        }

        $viewName = 'success';
        if ('send' == $zm_request->getRequestParameter('action') && !$zm_messages->hasMessages()) {
            if (null != $zm_request->getRequestParameter('edit_x', null)) {
                $viewName = null;
            } else {
                $viewName = 'confirm';
            }
        }

        return $this->findView($viewName);
    }

}

?>
