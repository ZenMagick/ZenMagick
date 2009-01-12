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
 * Ajax controller for JSON country data.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.ajax.controller
 * @version $Id$
 */
class ZMAjaxCountryController extends ZMAjaxController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
     * @return void
     */
    public function getCountryListJSON() {
        $flatObj = $this->flattenObject(ZMCountries::instance()->getCountries(), array('id', 'name'));
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Generates a JSON list of all zones for the requested country id.
     *
     * @param int countryId The country id.
     * @return void
     */
    public function getZonesForCountryIdJSON() {
        $countryId = ZMRequest::getParameter('countryId', null);

        $flatObj = $this->flattenObject(ZMCountries::instance()->getZonesForCountryId($countryId), array('id', 'name'));
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

}

?>
