<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

namespace ZenMagick\StoreBundle\Entity;

use ZenMagick\Base\ZMObject;
use Doctrine\ORM\Mapping as ORM;

/**
 * A single address.
 *
 * <p>An address can have either a zoneId or a state; state is the manually entered
 * value and zoneId is the state selected from a dropdown.</p>
 *
 * @author DerManoMann
 * @ORM\Table(name="address_book")
 * @ORM\Entity
 */
class Address extends ZMObject {
    /**
     * @var integer $addressId
     *
     * @ORM\Column(name="address_book_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $addressId;
    /**
     * @var integer $accountId
     *
     * @ORM\Column(name="customers_id", type="integer", nullable=false)
     */
    private $accountId;
    /**
     * @var string $gender
     *
     * @ORM\Column(name="entry_gender", type="string", length=1, nullable=false)
     */
    private $gender;
    /**
     * @var string $entryCompany
     *
     * @ORM\Column(name="entry_company", type="string", length=64, nullable=true)
     */
    private $companyName;
    /**
     * @var string $firstName
     *
     * @ORM\Column(name="entry_firstname", type="string", length=32, nullable=false)
     */
    private $irstName;
    /**
     * @var string $lastName
     *
     * @ORM\Column(name="entry_lastname", type="string", length=32, nullable=false)
     */
    private $lastName;
    /**
     * @var string $addressLine1
     *
     * @ORM\Column(name="entry_street_address", type="string", length=64, nullable=false)
     */
    private $addressLine1;
    /**
     * @var string $suburb
     *
     * @ORM\Column(name="entry_suburb", type="string", length=32, nullable=true)
     */
    private $suburb;
    /**
     * @var string $postcode
     *
     * @ORM\Column(name="entry_postcode", type="string", length=10, nullable=false)
     */
    private $postcode;
    /**
     * @var string $city
     *
     * @ORM\Column(name="entry_city", type="string", length=32, nullable=false)
     */
    private $city;
    /**
     * @var string $state
     *
     * @ORM\Column(name="entry_state", type="string", length=32, nullable=true)
     */
    private $state;
    /**
     * @var integer $countryId
     *
     * @ORM\Column(name="entry_country_id", type="integer", nullable=false)
     */
    private $countryId;
    /**
     * @var integer $entryZoneId
     *
     * @ORM\Column(name="entry_zone_id", type="integer", nullable=false)
     */
    private $zoneId;

    private $country;
    private $isPrimary;
    private $format;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();

        $this->addressId = 0;
        $this->accountId = 0;
        $this->firstName = null;
        $this->lastName = null;
        $this->companyName = null;
        $this->gender = null;
        $this->addressLine1 = null;
        $this->suburb = null;
        $this->postcode = null;
        $this->city = null;
        $this->state = null;
        $this->zoneId = 0;
        $this->country = null;
        $this->countryId = 0;
        $this->isPrimary = false;
        $this->format = 0;
    }


    /**
     * Get the address id.
     *
     * @return int The account id.
     */
    public function getId() { return $this->addressId; }

    /**
     * Get the account id.
     *
     * @return int The account id.
     */
    public function getAccountId() { return $this->accountId; }

    /**
     * Get the first name.
     *
     * @return string The first name.
     */
    public function getFirstName() { return $this->firstName; }

    /**
     * Get the last name.
     *
     * @return string The last name.
     */
    public function getLastName() { return $this->lastName; }

    /**
     * Get the gender.
     *
     * @return string The gender ('f' or 'm').
     */
    public function getGender() { return $this->gender; }

    /**
     * Get the company name.
     *
     * @return string The company name.
     */
    public function getCompanyName() { return $this->companyName; }

    /**
     * Get the address line.
     *
     * @return string The address line.
     */
    public function getAddressLine1() { return $this->addressLine1; }

    /**
     * Get the suburb.
     *
     * @return string The suburb.
     */
    public function getSuburb() { return $this->suburb; }

    /**
     * Get the postcode.
     *
     * @return string The postcode.
     */
    public function getPostcode() { return $this->postcode; }

    /**
     * Get the city.
     *
     * @return string The city.
     */
    public function getCity() { return $this->city; }

    /**
     * Get the state.
     *
     * @return string The state.
     */
    public function getState() { return $this->state; }

    /**
     * Get the zone id.
     *
     * @return int The zone id.
     */
    public function getZoneId() { return $this->zoneId; }

    /**
     * Get the country.
     *
     * @return Country The country.
     */
    public function getCountry() {
        if (null == $this->country) {
            $this->country = $this->container->get('countryService')->getCountryForId($this->countryId);
        }
        return $this->country;
    }

    /**
     * Get the countryId.
     *
     * @return int The countryId or <em>0</em>.
     */
    public function getCountryId() { return null != $this->country ? $this->country->getId() : $this->countryId; }

    /**
     * Check if the address is the primary address.
     *
     * @return boolean <code>true</code> if the address is the primary adddress, <code>false</code> if not.
     */
    public function isPrimary() { return $this->isPrimary; }

    /**
     * Get the format.
     *
     * @return string The format.
     */
    public function getFormat() { return $this->format; }

    /**
     * Set the format.
     *
     * @param string format The format.
     */
    public function setFormat($format) { $this->format = $format; }

    /**
     * Get the full name.
     *
     * @return string The formatted full name.
     */
    public function getFullName() { return $this->firstName . ' ' . $this->lastName; }

    /**
     * Set the account id.
     *
     * @param int accountId The account id.
     */
    public function setAccountId($accountId) { $this->accountId = $accountId; }

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
        return $this->container->get('addressService')->getAddressFormatForId($this->getCountry()->getAddressFormatId());
    }

    /**
     * Set the primary address flag.
     *
     * @param boolean value The new status.
     */
    public function setPrimary($value) { $this->isPrimary = $value; }

    /**
     * Set the address id.
     *
     * @param int id The account id.
     */
    public function setId($id) { $this->addressId = $id; }

    /**
     * Set the first name.
     *
     * @param string firstName The first name.
     */
    public function setFirstName($name) { $this->firstName = $name; }

    /**
     * Set the last name.
     *
     * @param string name The last name.
     */
    public function setLastName($name) { $this->lastName = $name; }

    /**
     * Set the gender.
     *
     * @param string gender The gender ('f' or 'm').
     */
    public function setGender($gender) { $this->gender = $gender; }

    /**
     * Set the address line.
     *
     * @param string addressLine The address line.
     */
    public function setAddressLine1($addressLine) { $this->addressLine1 = $addressLine; }

    /**
     * Set the suburb.
     *
     * @param string suburbThe suburb.
     */
    public function setSuburb($suburb) { $this->suburb = $suburb; }

    /**
     * Set the postcode.
     *
     * @param string postcode The postcode.
     */
    public function setPostcode($postcode) { $this->postcode = $postcode; }

    /**
     * Set the city.
     *
     * @param string city The city.
     */
    public function setCity($city) { $this->city = $city; }

    /**
     * Set the state.
     *
     * @param string state The state.
     */
    public function setState($state) { $this->state= $state; }

    /**
     * Set the zone id.
     *
     * @param int zoneId The zone id.
     */
    public function setZoneId($zoneId) { $this->zoneId = $zoneId; }

    /**
     * Set the country.
     *
     * @param Country country The country.
     */
    public function setCountry($country) { $this->country = $country; }

    /**
     * Set the country id.
     *
     * @param int countryId The country id.
     */
    public function setCountryId($countryId) { $this->countryId = $countryId; }

    /**
     * Set the company name.
     *
     * @param string name The company name.
     */
    public function setCompanyName($name) { $this->companyName = $name; }

}
