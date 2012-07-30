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
namespace zenmagick\apps\storefront\controller;

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;

/**
 * Request controller for checkout address change (shipping/billing).
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CheckoutAddressController extends \ZMController {
    private $modeSettings_;
    private $viewData_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->modeSettings_ = array();
        $this->viewData_ = array();
        $this->setMode('shipping');
    }


    /**
     * Set mode.
     *
     * @param string mode Either <em>shipping</em> or <em>billing</em>; other values will be ignored.
     */
    public function setMode($mode) {
        if ('shipping' == $mode) {
            $this->modeSettings_ = array('method' => 'setShippingAddressId', 'ignoreCheckId' => 'require_shipping', 'mode' => $mode);
        } else if ('billing' == $mode) {
            $this->modeSettings_ = array('method' => 'setBillingAddressId', 'ignoreCheckId' => 'require_payment', 'mode' => $mode);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        $shoppingCart = $request->getShoppingCart();
        $this->viewData_['shoppingCart'] = $shoppingCart;

        $addressList = $this->container->get('addressService')->getAddressesForAccountId($this->getUser()->getId());
        $this->viewData_['addressList'] = $addressList;
        if (null != ($address = $this->getFormData($request))) {
            $address->setPrimary(0 == count($addressList));
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function validateFormData($request, $formBean) {
        $addressId = $request->request->get('addressId', null);
        if (null !== $addressId) {
            // selected existing address, so do not validate
            return null;
        }

        if (null != ($view = parent::validateFormData($request, $formBean))) {
            // validation failed, so let's add our required view data
            $view->setVariables($this->viewData_);
        }

        return $view;
    }

    /**
     * Custom cart checker
     */
    protected function checkCart($request) {
        $checkoutHelper = $request->getShoppingCart()->getCheckoutHelper();
        if (null !== ($viewId = $checkoutHelper->validateCheckout($request, false)) && $this->modeSettings_['ignoreCheckId'] != $viewId) {
            return $this->findView($viewId, $this->viewData_);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        if (null != ($result = $this->checkCart($request))) {
            return $result;
        }

        return $this->findView(null, $this->viewData_);
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (null != ($result = $this->checkCart($request))) {
            return $result;
        }

        $shoppingCart = $request->getShoppingCart();
        $addressService = $this->container->get('addressService');
        // which addres do we update?
        $method = $this->modeSettings_['method'];


        // if address field in request, it's a select; otherwise a new address
        $addressId = $request->request->get('addressId', null);
        if (null !== $addressId) {
            $shoppingCart->$method($addressId);
        } else {
            $account = $this->getUser();
            $address = $this->getFormData($request);
            $address->setAccountId($account->getId());
            $address = $addressService->createAddress($address);

            $args = array('request' => $request, 'controller' => $this, 'account' => $account, 'address' => $address, 'type' => $this->settings_['mode']);
            Runtime::getEventDispatcher()->dispatch('create_address', new Event($this, $args));

            // process primary setting
            if ($address->isPrimary() || 1 == count($addressService->getAddressesForAccountId($account->getId()))) {
                $account->setDefaultAddressId($address->getId());
                $this->container->get('accountService')->updateAccount($account);
                $address->setPrimary(true);
                $addressService->updateAddress($address);

                $session = $request->getSession();
                $session->setAccount($account);
            }
            $shoppingCart->$method($address->getId());
        }

        return $this->findView('success', $this->viewData_);
    }

}
