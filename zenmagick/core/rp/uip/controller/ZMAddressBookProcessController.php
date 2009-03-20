<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * @author DerManoMann
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
     *{@inheritDoc}
     */
    public function handleRequest() { 
        ZMCrumbtrail::instance()->addCrumb("Account", ZMToolbox::instance()->net->url(FILENAME_ACCOUNT, '', true, false));
        ZMCrumbtrail::instance()->addCrumb("Address Book", ZMToolbox::instance()->net->url(FILENAME_ADDRESS_BOOK, '', true, false));
    }

    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processGet() {
        $viewName = null;
        if (ZMRequest::getParameter('edit')) {
            ZMCrumbtrail::instance()->addCrumb("Edit");
            $address = ZMAddresses::instance()->getAddressForId(ZMRequest::getParameter('edit'));
            var_dump($address);
            $this->exportGlobal('address', $address);
            $viewName = 'edit';
        } else if (ZMRequest::getParameter('delete')) {
            ZMCrumbtrail::instance()->addCrumb("Delete");
            $address = ZMAddresses::instance()->getAddressForId(ZMRequest::getParameter('delete'));
            $this->exportGlobal('address', $address);
            $viewName = 'delete';
        } else {
            ZMCrumbtrail::instance()->addCrumb("New Entry");
            $viewName = 'create';
        }

        return $this->findView($viewName);
    }

    /**
     * {@inheritDoc}
     */
    protected function validateFormBean($formBean) {
        // need specific view to go back to in case of validation errors
        $result = parent::validateFormBean($formBean);
        if (null != $result) {
            $action = ZMRequest::getParameter('action');
            $viewName = null;
            if ('edit' == $action) {
                $viewName = 'edit';
            } else if ('process' == $action) {
                $viewName = 'create';
            }
            return $this->findView($viewName);
        }
        return null;
    }

    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processPost() {
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
    protected function updateAddress() {
        $address = $this->formBean();
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
    protected function deleteAddress() {
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
    protected function createAddress() {
        $address = $this->formBean();
        $address->setAccountId(ZMRequest::getAccountId());
        $address = ZMAddresses::instance()->createAddress($address);

        // process primary setting
        if ($address->isPrimary() || 1 == count(ZMAddresses::instance()->getAddressesForAccountId(ZMRequest::getAccountId()))) {
            $account = ZMRequest::getAccount();
            $account->setDefaultAddressId($address->getId());
            ZMAccounts::instance()->updateAccount($account);
            $address->setPrimary(true);
            $address = ZMAddresses::instance()->updateAddress($address);

            $session = ZMRequest::getSession();
            $session->setAccount($account);
        }

        // if guest, there is no address book!
        if (ZMRequest::isRegistered()) {
            ZMMessages::instance()->success(zm_l10n_get('Address added to your address book.'));
        }

        return $this->findView('success');
    }

}

?>
