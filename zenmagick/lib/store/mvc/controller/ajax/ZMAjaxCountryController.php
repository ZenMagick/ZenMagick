<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Ajax controller for JSON country data.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller.ajax
 * @version $Id: ZMAjaxCountryController.php 2153 2009-04-14 03:28:18Z dermanomann $
 */
class ZMAjaxCountryController extends ZMAjaxController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ajaxCountry');
        $this->set('ajaxCountryMap', array('id', 'name'));
        $this->set('ajaxZoneMap', array('id', 'name'));
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Generates a JSON list of all countries.
     *
     * @param ZMRequest request The current request.
     */
    public function getCountryListJSON($request) {
        $flatObj = $this->flattenObject(ZMCountries::instance()->getCountries(), $this->get('ajaxCountryMap'));
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Generates a JSON list of all zones for the requested country id.
     *
     * <p>Request parameter (either or):</p>
     * <ul>
     *  <li>countryId - A valid country id</li>
     * </ul>
     *
     * @param ZMRequest request The current request.
     */
    public function getZonesForCountryIdJSON($request) {
        $countryId = $request->getParameter('countryId', null);

        $flatObj = $this->flattenObject(ZMCountries::instance()->getZonesForCountryId($countryId), $this->get('ajaxZoneMap'));
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

}

?>
