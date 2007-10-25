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
 * Manufacturers.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMManufacturers extends ZMService {


    /**
     * Default c'tor.
     */
    function ZMManufacturers() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMManufacturers();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // get manufacturer for id
    /**
     * Get manufacturer for id.
     *
     * @param int id The manufacturer id.
     * @return ZMManufacturer The manufacturer or <code>null</code>.
     */
    function &getManufacturerForId($id) {
    global $zm_runtime;

        $manufacturer = null;
        $db = $this->getDB();
        $sql = "select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image, mi.manufacturers_url
                from " . TABLE_MANUFACTURERS . " m
                left join " . TABLE_MANUFACTURERS_INFO . " mi
                on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = :languageId)
                where m.manufacturers_id = :manufacturerId";
        $sql = $db->bindVars($sql, ':languageId', $zm_runtime->getLanguageId(), 'integer');
        $sql = $db->bindVars($sql, ':manufacturerId', $id, 'integer');

        $results = $db->Execute($sql);
        if (0 < $results->RecordCount()) {
            $manufacturer = $this->_newManufacturer($results->fields);
        }

        return $manufacturer;
    }

    /**
     * Get the manufacturer for the given product.
     *
     * @param ZMProduct product The product.
     * @return ZMManufacturer The manufacturer or </code>null</code>.
     */
    function &getManufacturerForProduct($product) {
		    return $this->getManufacturerForId($product->manufacturerId_);
    }

    /**
     * Get all manufacturers.
     *
     * @return array List of <code>ZMManufacturer</code> instances.
     */
    function getManufacturers() {
    global $zm_runtime;

        $db = $this->getDB();
        $sql = "select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image, mi.manufacturers_url
                from " . TABLE_MANUFACTURERS . " m
                left join " . TABLE_MANUFACTURERS_INFO . " mi
                on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = :languageId)";
        $sql = $db->bindVars($sql, ':languageId', $zm_runtime->getLanguageId(), 'integer');
        $results = $db->Execute($sql);

        $manufacturers = array();
        while (!$results->EOF) {
            $manufacturer = $this->_newManufacturer($results->fields);
            array_push($manufacturers, $manufacturer);
            $results->MoveNext();
        }
        return $manufacturers;
    }


    /**
     * Create new manufacturer instance.
     */
    function &_newManufacturer($fields) {
        $manufacturer = $this->create("Manufacturer", $fields['manufacturers_id'], $fields['manufacturers_name']);
        $manufacturer->image_ = $fields['manufacturers_image'];
        $manufacturer->url_ = $fields['manufacturers_url'];
        return $manufacturer;
    }

}

?>
