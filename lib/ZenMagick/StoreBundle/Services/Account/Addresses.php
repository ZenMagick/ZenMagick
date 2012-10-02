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

namespace ZenMagick\StoreBundle\Services\Account;

use ZMRuntime;
use ZenMagick\Base\ZMObject;

/**
 * Addresses.
 *
 * @author DerManoMann
 */
class Addresses extends ZMObject {

    /**
     * Get the address for the given id.
     *
     * @param int addressId The address id.
     * @param int accountId Optional account id to make it easy to verify access; default is <code>null</code>.
     * @return ZenMagick\StoreBundle\Entity\Address The address or <code>null</code>.
     */
    public function getAddressForId($addressId, $accountId=null) {
        $sql = "SELECT *
                FROM %table.address_book%
                WHERE address_book_id = :id";
        if (null !== $accountId) {
            $sql .= " AND customers_id = :accountId";
        }
        $address = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $addressId, 'accountId' => $accountId), 'address_book', 'ZenMagick\StoreBundle\Entity\Address');
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
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Address</code> instances.
     */
    public function getAddressesForAccountId($accountId) {
        $sql = "SELECT *
                FROM %table.address_book%
                WHERE customers_id = :accountId";
        $addresses = ZMRuntime::getDatabase()->fetchAll($sql, array('accountId' => $accountId), 'address_book', 'ZenMagick\StoreBundle\Entity\Address');

        $defaultAddressId = $this->getDefaultAddressId($accountId);
        foreach ($addresses as $address) {
            $address->setPrimary($address->getId() == $defaultAddressId);
        }

        return $addresses;
    }


    /**
     * Update the given address.
     *
     * @param ZenMagick\StoreBundle\Entity\Address account The address.
     * @return ZenMagick\StoreBundle\Entity\Address The updated address.
     */
    public function updateAddress($address) {
        return ZMRuntime::getDatabase()->updateModel('address_book', $address);
    }


    /**
     * Create a new address.
     *
     * @param ZenMagick\StoreBundle\Entity\Address The new address.
     * @return ZenMagick\StoreBundle\Entity\Address The created address incl. the new address id.
     */
    public function createAddress($address) {
        return ZMRuntime::getDatabase()->createModel('address_book', $address);
    }


    /**
     * Delte an address.
     *
     * @param int addressId The address id.
     * @param boolean <code>true</code>.
     */
    public function deleteAddressForId($addressId) {
        $sql = "DELETE FROM %table.address_book%
                WHERE  address_book_id = :id";
        ZMRuntime::getDatabase()->updateObj($sql, array('id' => $addressId), 'address_book');
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
                FROM %table.address_format%
                WHERE address_format_id = :id";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $addressFormatId), 'address_format');
        return $result['format'];
    }

}
