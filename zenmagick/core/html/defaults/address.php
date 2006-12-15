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
 *
 * $Id$
 */
?>
<?php

    /**
     * Format an address according to the countries address format.
     *
     * @package net.radebatz.zenmagick.html.defaults
     * @param ZMAddress address The address to format.
     * @param bool html If <code>true</code>, format as HTML, otherwise plain text.
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A fully formatted address.
     */
    function zm_format_address($address, $html=true, $echo=true) {
    global $zm_countries, $zm_addresses;

        $company = $address->getCompanyName();
        if (!zm_is_empty($address->getFirstName())) {
            $firstname = $address->getFirstName();
            $lastname = $address->getLastName();
        } else {
            $firstname = '';
            $lastname = '';
        }
        $street = $address->getAddress();
        $suburb = $address->getSuburb();
        $city = $address->getCity();
        $state = $address->getState();
        $zmcountry = $address->getCountry();
        if (0 != $zmcountry->getId()) {
            $country = $zmcountry->getName();
            if (0 != $address->getZoneId()) {
                $state = $zm_countries->getZoneCode($zmcountry->getId(), $address->getZoneId(), $state);
            }
        } else {
            $country = '';
            $state = '';
        }
        $postcode = $address->getPostcode();
        $zip = $postcode;

        $boln = '';
        if ($html) {
            $HR = '<hr>';
            $hr = '<hr>';
            $CR = '<br />';
            $cr = '<br />';
            $eoln = $cr;
        } else {
            $CR = $eoln;
            $cr = $CR;
            $HR = '----------------------------------------';
            $hr = '----------------------------------------';
        }

        $statecomma = '';
        $streets = $street;
        if ($suburb != '') $streets = $street . $cr . $suburb;
        if ($state != '') $statecomma = $state . ', ';

        $format = $zm_addresses->getAddressFormatForId($zmcountry->getAddressFormatId());
        // $format is using all the local variables...
        eval("\$out = \"$format\";");

        if (zm_setting('isAccountCompany') && !zm_is_empty($company) ) {
            $out = $company . $cr . $out;
        }

        if ($echo) echo $out;
        return $out;
    }

?>
