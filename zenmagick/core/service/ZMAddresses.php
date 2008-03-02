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
        return parent::instance('Addresses');
    }


    /**
     * Get the address for the given id.
     *
     * @param int addressId The address id.
     * @return ZMAddress The address or <code>null</code>.
     */
    function getAddressForId($addressId) {
        $db = ZMRuntime::getDB();
        $sql = "select address_book_id, customers_id, entry_gender, entry_company, entry_firstname, entry_lastname, entry_street_address,
                  entry_suburb, entry_postcode, entry_city, entry_state, entry_zone_id, entry_country_id,
                  customers_id
                from " . TABLE_ADDRESS_BOOK . "
                where address_book_id = :addressId";
        $sql = $db->bindVars($sql, ":addressId", $addressId, "integer");
        $results = $db->Execute($sql);

        $address = null;
        if (0 < $results->RecordCount()) {
            $defaultAddressId = $this->_getDefaultAddressId($results->fields['customers_id']);
            $address = $this->_newAddress($results->fields);
            $address->isPrimary_ = $address->addressId_ == $defaultAddressId;
        }

        return $address;
    }


    /**
     * Get all addresses for the given account id.
     *
     * @param int accountId The account id.
     * @return array A list of <code>ZMAddress</code> instances.
     */
    function getAddressesForAccountId($accountId) {
        $defaultAddressId = $this->_getDefaultAddressId($accountId);

        $db = ZMRuntime::getDB();
        $sql = "select address_book_id, customers_id, entry_gender, entry_company, entry_firstname, entry_lastname, entry_street_address,
                  entry_suburb, entry_postcode, entry_city, entry_state, entry_zone_id, entry_country_id
                from " . TABLE_ADDRESS_BOOK . "
                where customers_id = :accountId";
        $sql = $db->bindVars($sql, ":accountId", $accountId, "integer");
        $results = $db->Execute($sql);

        $addresses = array();
        while (!$results->EOF) {
            $address = $this->_newAddress($results->fields);
            $address->isPrimary_ = $address->addressId_ == $defaultAddressId;
            array_push($addresses, $address);
            $results->MoveNext();
        }

        return $addresses;
    }


    /**
     * Update the given address.
     *
     * @param ZMAddress account The address.
     * @return ZMAddress The updated address.
     */
    function updateAddress(&$address) {
        $db = ZMRuntime::getDB();
        $sql = "update " . TABLE_ADDRESS_BOOK . " set
                    entry_firstname = :firstName;string,
                    entry_lastname = :lastName;string,
                    entry_company = :companyName;string,
                    entry_gender = :gender;string,
                    entry_street_address = :address;string, 
                    entry_suburb = :suburb;string,
                    entry_postcode = :postcode;string,
                    entry_city = :city;string,
                    entry_state = :state;string, 
                    entry_zone_id = :zoneId;integer, 
                    entry_country_id = :countryId;integer
                where address_book_id = :addressId";
        $sql = $db->bindVars($sql, ":addressId", $address->getId(), "integer");
        $sql = ZMDbUtils::bindObject($sql, $address);
        $db->Execute($sql);
        return $address;
    }


    /**
     * Create a new address.
     *
     * @param ZMAddress The new address.
     * @return ZMAddress The created address incl. the new address id.
     */
    function createAddress(&$address) {
        $db = ZMRuntime::getDB();
        $sql = "insert into " . TABLE_ADDRESS_BOOK . "(
                 customers_id,
                 entry_firstname, entry_lastname, entry_company, entry_gender, 
                 entry_street_address, entry_suburb, entry_postcode, entry_city, 
                 entry_state, entry_zone_id, entry_country_id
               ) values (
                  :accountId;integer,
                  :firstName;string, :lastName;string, :companyName;string, :gender;string,
                  :address;string, :suburb;string, :postcode;string, :city;string,
                  :state;string, :zoneId;integer, :countryId;integer)";
        $sql = ZMDbUtils::bindObject($sql, $address);
        $db->Execute($sql);
        $address->addressId_ = $db->Insert_ID();

        return $address;
    }


    /**
     * Delte an address.
     *
     * @param int The address id.
     * @param boolean <code>true</code>.
     */
    function deleteAddressForId($addressId) {
        $db = ZMRuntime::getDB();
        $sql = "delete from " . TABLE_ADDRESS_BOOK . "
                where  address_book_id = :addressId"; 
        $sql = $db->bindVars($sql, ':addressId', $addressId, 'integer');
        $db->Execute($sql);
        return true;
    }


    /**
     * Create new address instance.
     */
    function _newAddress($fields) {
        $address = $this->create("Address");
        $address->addressId_ = $fields['address_book_id'];
        $address->accountId_ = $fields['customers_id'];
        $address->firstName_ = $fields['entry_firstname'];
        $address->lastName_ = $fields['entry_lastname'];
        $address->companyName_ = $fields['entry_company'];
        $address->gender_ = $fields['entry_gender'];
        $address->address_ = $fields['entry_street_address'];
        $address->suburb_ = $fields['entry_suburb'];
        $address->postcode_ = $fields['entry_postcode'];
        $address->city_ = $fields['entry_city'];
        $address->state_ = $fields['entry_state'];
        $address->zoneId_ = $fields['entry_zone_id'];
        $address->country_ = ZMCountries::instance()->getCountryForId((int)$fields['entry_country_id']);
        return $address;
    }


    /**
     * Get the default address id for the given account.
     *
     * @param int accountId The account id.
     */
    function _getDefaultAddressId($accountId) {
        $account = ZMAccounts::instance()->getAccountForId($accountId);
        return null != $account ? $account->getDefaultAddressId() : 0;
    }


    /**
     * Get the address format for the given address format id.
     *
     * @param int addressFormatId The address format id.
     * @return string The address format.
     */
    function getAddressFormatForId($addressFormatId) {
        $db = ZMRuntime::getDB();
        $sql = "select address_format as format
                from " . TABLE_ADDRESS_FORMAT . "
                where address_format_id = :addressFormatId";
        $sql = $db->bindVars($sql, ":addressFormatId", $addressFormatId, "integer");
        $results = $db->Execute($sql);
        return $results->fields['format'];
    }

}

?>
