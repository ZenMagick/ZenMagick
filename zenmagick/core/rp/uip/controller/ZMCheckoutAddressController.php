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
 * Request controller for checkout address change (shipping/billing).
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.controller
 * @version $Id$
 */
class ZMCheckoutAddressController extends ZMController {
    private $settings_;

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->setMode('shipping');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Set mode.
     *
     * @param string mode Either <em>shipping</em> or <em>billing</em>; other values will be ignored.
     */
    public function setMode($mode) {
        if ('shipping' == $mode) {
            $this->settings_ = array('url' => 'checkout_shipping', 'method' => 'setShippingAddressId');
        } else if ('billing' == $mode) {
            $this->settings_ = array('url' => 'checkout_billing', 'method' => 'setBillingAddressId');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function handleRequest() {
        ZMCrumbtrail::instance()->addCrumb("Checkout", ZMToolbox::instance()->net->url($this->settings_['url'], '', true, false));
        ZMCrumbtrail::instance()->addCrumb(ZMToolbox::instance()->utils->getTitle(null, false));

        $shoppingCart = ZMRequest::getShoppingCart();
        $this->exportGlobal("zm_cart", $shoppingCart);

        $addressList = ZMAddresses::instance()->getAddressesForAccountId(ZMRequest::getAccountId());
        $this->exportGlobal("zm_addressList", $addressList);
        $address = $this->getFormBean();
        $address->setPrimary(0 == count($addressList));
    }

    /**
     * {@inheritDoc}
     */
    protected function validateFormBean($formBean) {
        $addressId = ZMRequest::getParameter('addressId', null);
        if (null !== $addressId) {
            // selected existing address, so do not validate
          echo $addressId;die();
            return null;
        }
        return parent::validateFormBean($formBean);
    }

    /**
     * {@inheritDoc}
     */
    public function process() {
        $checkoutHelper = ZMLoader::make('CheckoutHelper', ZMRequest::getShoppingCart());
        if (null !== ($viewId = $checkoutHelper->validateCheckout())) {
            return $this->findView($viewId);
        }

        return parent::process();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost() {
        $shoppingCart = ZMRequest::getShoppingCart();
        // which addres do we update?
        $method = $this->settings_['method'];

        // if address field in request, it's a select; otherwise a new address
        $addressId = ZMRequest::getParameter('addressId', null);
        if (null !== $addressId) {
            $shoppingCart->$method($addressId);
        } else {
            $address = $this->getFormBean();
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
            $shoppingCart->$method($address->getId());
        }

        return $this->findView('success');
    }

}

?>
