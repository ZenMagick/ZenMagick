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
 * @package org.zenmagick.store.mvc.controller
 * @version $Id: ZMCheckoutAddressController.php 2197 2009-05-04 03:44:37Z dermanomann $
 */
class ZMCheckoutAddressController extends ZMController {
    private $settings_;
    private $viewData_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->settings_ = array();
        $this->viewData_ = array();
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
    public function handleRequest($request) {
        ZMCrumbtrail::instance()->addCrumb("Checkout", ZMToolbox::instance()->net->url($this->settings_['url'], '', true, false));
        ZMCrumbtrail::instance()->addCrumb(ZMToolbox::instance()->utils->getTitle(null, false));

        $shoppingCart = $request->getShoppingCart();
        $this->viewData_['zm_cart'] = $shoppingCart;

        $addressList = ZMAddresses::instance()->getAddressesForAccountId($request->getAccountId());
        $this->viewData_['zm_addressList'] = $zm_addressList;
        $address = $this->getFormBean($request);
        $address->setPrimary(0 == count($addressList));
    }

    /**
     * {@inheritDoc}
     */
    protected function validateFormBean($request, $formBean) {
        $addressId = $request->getParameter('addressId', null);
        if (null !== $addressId) {
            // selected existing address, so do not validate
            return null;
        }
        return parent::validateFormBean($request, $formBean);
    }

    /**
     * {@inheritDoc}
     */
    public function process($request) {
        $checkoutHelper = ZMLoader::make('CheckoutHelper', $request->getShoppingCart());
        if (null !== ($viewId = $checkoutHelper->validateCheckout())) {
            return $this->findView($viewId, $this->viewData_);
        }

        return parent::process($request);
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $shoppingCart = $request->getShoppingCart();
        // which addres do we update?
        $method = $this->settings_['method'];

        // if address field in request, it's a select; otherwise a new address
        $addressId = $request->getParameter('addressId', null);
        if (null !== $addressId) {
            $shoppingCart->$method($addressId);
        } else {
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
            $shoppingCart->$method($address->getId());
        }

        return $this->findView('success', $this->viewData_);
    }

}

?>
