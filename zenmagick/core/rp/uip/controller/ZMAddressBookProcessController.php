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
 * @package org.zenmagick.rp.uip.controller
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
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    function process() { 
    global $zm_crumbtrail;

        $zm_crumbtrail->addCrumb("Account", zm_secure_href(FILENAME_ACCOUNT, '', false));
        $zm_crumbtrail->addCrumb("Address Book", zm_secure_href(FILENAME_ADDRESS_BOOK, '', false));

        return parent::process();
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
    global $zm_request, $zm_crumbtrail, $zm_addresses;

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

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
    global $zm_request, $zm_crumbtrail;
        
        $action = $zm_request->getParameter('action');
        $view = null;
        if ('update' == $action) {
            $zm_crumbtrail->addCrumb("Edit");
            $view = $this->updateAddress();
        } else if ('deleteconfirm' == $action) {
            $zm_crumbtrail->addCrumb("Delete");
            $view = $this->deleteAddress();
        } else if ('process' == $action) {
            $zm_crumbtrail->addCrumb("New Entry");
            $view = $this->createAddress();
        }

        return $view;
    }

    /**
     * Update address.
     *
     * @return ZMView The result view.
     */
    function updateAddress() {
    global $zm_request, $zm_addresses, $zm_accounts, $zm_messages;

        $address = $this->create("Address");
        $address->populate();

        if (!$this->validate('addressObject', $address)) {
            $this->exportGlobal("zm_address", $address);
            return $this->findView('address_book_edit');
        }

        $address = $zm_addresses->updateAddress($address);

        // process primary setting
        if ($address->isPrimary()) {
            $account = $zm_request->getAccount();
            $account->setDefaultAddressId($address->getId());
            $zm_accounts->updateAccount($account);

            $session = $zm_request->getSession();
            $session->setAccount($account);
        }

        $zm_messages->success(zm_l10n_get('The selected address has been successfully updated.'));
        return $this->findView('success');
    }

    /**
     * Delete address.
     *
     * @return ZMView The result view.
     */
    function deleteAddress() {
    global $zm_addresses, $zm_request, $zm_messages;

        $account = $zm_request->getAccount();
        $addressId = $zm_request->getParameter('addressId', 0);
        if (0 < $addressId) {
            $zm_addresses->deleteAddressForId($addressId);
            $zm_messages->success(zm_l10n_get('The selected address has been successfully removed from your address book.'));
        }
        return $this->findView('success');
    }

    /**
     * Create address.
     *
     * @return ZMView The result view.
     */
    function createAddress() {
    global $zm_addresses, $zm_accounts, $zm_request, $zm_messages;

        $address = $this->create("Address");
        $address->populate();
        $address->setAccountId($zm_request->getAccountId());

        if (!$this->validate('addressObject', $address)) {
            $this->exportGlobal("zm_address", $address);
            return $this->findView('address_book_create');
        }

        $address = $zm_addresses->createAddress($address);

        // process primary setting
        if ($address->isPrimary() || 1 == count($zm_addresses->getAddressesForAccountId($zm_request->getAccountId()))) {
            $account = $zm_request->getAccount();
            $account->setDefaultAddressId($address->getId());
            $zm_accounts->updateAccount($account);

            $session = $zm_request->getSession();
            $session->setAccount($account);
        }

        $this->exportGlobal("zm_address", $address);

        // if guest, there is no address book!
        if ($zm_request->isRegistered()) {
            $zm_messages->success(zm_l10n_get('Address added to your address book.'));
        }

        return $this->findView('success');
    }

}

?>
