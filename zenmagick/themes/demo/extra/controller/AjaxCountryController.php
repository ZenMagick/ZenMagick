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
 * Ajax demo controller for XML formatted country data.
 *
 * @author mano
 */
class AjaxCountryController extends ZMAjaxCountryController {

    /**
     * Default c'tor.
     */
    function AjaxCountryController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->AjaxCountryController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Generates a simple XML formatted list of all countries.
     *
     * @return void
     */
    function getCountryListXML() {
    global $zm_countries;

        $this->setContentType('text/xml');

        // create XML
        echo '<countries>';
        foreach ($zm_countries->getCountries() as $country) {
            echo '<country id="'.$country->getId().'" name="'.$country->getName().'" />';
        }
        echo '</countries>';
    }

    /**
     * Generates a simple XML list of all zones for the requested country id.
     *
     * @return void
     */
    function getZonesForCountryIdXML() {
    global $zm_request, $zm_countries;

        $this->setContentType('text/xml');

        // create XML
        $countryId = $zm_request->getRequestParameter('countryId', null);

        echo '<zones>';
        foreach ($zm_countries->getZonesForCountryId($countryId) as $zone) {
            echo '<zones id="'.$zone->getId().'" name="'.$zone->getName().'" />';
        }
        echo '</zones>';
    }

}

?>
