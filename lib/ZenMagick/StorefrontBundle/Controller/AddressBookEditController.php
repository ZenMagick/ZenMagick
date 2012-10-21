<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\StorefrontBundle\Controller;


/**
 * Request controller for addressbook editing.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AddressBookEditController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        // populate with original data
        $address = $this->container->get('addressService')->getAddressForId($request->getParameter('id'));
        $account = $this->getUser();

        if ($account->getId() != $address->getAccountId()) {
            $this->messageService->error(_zm('Address not found'));
            return $this->findView('error');
        }

        return $this->findView(null, array('address' => $address));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $address = $this->getFormData($request);
        $addressService = $this->container->get('addressService');
        $account = $this->getUser();

        if (1 == count($addressService->getAddressesForAccountId($account->getId()))) {
            $address->setPrimary(true);
        }

        $address->setAccountId($account->getId());
        $addressService->updateAddress($address);

        // process primary setting
        if ($address->isPrimary()) {
            if ($account->getDefaultAddressId() != $address->getId()) {
                $account->setDefaultAddressId($address->getId());
                $this->container->get('accountService')->updateAccount($account);

                $session = $request->getSession();
                $session->setAccount($account);
            }
        }

        $this->messageService->success(_zm('The selected address has been successfully updated.'));
        return $this->findView('success');
    }

}
