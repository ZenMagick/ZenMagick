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
 * Request controller for addressbook editing.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMAddressBookEditController extends ZMController {

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
     * {@inheritDoc}
     */
    public function handleRequest() {
        ZMCrumbtrail::instance()->addCrumb("Account", ZMToolbox::instance()->net->url(FILENAME_ACCOUNT, '', true, false));
        ZMCrumbtrail::instance()->addCrumb("Address Book", ZMToolbox::instance()->net->url(FILENAME_ADDRESS_BOOK, '', true, false));
        ZMCrumbtrail::instance()->addCrumb("Edit");
    }

    /**
     * {@inheritDoc}
     */
    public function processGet() {
        // populate with original data
        $address = ZMAddresses::instance()->getAddressForId(ZMRequest::getParameter('id'));
        return $this->findView(null, array('address' => $address));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost() {
        $address = $this->getFormBean();

        if (1 == count(ZMAddresses::instance()->getAddressesForAccountId(ZMRequest::getAccountId()))) {
            $address->setPrimary(true);
        }

        $address->setAccountId(ZMRequest::getAccountId());
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

}

?>
