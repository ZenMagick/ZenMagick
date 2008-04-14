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
 * Macro utilities.
 *
 * @author mano
 * @package org.zenmagick.toolbox.defaults
 * @version $Id$
 */
class ZMToolboxMacro extends ZMObject {

    /**
     * <code>phpinfo</code> wrapper.
     *
     * @param what What to display (see phpinfo manual for more); default is <code>1</code>.
     * @param boolean echo If <code>true</code>, the info will be echo'ed as well as returned.
     * @return string The <code>phpinfo</code> output minus a few formatting things that break validation.
     */
    public function phpinfo($what=1, $echo=ZM_ECHO_DEFAULT) {
        ob_start();                                                                                                       
        phpinfo($what);                                                                                                       
        $info = ob_get_clean();                                                                                       
        $info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $info);
        $info = str_replace('width="600"', '', $info);

        if ($echo) echo $info;
        return $info;
    }


    /**
     * Format an address according to the countries address format.
     *
     * <p>The following values are available for display:</p>
     *
     * <ul>
     *  <li><code>$firstname</code> - The first name</li>
     *  <li><code>$lastname</code> - The last name</li>
     *  <li><code>$company</code> - The company name</li>
     *  <li><code>$street</code> - The street address</li>
     *  <li><code>$streets</code> - Depending on availablility either <code>$street</code> or <code>$street$cr$suburb</code></li>
     *  <li><code>$suburb</code> - The subrub</li>
     *  <li><code>$city</code> - The city</li>
     *  <li><code>$state</code> - The state (either from the list of states or manually entered)</li>
     *  <li><code>$country</code> - The country name</li>
     *  <li><code>$postcode</code>/<code>$zip</code> - The post/zip code</li>
     *  <li><code>$hr</code> - A horizontal line</li>
     *  <li><code>$cr</code> - New line character</li>
     *  <li><code>$statecomma</code> - The sequence <code>$state, </code> (note the trailing space)</li>
     * </ul>
     *
     * <p>If address is <code>null</code>, the localized version of <em>N/A</em> will be returned.</p>
     *
     * @param ZMAddress address The address to format.
     * @param boolean html If <code>true</code>, format as HTML, otherwise plain text.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A fully formatted address that, depending on the <em>html</code> flag, is either HTML or ASCII formatted.
     */
    public function formatAddress($address, $html=true, $echo=ZM_ECHO_DEFAULT) {
        if (null == $address) {
            $out = zm_l10n_get("N/A");    
        } else {
            if (!zm_is_empty($address->getLastName())) {
                $firstname = $address->getFirstName();
                $lastname = $address->getLastName();
            } else {
                $firstname = '';
                $lastname = '';
            }
            $company = $address->getCompanyName();
            $street = $address->getAddress();
            $suburb = $address->getSuburb();
            $city = $address->getCity();
            $state = $address->getState();
            if (0 != $address->getCountryId()) {
                $zmcountry = $address->getCountry();
                $country = $zmcountry->getName();
                if (0 != $address->getZoneId()) {
                    $state = ZMCountries::instance()->getZoneCode($zmcountry->getId(), $address->getZoneId(), $state);
                }
            } else {
                $zmcountry = ZMCountries::instance()->getCountryForId(ZMSettings::get('storeCountry'));
                $country = '';
                $state = '';
            }
            $postcode = $address->getPostcode();

            $boln = '';
            if ($html) {
                $hr = '<hr>';
                $cr = '<br />';
            } else {
                $hr = '----------------------------------------';
                $cr = "\n";
            }

            // encode
            $toolbox = ZMToolbox::instance();
            $vars = array('firstname', 'lastname', 'company', 'street', 'suburb', 'city', 'state', 'country', 'postcode');
            foreach ($vars as $var) {
                $$var = $toolbox->html->encode($$var, false);
            }

            // alias or derived
            $zip = $postcode;
            $statecomma = '';
            $streets = $street;
            if ($suburb != '') $streets = $street . $cr . $suburb;
            if ($state != '') $statecomma = $state . ', ';

            $format = ZMAddresses::instance()->getAddressFormatForId($zmcountry->getAddressFormatId());
            // $format is using all the local variables...
            eval("\$out = \"$format\";");

            $company = $address->getCompanyName();
            if (ZMSettings::get('isAccountCompany') && !empty($company) ) {
                $out = $company . $cr . $out;
            }
        }

        if ($echo) echo $out;
        return $out;
    }

}

?>
