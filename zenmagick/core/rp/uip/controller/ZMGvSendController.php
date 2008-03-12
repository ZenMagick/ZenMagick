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
 * Request controller for gv send page.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMGvSendController extends ZMController {

    /**
     * Create new instance.
     */
    function ZMGvSendController() {
        parent::__construct();
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->ZMGvSendController();
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
        ZMCrumbtrail::instance()->addCrumb("Account", zm_secure_href(FILENAME_ACCOUNT, '', false));
        ZMCrumbtrail::instance()->addCrumb(zm_title(false));

        return parent::process();
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
        $this->exportGlobal("zm_account", ZMRequest::getAccount());
        $this->exportGlobal("zm_gvreceiver", $this->create("GVReceiver"));

        return $this->findView();
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
        $this->exportGlobal("zm_account", ZMRequest::getAccount());
        $gvreceiver = $this->create("GVReceiver");
        $gvreceiver->populate();
        $this->exportGlobal("zm_gvreceiver", $gvreceiver);

        // back from confirmation to edit or not valid
        if (null != ZMRequest::getParameter('edit') || !$this->validate('gvreceiverObject', $gvreceiver)) {
            return $this->findView();
        }

        // to fakce the email content display
        $this->exportGlobal("zm_coupon", $this->create("Coupon", 0, zm_l10n_get('THE_COUPON_CODE')));

        return $this->findView('success');
    }

}

?>
