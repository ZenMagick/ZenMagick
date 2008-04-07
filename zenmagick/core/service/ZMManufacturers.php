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
    public static $MANUFACTURER_MAPPING = null;
    public static $MANUFACTURER_INFO_MAPPING = null;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        if (null == ZMManufacturers::$MANUFACTURER_MAPPING) {
            ZMManufacturers::$MANUFACTURER_MAPPING = array(
              'id' => 'column=manufacturers_id;type=integer;key=true;primary=true',
              'languageId' => 'column=languages_id;type=integer;readonly=true',
              'name' => 'column=manufacturers_name;type=string',
              'image' => 'column=manufacturers_image;type=string',
              'url' => 'column=manufacturers_url;type=string;readonly=true',
              'clickCount' => 'column=url_clicked;type=integer;readonly=true',
              'lastClick' => 'column=date_last_click;type=date;readonly=true'
            );
            ZMManufacturers::$MANUFACTURER_MAPPING = ZMDbUtils::addCustomFields(ZMManufacturers::$MANUFACTURER_MAPPING, TABLE_MANUFACTURERS);
        }
        if (null == ZMManufacturers::$MANUFACTURER_INFO_MAPPING) {
            ZMManufacturers::$MANUFACTURER_INFO_MAPPING = array(
              // id follows manufacturers id, so no autoincrement, or such
              'id' => 'column=manufacturers_id;type=integer;key=true',
              'languageId' => 'column=languages_id;type=integer;key=true',
              'url' => 'column=manufacturers_url;type=string'/*,
              'url_clicked' => 'column=url_clicked;type=integer',
              'date_last_click' => 'column=url_clicked;type=date'*/
            );
            ZMManufacturers::$MANUFACTURER_INFO_MAPPING = ZMDbUtils::addCustomFields(ZMManufacturers::$MANUFACTURER_INFO_MAPPING, TABLE_MANUFACTURERS_INFO);
        }
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

        $sql = "SELECT m.manufacturers_id, m.manufacturers_name, m.manufacturers_image,
                  mi.manufacturers_url, mi.languages_id, mi.url_clicked, mi.date_last_click
                   ".ZMDbUtils::getCustomFieldsSQL(TABLE_MANUFACTURERS, 'm')."
                FROM " . TABLE_MANUFACTURERS . " m
                LEFT JOIN " . TABLE_MANUFACTURERS_INFO . " mi
                ON (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = :languageId)
                WHERE m.manufacturers_id = :id";

        $args = array('id' => $id, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, ZMManufacturers::$MANUFACTURER_MAPPING, 'Manufacturer');
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
        ZMRuntime::getDatabase()->updateModel(TABLE_MANUFACTURERS, $manufacturer, ZMManufacturers::$MANUFACTURER_MAPPING);
        ZMRuntime::getDatabase()->updateModel(TABLE_MANUFACTURERS_INFO, $manufacturer, ZMManufacturers::$MANUFACTURER_INFO_MAPPING);
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

        $sql = "select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image,
                mi.manufacturers_url, mi.languages_id, mi.url_clicked, mi.date_last_click
                 ".ZMDbUtils::getCustomFieldsSQL(TABLE_MANUFACTURERS, 'm')."
                 ".ZMDbUtils::getCustomFieldsSQL(TABLE_MANUFACTURERS_INFO, 'mi')."
                from " . TABLE_MANUFACTURERS . " m
                left join " . TABLE_MANUFACTURERS_INFO . " mi
                on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = :languageId)";

        $args = array('languageId' => $languageId);
        return ZMRuntime::getDatabase()->query($sql, $args, ZMManufacturers::$MANUFACTURER_MAPPING, 'Manufacturer');
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
        return ZMRuntime::getDatabase()->update($sql, $args, ZMManufacturers::$MANUFACTURER_INFO_MAPPING);
    }

}

?>
