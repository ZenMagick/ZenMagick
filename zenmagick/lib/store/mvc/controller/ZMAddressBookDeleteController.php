<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Request controller to delete addressbook entry (address).
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 * @version $Id$
 */
class ZMAddressBookDeleteController extends ZMController {

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
    public function preProcess($request) {
        $request->getToolbox()->crumbtrail->addCrumb("Account", $request->getToolbox()->net->url(FILENAME_ACCOUNT, '', true));
        $request->getToolbox()->crumbtrail->addCrumb("Address Book", $request->getToolbox()->net->url(FILENAME_ADDRESS_BOOK, '', true));
        $request->getToolbox()->crumbtrail->addCrumb("Delete");
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $address = ZMAddresses::instance()->getAddressForId($request->getParameter('id'));
        return $this->findView(null, array('address' => $address));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $account = $request->getAccount();
        $addressId = $request->getParameter('id', 0);
        if (0 < $addressId) {
            ZMAddresses::instance()->deleteAddressForId($addressId);
            ZMMessages::instance()->success(zm_l10n_get('The selected address has been successfully removed from your address book.'));
        }
        return $this->findView('success');
    }

}
