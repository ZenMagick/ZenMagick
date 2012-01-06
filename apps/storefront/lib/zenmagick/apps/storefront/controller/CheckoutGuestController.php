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
namespace zenmagick\apps\storefront\controller;

use zenmagick\base\Runtime;

/**
 * Request controller for guest checkout.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CheckoutGuestController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (!Runtime::getSettings()->get('isGuestCheckout')) {
            $this->messageService->warn(_zm('Guest checkout not allowed at this time'));
            return $this->findView('guest_checkout_disabled');
        }

        // our session
        $session = $request->getSession();

        if (!$session->isAnonymous()) {
            // already logged in either way
            return $this->findView('success');
        }

        $address = $this->getFormData($request,'ZMAddress', 'checkout_guest');
        $address->setPrimary(true);
        if (!$this->validate($request, 'checkout_guest')) {
            return $this->findView(null, array('guestCheckoutAddress' => $address));
        }

        // create anonymous account
        $account = Runtime::getContainer()->get("ZMAccount");
        $account->setEmail($request->getParameter('email_address'));
        $account->setPassword('');
        $account->setDob(\ZMDatabase::NULL_DATETIME);
        $account->setType(\ZMAccount::GUEST);
        $account = $this->container->get('accountService')->createAccount($account);

        // update session with valid account
        $session->regenerate();
        $session->setAccount($account);

        if (Runtime::getSettings()->get('isGuestCheckoutAskAddress')) {
            // double check
            $lastName = $address->getLastName();
            if (!empty($lastName)) {
                $address->setAccountId($account->getAccountId());
                $address = $this->container->get('addressService')->createAddress($address);
                $account->setDefaultAddressId($address->getId());
                $this->container->get('accountService')->updateAccount($account);
                // use as shipping/billing address
                $shoppingCart = $request->getShoppingCart();
                $shoppingCart->setShippingAddressId($address->getId());
                $shoppingCart->setBillingAddressId($address->getId());
            }
        }

        return $this->findView('success');
    }

}
