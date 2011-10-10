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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * A zencart order based on the shopping cart.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.utils
 */
class ZenCartCheckoutOrder extends ZMObject {

    /**
     * Create new instance for the given shopping cart.
     *
     * @param ZMShoppingCart shoppingCart The shopping cart; default is <code>null</code>
     */
    public function __construct($shoppingCart=null) {
        parent::__construct();
        $this->setShoppingCart($shoppingCart);
    }

    /**
     * Set the shopping cart.
     *
     * @param ZMShoppingCart shoppingCart The shopping cart.
     */
    public function setShoppingCart($shoppingCart) {
        if (null == $shoppingCart) {
            return;
        }

        // type
        $this->content_type = $shoppingCart->getType();

        // general stuff
        $this->info = array(
            //'currency' => 'TODO',
            //'shipping_method' => 'TODO',
            //'shipping_cost' => 'TODO',
            'total' => $shoppingCart->getTotal()
        );

        // account
        $account = $this->container->get('accountService')->getAccountForId($shoppingCart->getAccountId());
        $this->setAccount($account);

        // addresses
        $this->setShippingAddress($shoppingCart->getShippingAddress());
        $this->setBillingAddress($shoppingCart->getBillingAddress());

        // fill with something to have the correct count
        $this->products = $shoppingCart->getItems();
        // id, final_price, tax, model, name, attributes, onetime_charges, qty
        // attributes: option, value
    }

    /**
     * Address to array.
     *
     * @param ZMAddress address The address.
     * @return array Address array.
     */
    protected function address2array($address) {
        $aa = array();
        $aa['country'] = array();
        if (null != $address) {
            $aa['firstname'] = $address->getFirstName();
            $aa['lastname'] = $address->getLastName();
            $aa['street_address'] = $address->getAddressLine1();
            $aa['city'] = $address->getCity();
            $aa['suburb'] = $address->getSuburb();
            $aa['zone_id'] = $address->getZoneId();
            $aa['state'] = $address->getState();
            $aa['postcode'] = $address->getPostcode();
            if (null != ($country = $address->getCountry())) {
                $aa['country']['id'] = $country->getId();
                $aa['country']['title'] = $country->getName();
                $aa['country']['iso_code_2'] = $country->getIsoCode2();
            }
        }
        return $aa;
    }

    /**
     * Set the account.
     *
     * @param ZMAccount account The account.
     */
    public function setAccount($account) {
        $primaryAddress = $this->container->get('addressService')->getAddressForId($account->getDefaultAddressId());
        $customer = $this->address2array($primaryAddress);
        $customer['firstname'] = $account->getFirstName();
        $customer['lastname'] = $account->getLastName();
        $customer['email_address'] = $account->getEmail();
        $this->customer = $customer;
    }

    /**
     * Set shipping address.
     *
     * @param ZMAddress address The shipping address.
     */
    public function setShippingAddress($address) {
        $this->shipping = $this->address2array($address);
    }

    /**
     * Set billing address.
     *
     * @param ZMAddress address The billing address.
     */
    public function setBillingAddress($address) {
        $this->delivery = $this->address2array($address);
    }

}
