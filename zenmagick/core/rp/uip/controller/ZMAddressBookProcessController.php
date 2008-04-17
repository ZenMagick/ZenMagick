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
 * Request controller for addressbook processing.
 *
 * @author mano
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMAddressBookProcessController extends ZMController {

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
        ZMCrumbtrail::instance()->addCrumb("Account", ZMToolbox::instance()->net->url(FILENAME_ACCOUNT, '', true, false));
        ZMCrumbtrail::instance()->addCrumb("Address Book", zm_secure_href(FILENAME_ADDRESS_BOOK, '', false));

        return parent::process();
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
        $viewName = null;
        if (ZMRequest::getParameter('edit')) {
            ZMCrumbtrail::instance()->addCrumb("Edit");
            $address = ZMAddresses::instance()->getAddressForId(ZMRequest::getParameter('edit'));
            // set the original isPrimary status to avoid hiding the tickbox when selected, but validation fails
            $address->set('_isPrimary', $address->isPrimary());
            $this->exportGlobal("zm_address", $address);
            $viewName = 'address_book_edit';
        } else if (ZMRequest::getParameter('delete')) {
            ZMCrumbtrail::instance()->addCrumb("Delete");
            $address = ZMAddresses::instance()->getAddressForId(ZMRequest::getParameter('delete'));
            $this->exportGlobal("zm_address", $address);
            $viewName = 'address_book_delete';
        } else {
            ZMCrumbtrail::instance()->addCrumb("New Entry");
            $this->exportGlobal("zm_address", ZMLoader::make("Address"));
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
        $action = ZMRequest::getParameter('action');
        $view = null;
        if ('update' == $action) {
            ZMCrumbtrail::instance()->addCrumb("Edit");
            $view = $this->updateAddress();
        } else if ('deleteconfirm' == $action) {
            ZMCrumbtrail::instance()->addCrumb("Delete");
            $view = $this->deleteAddress();
        } else if ('process' == $action) {
            ZMCrumbtrail::instance()->addCrumb("New Entry");
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
        $address = ZMLoader::make("Address");
        $address->populate();
        // preserve original status
        $address->set('_isPrimary', ZMRequest::getParameter('_isPrimary', false));

        if (!$this->validate('addressObject', $address)) {
            $this->exportGlobal("zm_address", $address);
            return $this->findView('address_book_edit');
        }

        $address = ZMAddresses::instance()->updateAddress($address);

        // process primary setting
        if ($address->isPrimary()) {
            $account = ZMRequest::getAccount();
            if ($account->getDefaultAddressId() != $address->getId()) {
                $account->setDefaultAddressId($address->getId());
                ZMAccounts::instance()->updateAccount($account);

                $session = ZMRequest::getSession();
                $session->setAccount($account);
            }
        }

        ZMMessages::instance()->success(zm_l10n_get('The selected address has been successfully updated.'));
        return $this->findView('success');
    }

    /**
     * Delete address.
     *
     * @return ZMView The result view.
     */
    function deleteAddress() {
        $account = ZMRequest::getAccount();
        $addressId = ZMRequest::getParameter('addressId', 0);
        if (0 < $addressId) {
            ZMAddresses::instance()->deleteAddressForId($addressId);
            ZMMessages::instance()->success(zm_l10n_get('The selected address has been successfully removed from your address book.'));
        }
        return $this->findView('success');
    }

    /**
     * Create address.
     *
     * @return ZMView The result view.
     */
    function createAddress() {
        $address = ZMLoader::make("Address");
        $address->populate();
        $address->setAccountId(ZMRequest::getAccountId());

        if (!$this->validate('addressObject', $address)) {
            $this->exportGlobal("zm_address", $address);
            return $this->findView('address_book_create');
        }

        $address = ZMAddresses::instance()->createAddress($address);

        // process primary setting
        if ($address->isPrimary() || 1 == count(ZMAddresses::instance()->getAddressesForAccountId(ZMRequest::getAccountId()))) {
            $account = ZMRequest::getAccount();
            $account->setDefaultAddressId($address->getId());
            ZMAccounts::instance()->updateAccount($account);

            $session = ZMRequest::getSession();
            $session->setAccount($account);
        }

        $this->exportGlobal("zm_address", $address);

        // if guest, there is no address book!
        if (ZMRequest::isRegistered()) {
            ZMMessages::instance()->success(zm_l10n_get('Address added to your address book.'));
        }

        return $this->findView('success');
    }

}

?>
