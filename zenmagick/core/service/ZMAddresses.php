<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Addresses.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMAddresses extends ZMObject {

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
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Addresses');
    }


    /**
     * Get the address for the given id.
     *
     * @param int addressId The address id.
     * @return ZMAddress The address or <code>null</code>.
     */
    public function getAddressForId($addressId) {
        $sql = "select *
                from " . TABLE_ADDRESS_BOOK . "
                where address_book_id = :id";
        $address = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $addressId), TABLE_ADDRESS_BOOK, 'Address');
        if (null != $address) {
            $defaultAddressId = $this->getDefaultAddressId($address->getAccountId());
            $address->setPrimary($address->getId() == $defaultAddressId);
        }

        return $address;
    }


    /**
     * Get all addresses for the given account id.
     *
     * @param int accountId The account id.
     * @return array A list of <code>ZMAddress</code> instances.
     */
    public function getAddressesForAccountId($accountId) {
        $sql = "select *
                from " . TABLE_ADDRESS_BOOK . "
                where customers_id = :accountId";
        $addresses = ZMRuntime::getDatabase()->query($sql, array('accountId' => $accountId), TABLE_ADDRESS_BOOK, 'Address');

        $defaultAddressId = $this->getDefaultAddressId($accountId);
        foreach ($addresses as $address) {
            $address->setPrimary($address->getId() == $defaultAddressId);
        }

        return $addresses;
    }


    /**
     * Update the given address.
     *
     * @param ZMAddress account The address.
     * @return ZMAddress The updated address.
     */
    public function updateAddress($address) {
        ZMRuntime::getDatabase()->updateModel(TABLE_ADDRESS_BOOK, $address);
        return $address;
    }


    /**
     * Create a new address.
     *
     * @param ZMAddress The new address.
     * @return ZMAddress The created address incl. the new address id.
     */
    public function createAddress($address) {
        ZMRuntime::getDatabase()->createModel(TABLE_ADDRESS_BOOK, $address);
        return $address;
    }


    /**
     * Delte an address.
     *
     * @param int The address id.
     * @param boolean <code>true</code>.
     */
    public function deleteAddressForId($addressId) {
        $sql = "DELETE FROM " . TABLE_ADDRESS_BOOK . "
                WHERE  address_book_id = :id"; 
        ZMRuntime::getDatabase()->query($sql, array('id' => $addressId), TABLE_ADDRESS_BOOK);
        return true;
    }


    /**
     * Get the default address id for the given account.
     *
     * @param int accountId The account id.
     */
    private function getDefaultAddressId($accountId) {
        $account = ZMAccounts::instance()->getAccountForId($accountId);
        return null != $account ? $account->getDefaultAddressId() : 0;
    }


    /**
     * Get the address format for the given address format id.
     *
     * @param int addressFormatId The address format id.
     * @return string The address format.
     */
    public function getAddressFormatForId($addressFormatId) {
        $sql = "select address_format
                from " . TABLE_ADDRESS_FORMAT . "
                where address_format_id = :id";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $addressFormatId), TABLE_ADDRESS_FORMAT);
        return $result['format'];
    }

}

?>
