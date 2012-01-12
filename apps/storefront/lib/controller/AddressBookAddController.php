<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\apps\store\storefront\controller;

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;

/**
 * Request controller for adding an address.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AddressBookAddController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        $request->getToolbox()->crumbtrail->addCrumb("Account", $request->url('account', '', true));
        $request->getToolbox()->crumbtrail->addCrumb("Address Book", $request->url('address_book', '', true));
        $request->getToolbox()->crumbtrail->addCrumb("New Entry");
    }

    /**
     *{@inheritDoc}
     */
    public function processPost($request) {
        $addressService = $this->container->get('addressService');
        $address = $this->getFormData($request);
        $address->setAccountId($request->getAccountId());
        $address = $addressService->createAddress($address);

        $account = $request->getAccount();
        $args = array('request' => $request, 'controller' => $this, 'account' => $account, 'address' => $address, 'type' => 'addressBook');
        Runtime::getEventDispatcher()->dispatch('create_address', new Event($this, $args));

        // process primary setting
        if ($address->isPrimary() || 1 == count($addressService->getAddressesForAccountId($request->getAccountId()))) {
            $account = $request->getAccount();
            $account->setDefaultAddressId($address->getId());
            $this->container->get('accountService')->updateAccount($account);
            $address->setPrimary(true);
            $addressService->updateAddress($address);

            $session = $request->getSession();
            $session->setAccount($account);
        }

        // if guest, there is no address book!
        if ($request->isRegistered()) {
            $this->messageService->success(_zm('Address added to your address book.'));
        }

        return $this->findView('success');
    }

}
