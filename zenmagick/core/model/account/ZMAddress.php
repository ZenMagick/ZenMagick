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
 * @author mano
 * @package net.radebatz.zenmagick.model.account
 * @version $Id$
 */
class ZMAddress extends ZMModel {
    var $addressId_;
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

        $this->addressId_ = 0;
        $this->firstName_ = $zm_request->getParameter('firstname', '');
        $this->lastName_ = $zm_request->getParameter('lastname', '');
        $this->companyName_ = $zm_request->getParameter('company', '');
        $this->gender_ = $zm_request->getParameter('gender', '');
        $this->address_ = $zm_request->getParameter('street_address', '');
        $this->suburb_ = $zm_request->getParameter('suburb', '');
        $this->postcode_ = $zm_request->getParameter('postcode', '');
        $this->city_ = $zm_request->getParameter('city', '');
        $this->state_ = $zm_request->getParameter('state', '');
        $this->zoneId_ = 0;
        $this->country_ = $zm_countries->getCountryForId($zm_request->getParameter('country', 0));
        if (null == $this->country_) {
            $this->country_ = $this->create("Country");
        }
        $this->isPrimary_ = false;
        $this->format_ = 0;
    }


    // validate this account
    function isValid() {
    global $zm_messages, $zm_countries;
        $msgCount = count($zm_messages->getMessages());
        if ($this->gender_ != 'm' && $this->gender_ != 'f') {
            $zm_messages->error(zm_l10n_get("Please choose a title."));
        }

        if (strlen($this->firstName_) < zm_setting('firstNameMinLength')) {
            $zm_messages->error(zm_l10n_get("Your First Name must contain a minimum of %s characters.", zm_setting('firstNameMinLength')));
        }

        if (strlen($this->lastName_) < zm_setting('lastNameMinLength')) {
            $zm_messages->error(zm_l10n_get("Your Last Name must contain a minimum of %s characters.", zm_setting('lastNameMinLength')));
        }

        if (strlen($this->address_) < zm_setting('addressMinLength')) {
            $zm_messages->error(zm_l10n_get("Your Street Address must contain a minimum of %s characters.", zm_setting('addressMinLength')));
        }

        if (strlen($this->postcode_) < zm_setting('postcodeMinLength')) {
            $zm_messages->error(zm_l10n_get("Your Post Code must contain a minimum of %s characters.", zm_setting('postcodeMinLength')));
        }

        if (strlen($this->city_) < zm_setting('cityMinLength')) {
            $zm_messages->error(zm_l10n_get("Your City must contain a minimum of %s characters.", zm_setting('cityMinLength')));
        }

        if (zm_setting('isAccountState')) {
            $zones = $zm_countries->getZonesForCountryId($this->country_ ? $this->country_->getId() : null);
            if (0 < count($zones)) {
                if (!array_key_exists($this->state_, $zones)) {
                    $zm_messages->error(zm_l10n_get("Please select a State."));
                }
            } else {
                if (strlen($this->state_) < zm_setting('stateMinLength')) {
                    $zm_messages->error(zm_l10n_get("Your State must contain a minimum of %s characters.", zm_setting('stateMinLength')));
                }
            }
        }

        if (!$this->country_) {
            $zm_messages->error(zm_l10n_get("Please select a Country."));
        }

        return count($zm_messages->getMessages()) == $msgCount;
    }


    // getter/setter
    function getId() { return $this->addressId_; }
    function getFirstName() { return $this->firstName_; }
    function getLastName() { return $this->lastName_; }
    function getCompanyName() { return $this->companyName_; }
    function getGender() { return $this->gender_; }
    function getAddress() { return $this->address_; }
    function getSuburb() { return $this->suburb_; }
    function getPostcode() { return $this->postcode_; }
    function getCity() { return $this->city_; }
    function getState() { return $this->state_; }
    function getZoneId() { return $this->zoneId_; }
    function getCountry() { return $this->country_; }
    function getCountryId() { return $this->country_->id_; }
    function isPrimary() { return $this->isPrimary_; }
    function getFormat() { return $this->format_; }
    function getFullName() { return $this->firstName_ . ' ' . $this->lastName_; }

}

?>
