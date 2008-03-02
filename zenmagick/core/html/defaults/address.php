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
 *
 * $Id$
 */
?>
<?php

    /**
     * Format an address according to the countries address format.
     *
     * @package org.zenmagick.html.defaults
     * @param ZMAddress address The address to format.
     * @param boolean html If <code>true</code>, format as HTML, otherwise plain text.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A fully formatted address.
     */
    function zm_format_address(&$address, $html=true, $echo=ZM_ECHO_DEFAULT) {
    global $zm_countries;

        if (null == $address) {
            $out = zm_l10n_get("N/A");    
            if ($echo) echo $out;
            return $out;
        }
        if (!zm_is_empty($address->getLastName())) {
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
        if (0 != $address->getCountryId()) {
            $zmcountry = $address->getCountry();
            $country = $zmcountry->getName();
            if (0 != $address->getZoneId()) {
                $state = $zm_countries->getZoneCode($zmcountry->getId(), $address->getZoneId(), $state);
            }
        } else {
            $zmcountry = $zm_countries->getCountryForId(zm_setting('storeCountry'));
            $country = '';
            $state = '';
        }
        $postcode = $address->getPostcode();
        $zip = $postcode;

        $boln = '';
        if ($html) {
            $hr = '<hr>';
            $cr = '<br />';
        } else {
            $hr = '----------------------------------------';
            $cr = "\n";
        }

        $statecomma = '';
        $streets = $street;
        if ($suburb != '') $streets = $street . $cr . $suburb;
        if ($state != '') $statecomma = $state . ', ';

        $format = ZMAddresses::instance()->getAddressFormatForId($zmcountry->getAddressFormatId());
        // $format is using all the local variables...
        eval("\$out = \"$format\";");

        $company = $address->getCompanyName();
        if (zm_setting('isAccountCompany') && !zm_is_empty($company) ) {
            $out = $company . $cr . $out;
        }

        if ($echo) echo $out;
        return $out;
    }

?>
