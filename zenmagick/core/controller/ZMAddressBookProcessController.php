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
 * Request controller for addressbook processing.
 *
 * @author mano
 * @package net.radebatz.zenmagick.controller
 * @version $Id$
 */
class ZMAddressBookProcessController extends ZMController {

    // create new instance
    function ZMAddressBookProcessController() {
        parent::__construct();
    }

    // create new instance
    function __construct() {
        $this->ZMAddressBookProcessController();
    }

    function __destruct() {
    }


    /** API implementation */

    // process a GET request
    function processGet() {
    global $zm_request, $zm_crumbtrail, $zm_addresses;

        $zm_crumbtrail->addCrumb("Account", zm_secure_href(FILENAME_ACCOUNT, '', false));
        $zm_crumbtrail->addCrumb("Address Book", zm_secure_href(FILENAME_ADDRESS_BOOK, '', false));

        if ($zm_request->getRequestParameter('edit')) {
            $zm_crumbtrail->addCrumb("Edit");
            $address = $zm_addresses->getAddressForId($zm_request->getRequestParameter('edit'));
            $this->exportGlobal("zm_address", $address);
            $this->setResponseView(new ZMView("address_book_edit", "address_book_edit"));
        } else if ($zm_request->getRequestParameter('delete')) {
            $zm_crumbtrail->addCrumb("Delete");
            $address = $zm_addresses->getAddressForId($zm_request->getRequestParameter('delete'));
            $this->exportGlobal("zm_address", $address);
            $this->setResponseView(new ZMView("address_book_delete", "address_book_delete"));
        } else {
            $zm_crumbtrail->addCrumb("New Entry");
            $this->exportGlobal("zm_address", new ZMAddress());
            $this->setResponseView(new ZMView("address_book_create", "address_book_create"));
        }

        return true;
    }

}

?>
