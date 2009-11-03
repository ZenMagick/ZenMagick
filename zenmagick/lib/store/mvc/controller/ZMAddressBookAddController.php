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
 * Request controller for adding an address.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id: ZMAddressBookAddController.php 2090 2009-03-20 05:05:38Z dermanomann $
 */
class ZMAddressBookAddController extends ZMController {

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
    public function handleRequest($request) {
        $request->getCrumbtrail()->addCrumb("Account", $request->getToolbox()->net->url(FILENAME_ACCOUNT, '', true, false));
        $request->getCrumbtrail()->addCrumb("Address Book", $request->getToolbox()->net->url(FILENAME_ADDRESS_BOOK, '', true, false));
        $request->getCrumbtrail()->addCrumb("New Entry");
    }

    /**
     *{@inheritDoc}
     */
    public function processPost($request) {
        $address = $this->getFormBean($request);
        $address->setAccountId($request->getAccountId());
        $address = ZMAddresses::instance()->createAddress($address);

        // process primary setting
        if ($address->isPrimary() || 1 == count(ZMAddresses::instance()->getAddressesForAccountId($request->getAccountId()))) {
            $account = $request->getAccount();
            $account->setDefaultAddressId($address->getId());
            ZMAccounts::instance()->updateAccount($account);
            $address->setPrimary(true);
            $address = ZMAddresses::instance()->updateAddress($address);

            $session = $request->getSession();
            $session->setAccount($account);
        }

        // if guest, there is no address book!
        if ($request->isRegistered()) {
            ZMMessages::instance()->success(zm_l10n_get('Address added to your address book.'));
        }

        return $this->findView('success');
    }

}

?>
