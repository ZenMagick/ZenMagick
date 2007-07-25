<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * A single address.
 *
 * <p>An address can have either a zoneId or a state; state is the manually entered
 * value and zoneId is the state selected from a dropdown.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick.model.account
 * @version $Id$
 */
class ZMAddress extends ZMModel {
    var $addressId_;
    var $accountId_;
    var $firstName_;
    var $lastName_;
    var $companyName_;
    var $gender_;
    var $address_;
    var $suburb_;
    var $postcode_;
    var $city_;
    var $state_;
    var $zoneId_;
    var $country_;
    var $isPrimary_;
    var $format_;


    /**
     * Default c'tor.
     */
    function ZMAddress() {
        parent::__construct();

        $this->addressId_ = 0;
        $this->firstName_ = '';
        $this->lastName_ = '';
        $this->companyName_ = '';
        $this->gender_ = '';
        $this->address_ = '';
        $this->suburb_ = '';
        $this->postcode_ = '';
        $this->city_ = '';
        $this->state_ = '';
        $this->zoneId_ = 0;
        $this->country_ = $this->create("Country");
        $this->isPrimary_ = false;
        $this->format_ = 0;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMAddress();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Populate all available fields from the given request.
     *
     * @param array req A request; if <code>null</code>, use the current <code>ZMRequest</code> instead.
     */
    function populate($req=null) {
    global $zm_request, $zm_countries;

        $this->addressId_ = $zm_request->getParameter('addressId', 0);
        $this->firstName_ = $zm_request->getParameter('firstname', '');
        $this->lastName_ = $zm_request->getParameter('lastname', '');
        $this->companyName_ = $zm_request->getParameter('company', '');
        $this->gender_ = $zm_request->getParameter('gender', '');
        $this->address_ = $zm_request->getParameter('street_address', '');
        $this->suburb_ = $zm_request->getParameter('suburb', '');
        $this->postcode_ = $zm_request->getParameter('postcode', '');
        $this->city_ = $zm_request->getParameter('city', '');
        $this->country_ = $zm_countries->getCountryForId($zm_request->getParameter('zone_country_id', 0));
        if (null == $this->country_) {
            $this->country_ = $this->create("Country");
        }

        $this->state_ = '';
        $this->zoneId_ = 0;
        // free text or zone id
        $state = $zm_request->getParameter('state', '');
        $zones = $zm_countries->getZonesForCountryId($this->country_->getId());
        if (0 < count ($zones)) {
            // need $state to match either an id or name
            foreach ($zones as $zone) {
                if ($zone->getName() == $state || $zone->getId() == $state) {
                    $this->zoneId_ = $zone->getId();
                    break;
                }
            }
        } else {
            // need some free text that is not numeric (pretty safe to assume!)
            if (!zm_is_empty($state)) {
                if (!is_numeric($state)) {
                    $this->state_ = $state;
                }
            }

        }

        $this->isPrimary_ = zm_boolean($zm_request->getParameter('primary', false));
        $this->format_ = 0;
    }


    /**
     * Get the address id.
     *
     * @return int The account id.
     */
    function getId() { return $this->addressId_; }

    /**
     * Get the account id.
     *
     * @return int The account id.
     */
    function getAccountId() { return $this->accountId_; }

    /**
     * Get the first name.
     *
     * @return string The first name.
     */
    function getFirstName() { return $this->firstName_; }

    /**
     * Get the last name.
     *
     * @return string The last name.
     */
    function getLastName() { return $this->lastName_; }

    /**
     * Get the gender.
     *
     * @return string The gender ('f' or 'm').
     */
    function getGender() { return $this->gender_; }

    function getCompanyName() { return $this->companyName_; }
    function getAddress() { return $this->address_; }
    function getSuburb() { return $this->suburb_; }
    function getPostcode() { return $this->postcode_; }
    function getCity() { return $this->city_; }
    function getState() { return $this->state_; }
    function setState($state) { $this->state_= $state; }
    function getZoneId() { return $this->zoneId_; }
    function setZoneId($zoneId) { $this->zoneId_ = $zoneId; }
    function getCountry() { return $this->country_; }
    function getCountryId() { return $this->country_->id_; }
    function isPrimary() { return $this->isPrimary_; }
    function getFormat() { return $this->format_; }
    function getFullName() { return $this->firstName_ . ' ' . $this->lastName_; }

    /**
     * Set the account id.
     *
     * @param int accountId The account id.
     */
    function setAccountId($accountId) { $this->accountId_ = $accountId; }

}

?>
