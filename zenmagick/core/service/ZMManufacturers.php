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
 * Manufacturers.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMManufacturers extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Manufacturers');
    }


    /**
     * Get manufacturer for id.
     *
     * @param int id The manufacturer id.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return ZMManufacturer The manufacturer or <code>null</code>.
     */
    function getManufacturerForId($id, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT m.*, mi.*
                FROM " . TABLE_MANUFACTURERS . " m
                  LEFT JOIN " . TABLE_MANUFACTURERS_INFO . " mi
                  ON (m.manufacturers_id = mi.manufacturers_id AND mi.languages_id = :languageId)
                WHERE m.manufacturers_id = :id";
        $args = array('id' => $id, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_MANUFACTURERS, TABLE_MANUFACTURERS_INFO), 'Manufacturer');
    }

    /**
     * Get the manufacturer for the given product.
     *
     * @param ZMProduct product The product.
     * @return ZMManufacturer The manufacturer or </code>null</code>.
     */
    function getManufacturerForProduct($product) {
		    return $this->getManufacturerForId($product->manufacturerId_);
    }

    /**
     * Update an existing manufacturer.
     *
     * @param ZMManufacturer manufacturer The manufacturer.
     * @return ZMManufacturer The updated manufacturer.
     */
    function updateManufacturer($manufacturer) {
        ZMRuntime::getDatabase()->updateModel(TABLE_MANUFACTURERS, $manufacturer);
        ZMRuntime::getDatabase()->updateModel(TABLE_MANUFACTURERS_INFO, $manufacturer);
    }

    /**
     * Get all manufacturers.
     *
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array List of <code>ZMManufacturer</code> instances.
     */
    function getManufacturers($languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT m.*, mi.*
                FROM " . TABLE_MANUFACTURERS . " m
                  LEFT JOIN " . TABLE_MANUFACTURERS_INFO . " mi
                  ON (m.manufacturers_id = mi.manufacturers_id AND mi.languages_id = :languageId)";
        $args = array('languageId' => $languageId);
        return ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_MANUFACTURERS, TABLE_MANUFACTURERS_INFO), 'Manufacturer');
    }

    /**
     * Update manufacturers click stats.
     *
     * @param int id The manufacturer id.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     */
    function updateManufacturerClickCount($id, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "UPDATE " . TABLE_MANUFACTURERS_INFO . "
                SET url_clicked = url_clicked+1, date_last_click = now() 
                WHERE manufacturers_id = :id 
                AND languages_id = :languageId";
        $args = array('id' => $id, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->update($sql, $args, array(TABLE_MANUFACTURERS, TABLE_MANUFACTURERS_INFO));
    }

}

?>
