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
    private static $mapping_ = null;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        if (null == ZMManufacturers::$mapping_) {
            ZMManufacturers::$mapping_ = array(
              'id' => 'manufacturers_id:integer:primary',
              'name' => 'manufacturers_name:string',
              'image' => 'manufacturers_image:string',
              'url' => 'manufacturers_url:string',
              'languageId' => 'languageId:integer'
            );
            ZMManufacturers::$mapping_ = ZMDbUtils::addCustomFields(ZMManufacturers::$mapping_, TABLE_MANUFACTURERS);
        }
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return parent::instance('Manufacturers');
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

        $sql = "select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image, mi.manufacturers_url
                 ".ZMDbUtils::getCustomFieldsSQL(TABLE_MANUFACTURERS, 'm')."
                from " . TABLE_MANUFACTURERS . " m
                left join " . TABLE_MANUFACTURERS_INFO . " mi
                on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = :languageId)
                where m.manufacturers_id = :id";

        $args = array('id' => $id, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, ZMManufacturers::$mapping_, 'Manufacturer');
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

        $sql = "select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image, mi.manufacturers_url
                 ".ZMDbUtils::getCustomFieldsSQL(TABLE_MANUFACTURERS, 'm')."
                from " . TABLE_MANUFACTURERS . " m
                left join " . TABLE_MANUFACTURERS_INFO . " mi
                on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = :languageId)";

        $args = array('languageId' => $languageId);
        return ZMRuntime::getDatabase()->query($sql, $args, ZMManufacturers::$mapping_, 'Manufacturer');
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

        $db = ZMRuntime::getDB();
        $sql = "UPDATE " . TABLE_MANUFACTURERS_INFO . "
                SET url_clicked = url_clicked+1, date_last_click = now() 
                WHERE manufacturers_id = :manufacturersId 
                AND languages_id = :languagesId";
        $sql = $db->bindVars($sql, ':languageId', $languageId, 'integer');
        $sql = $db->bindVars($sql, ':manufacturerId', $id, 'integer');
        $db->Execute($sql);
    }

}

?>
