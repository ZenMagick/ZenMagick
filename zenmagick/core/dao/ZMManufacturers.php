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
 */
?>
<?php


/**
 * Manufacturers.
 *
 * @author mano
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMManufacturers {
    // db access
    var $db_;


    // create new instance
    function ZMManufacturers() {
    global $zm_runtime;
        $this->db_ = $zm_runtime->getDB();
    }

    // create new instance
    function __construct() {
        $this->ZMManufacturers();
    }

    function __destruct() {
    }


    // get manufacturer for id
    function getManufacturerForId($manufacturerId) {
    global $zm_request;
        $manufacturer = null;
        $sql = "select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image, mi.manufacturers_url
                from " . TABLE_MANUFACTURERS . " m
                left join " . TABLE_MANUFACTURERS_INFO . " mi
                on (m.manufacturers_id = mi.manufacturers_id
                and mi.languages_id = '" . $zm_request->getLanguageId() . "')
                where m.manufacturers_id = " . $manufacturerId;

        $results = $this->db_->Execute($sql);
        if (0 < $results->RecordCount()) {
            $manufacturer = $this->_newManufacturer($results->fields);
        }
        return $manufacturer;
    }


    // get manufacturer for product
    function getManufacturerForProduct($product) {
		    return $this->getManufacturerForId($product->manufacturerId_);
    }


    // get all manufacturers
    function getManufacturers() {
    global $zm_request;
        $sql = "select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image, mi.manufacturers_url
                from " . TABLE_MANUFACTURERS . " m
                left join " . TABLE_MANUFACTURERS_INFO . " mi
                on (m.manufacturers_id = mi.manufacturers_id
                and mi.languages_id = '" . $zm_request->getLanguageId() . "')";
        $results = $this->db_->Execute($sql);

        $manufacturers = array();
        while (!$results->EOF) {
            $manufacturer = $this->_newManufacturer($results->fields);
            array_push($manufacturers, $manufacturer);
            $results->MoveNext();
        }
        return $manufacturers;
    }

    
    function _newManufacturer($fields) {
        $manufacturer = new ZMManufacturer($fields['manufacturers_id'], $fields['manufacturers_name']);
        $manufacturer->image_ = $fields['manufacturers_image'];
        $manufacturer->url_ = $fields['manufacturers_url'];
        return $manufacturer;
    }

}

?>
