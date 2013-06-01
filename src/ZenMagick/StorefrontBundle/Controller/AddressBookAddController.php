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

use ZenMagick\ZenMagickBundle\Controller\DefaultController;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Request controller for adding an address.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AddressBookAddController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        $addressService = $this->container->get('addressService');
        $account = $this->getUser();
        $address = $this->getFormData($request);
        $address->setAccountId($account->getId());
        $address = $addressService->createAddress($address);

        $args = array('request' => $request, 'controller' => $this, 'account' => $account, 'address' => $address, 'type' => 'addressBook');
        $this->container->get('event_dispatcher')->dispatch('create_address', new GenericEvent($this, $args));

        $session = $request->getSession();
        // process primary setting
        if ($address->isPrimary() || 1 == count($addressService->getAddressesForAccountId($account->getId()))) {
            $account->setDefaultAddressId($address->getId());
            $this->container->get('accountService')->updateAccount($account);
            $address->setPrimary(true);
            $addressService->updateAddress($address);

            $session->setAccount($account);
        }

        // if guest, there is no address book!
        if ($session->isRegistered()) {
            $message = $this->get('translator')->trans('Address added to your address book.');
            $this->get('session.flash_bag')->success($message);
        }

        return $this->findView('success');
    }

}
