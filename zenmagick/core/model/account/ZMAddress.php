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
 * A single address.
 *
 * <p>An address can have either a zoneId or a state; state is the manually entered
 * value and zoneId is the state selected from a dropdown.</p>
 *
 * @author mano
 * @package org.zenmagick.model.account
 * @version $Id$
 */
class ZMAddress extends ZMModel {
    private $addressId_;
    private $accountId_;
    private $firstName_;
    private $lastName_;
    private $companyName_;
    private $gender_;
    private $address_;
    private $suburb_;
    private $postcode_;
    private $city_;
    private $state_;
    private $zoneId_;
    private $country_;
    private $isPrimary_;
    private $format_;


    /**
     * Create new instance.
     */
    function __construct() {
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
        $this->country_ = null;
        $this->isPrimary_ = false;
        $this->format_ = 0;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Populate all available fields from the given request.
     *
     * @param array req A request; if <code>null</code>, use the current <code>ZMRequest</code> instead.
     */
    public function populate($req=null) {
        $this->addressId_ = ZMRequest::getParameter('addressId', 0);
        // default to current
        $this->accountId_ = ZMRequest::getParameter('accountId', ZMRequest::getAccount()->getId());
        $this->firstName_ = ZMRequest::getParameter('firstname', '');
        $this->lastName_ = ZMRequest::getParameter('lastname', '');
        $this->companyName_ = ZMRequest::getParameter('company', '');
        $this->gender_ = ZMRequest::getParameter('gender', '');
        $this->address_ = ZMRequest::getParameter('street_address', '');
        $this->suburb_ = ZMRequest::getParameter('suburb', '');
        $this->postcode_ = ZMRequest::getParameter('postcode', '');
        $this->city_ = ZMRequest::getParameter('city', '');
        $this->countryId_ = ZMRequest::getParameter('zone_country_id', 0);

        $this->state_ = '';
        $this->zoneId_ = 0;
        // free text or zone id
        $state = ZMRequest::getParameter('state', '');
        $zones = ZMCountries::instance()->getZonesForCountryId($this->countryId_);
        if (0 < count ($zones)) {
            // need $state to match either an id or name
            foreach ($zones as $zone) {
                if ($zone->getName() == $state || $zone->getId() == $state || $zone->getCode() == $state) {
                    $this->zoneId_ = $zone->getId();
                    break;
                }
            }
        } else {
            // need some free text that is not numeric (pretty safe to assume!)
            if (!empty($state)) {
                if (!is_numeric($state)) {
                    $this->state_ = $state;
                }
            }

        }

        $this->isPrimary_ = ZMTools::asBoolean(ZMRequest::getParameter('primary', false));
        $this->format_ = 0;
    }


    /**
     * Get the address id.
     *
     * @return int The account id.
     */
    public function getId() { return $this->addressId_; }

    /**
     * Get the account id.
     *
     * @return int The account id.
     */
    public function getAccountId() { return $this->accountId_; }

    /**
     * Get the first name.
     *
     * @return string The first name.
     */
    public function getFirstName() { return $this->firstName_; }

    /**
     * Get the last name.
     *
     * @return string The last name.
     */
    public function getLastName() { return $this->lastName_; }

    /**
     * Get the gender.
     *
     * @return string The gender ('f' or 'm').
     */
    public function getGender() { return $this->gender_; }

    /**
     * Get the company name.
     *
     * @return string The company name.
     */
    public function getCompanyName() { return $this->companyName_; }

    /**
     * Get the address line.
     *
     * @return string The address line.
     */
    public function getAddress() { return $this->address_; }

    /**
     * Get the suburb.
     *
     * @return string The suburb.
     */
    public function getSuburb() { return $this->suburb_; }

    /**
     * Get the postcode.
     *
     * @return string The postcode.
     */
    public function getPostcode() { return $this->postcode_; }

    /**
     * Get the city.
     *
     * @return string The city.
     */
    public function getCity() { return $this->city_; }

    /**
     * Get the state.
     *
     * @return string The state.
     */
    public function getState() { return $this->state_; }

    /**
     * Get the zone id.
     *
     * @return int The zone id.
     */
    public function getZoneId() { return $this->zoneId_; }

    /**
     * Get the country.
     *
     * @return ZMCountry The country.
     */
    public function getCountry() {
        if (null == $this->country_) {
            $this->country_ = ZMCountries::instance()->getCountryForId($this->countryId_);
        }
        return $this->country_;
    }

    /**
     * Get the countryId.
     *
     * @return int The countryId or <em>0</em>.
     */
    public function getCountryId() { return $this->countryId_; }

    /**
     * Check if the address is the primary address.
     *
     * @return boolean <code>true</code> if the address is the primary adddress, <code>false</code> if not.
     */
    public function isPrimary() { return $this->isPrimary_; }

    /**
     * Get the format.
     *
     * @return string The format.
     */
    public function getFormat() { return $this->format_; }

    /**
     * Get the full name.
     *
     * @return string The formatted full name.
     */
    public function getFullName() { return $this->firstName_ . ' ' . $this->lastName_; }

    /**
     * Set the account id.
     *
     * @param int accountId The account id.
     */
    public function setAccountId($accountId) { $this->accountId_ = $accountId; }

    /**
     * Get the address format id.
     *
     * @return int The address format id.
     */
    public function getAddressFormatId() {
        return $this->getCountry()->getAddressFormatId();
    }

    /**
     * Get the address format.
     *
     * @return string The address format id.
     */
    public function getAddressFormat() {
        return ZMAddresses::instance()->getAddressFormatForId($this->getCountry()->getAddressFormatId());
    }

    /**
     * Set the primary address flag.
     *
     * @param boolean isPrimary The new status.
     */
    public function setPrimary($isPrimary) { $this->isPrimary_ = $isPrimary; }

    /**
     * Set the address id.
     *
     * @param int id The account id.
     */
    public function setId($id) { $this->addressId_ = $id; }

    /**
     * Set the first name.
     *
     * @param string firstName The first name.
     */
    public function setFirstName($name) { $this->firstName_ = $name; }

    /**
     * Set the last name.
     *
     * @param string name The last name.
     */
    public function setLastName($name) { $this->lastName_ = $name; }

    /**
     * Set the gender.
     *
     * @param string gender The gender ('f' or 'm').
     */
    public function setGender($gender) { $this->gender_ = $gender; }

    /**
     * Set the address line.
     *
     * @param string address The address line.
     */
    public function setAddress($address) { $this->address_ = $address; }

    /**
     * Set the suburb.
     *
     * @param string suburbThe suburb.
     */
    public function setSuburb($suburb) { $this->suburb_ = $suburb; }

    /**
     * Set the postcode.
     *
     * @param string postcode The postcode.
     */
    public function setPostcode($postcode) { $this->postcode_ = $postcode; }

    /**
     * Set the city.
     *
     * @param string city The city.
     */
    public function setCity($city) { $this->city_ = $city; }

    /**
     * Set the state.
     *
     * @param string state The state.
     */
    public function setState($state) { $this->state_= $state; }

    /**
     * Set the zone id.
     *
     * @param int zoneId The zone id.
     */
    public function setZoneId($zoneId) { $this->zoneId_ = $zoneId; }

    /**
     * Set the country.
     *
     * @param ZMCountry country The country.
     */
    public function setCountry($country) { $this->country_ = $country; }

    /**
     * Set the country id.
     *
     * @param int countryId The country id.
     */
    public function setCountryId($countryId) { $this->countryId_ = $countryId; }

    /**
     * Set the company name.
     *
     * @param string name The company name.
     */
    public function setCompanyName($name) { $this->companyName_ = $name; }

}

?>
