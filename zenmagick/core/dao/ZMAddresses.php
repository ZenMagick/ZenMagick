<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMAddresses extends ZMDao {

    /**
     * Default c'tor.
     */
    function ZMAddresses() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMAddresses();
    }

    function __destruct() {
    }


    function getAddressForId($addressId) {
        $sql = "select address_book_id, entry_gender, entry_company, entry_firstname, entry_lastname, entry_street_address,
                  entry_suburb, entry_postcode, entry_city, entry_state, entry_zone_id, entry_country_id,
                  customers_id
                from " . TABLE_ADDRESS_BOOK . "
                where address_book_id = :addressId";
        $sql = $this->db_->bindVars($sql, ":addressId", $addressId, "integer");
        $results = $this->db_->Execute($sql);

        $address = null;
        if (0 < $results->RecordCount()) {
            $defaultAddressId = $this->_getDefaultAddressId($results->fields['customers_id']);
            $address = $this->_newAddress($results->fields);
            $address->isPrimary_ = $address->addressId_ == $defaultAddressId;
        }

        return $address;
    }


    function getAddressesForAccountId($accountId) {
        $defaultAddressId = $this->_getDefaultAddressId($accountId);

        $sql = "select address_book_id, entry_gender, entry_company, entry_firstname, entry_lastname, entry_street_address,
                  entry_suburb, entry_postcode, entry_city, entry_state, entry_zone_id, entry_country_id
                from " . TABLE_ADDRESS_BOOK . "
                where customers_id = :accountId";
        $sql = $this->db_->bindVars($sql, ":accountId", $accountId, "integer");
        $results = $this->db_->Execute($sql);

        $addresses = array();
        while (!$results->EOF) {
            $address = $this->_newAddress($results->fields);
            $address->isPrimary_ = $address->addressId_ == $defaultAddressId;
            array_push($addresses, $address);
            $results->MoveNext();
        }

        return $addresses;
    }


    function _newAddress($fields) {
    global $zm_countries;
        $address =& $this->create("Address");
        $address->addressId_ = $fields['address_book_id'];
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
        $address->country_ = $zm_countries->getCountryForId((int)$fields['entry_country_id']);
        return $address;
    }


    function _getDefaultAddressId($accountId) {
    global $zm_accounts;
        $account = $zm_accounts->getAccountForId($accountId);
        return null != $account ? $account->getDefaultAddressId() : 0;
    }


    function getAddressFormatForId($id) {
        $sql = "select address_format as format
                from " . TABLE_ADDRESS_FORMAT . "
                where address_format_id = :id";
        $sql = $this->db_->bindVars($sql, ":id", $id, "integer");
        $results = $this->db_->Execute($sql);
        return $results->fields['format'];
    }

}

?>
