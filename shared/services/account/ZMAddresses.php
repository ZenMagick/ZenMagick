<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 * Addresses.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.account
 */
class ZMAddresses extends ZMObject {

    /**
     * Get instance.
     */
    public static function instance() {
        return Runtime::getContainer()->get('addressService');
    }


    /**
     * Get the address for the given id.
     *
     * @param int addressId The address id.
     * @param int accountId Optional account id to make it easy to verify access; default is <code>null</code>.
     * @return ZMAddress The address or <code>null</code>.
     */
    public function getAddressForId($addressId, $accountId=null) {
        $sql = "SELECT *
                FROM " . TABLE_ADDRESS_BOOK . "
                WHERE address_book_id = :id";
        if (null !== $accountId) {
            $sql .= " AND customers_id = :accountId";
        }
        $address = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $addressId, 'accountId' => $accountId), TABLE_ADDRESS_BOOK, 'ZMAddress');
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
        $sql = "SELECT *
                FROM " . TABLE_ADDRESS_BOOK . "
                WHERE customers_id = :accountId";
        $addresses = ZMRuntime::getDatabase()->fetchAll($sql, array('accountId' => $accountId), TABLE_ADDRESS_BOOK, 'ZMAddress');

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
        return ZMRuntime::getDatabase()->updateModel(TABLE_ADDRESS_BOOK, $address);
    }


    /**
     * Create a new address.
     *
     * @param ZMAddress The new address.
     * @return ZMAddress The created address incl. the new address id.
     */
    public function createAddress($address) {
        return ZMRuntime::getDatabase()->createModel(TABLE_ADDRESS_BOOK, $address);
    }


    /**
     * Delte an address.
     *
     * @param int addressId The address id.
     * @param boolean <code>true</code>.
     */
    public function deleteAddressForId($addressId) {
        $sql = "DELETE FROM " . TABLE_ADDRESS_BOOK . "
                WHERE  address_book_id = :id";
        ZMRuntime::getDatabase()->updateObj($sql, array('id' => $addressId), TABLE_ADDRESS_BOOK);
        return true;
    }


    /**
     * Get the default address id for the given account.
     *
     * @param int accountId The account id.
     */
    private function getDefaultAddressId($accountId) {
        $account = $this->container->get('accountService')->getAccountForId($accountId);
        return null != $account ? $account->getDefaultAddressId() : 0;
    }


    /**
     * Get the address format for the given address format id.
     *
     * @param int addressFormatId The address format id.
     * @return string The address format.
     */
    public function getAddressFormatForId($addressFormatId) {
        $sql = "SELECT address_format
                FROM " . TABLE_ADDRESS_FORMAT . "
                WHERE address_format_id = :id";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $addressFormatId), TABLE_ADDRESS_FORMAT);
        return $result['format'];
    }

}
