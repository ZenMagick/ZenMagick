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
 * Request controller for addressbook processing.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMAddressBookProcessController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMAddressBookProcessController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMAddressBookProcessController();
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
    global $zm_request, $zm_crumbtrail, $zm_addresses;

        $zm_crumbtrail->addCrumb("Account", zm_secure_href(FILENAME_ACCOUNT, '', false));
        $zm_crumbtrail->addCrumb("Address Book", zm_secure_href(FILENAME_ADDRESS_BOOK, '', false));

        $viewName = null;
        if ($zm_request->getParameter('edit')) {
            $zm_crumbtrail->addCrumb("Edit");
            $address = $zm_addresses->getAddressForId($zm_request->getParameter('edit'));
            $this->exportGlobal("zm_address", $address);
            $viewName = 'address_book_edit';
        } else if ($zm_request->getParameter('delete')) {
            $zm_crumbtrail->addCrumb("Delete");
            $address = $zm_addresses->getAddressForId($zm_request->getParameter('delete'));
            $this->exportGlobal("zm_address", $address);
            $viewName = 'address_book_delete';
        } else {
            $zm_crumbtrail->addCrumb("New Entry");
            $this->exportGlobal("zm_address", $this->create("Address"));
            $viewName = 'address_book_create';
        }

        return $this->findView($viewName);
    }

}

?>
