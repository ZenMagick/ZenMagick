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
 * @author DerManoMann
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMManufacturers extends ZMObject {
    private $cache;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->cache = ZMCaches::instance()->getCache('services', array(), ZMCache::TRANSIENT);
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
    public function getManufacturerForId($id, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT m.*, mi.*
                FROM " . TABLE_MANUFACTURERS . " m
                  LEFT JOIN " . TABLE_MANUFACTURERS_INFO . " mi
                  ON (m.manufacturers_id = mi.manufacturers_id AND mi.languages_id = :languageId)
                WHERE m.manufacturers_id = :manufacturerId";
        $args = array('manufacturerId' => $id, 'languageId' => $languageId);

        $cacheKey = ZMTools::mkUnique($id, $languageId);
        if (false === ($manufacturer = $this->cache->lookup($cacheKey))) {
            $manufacturer = ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_MANUFACTURERS, TABLE_MANUFACTURERS_INFO), 'Manufacturer');
            $this->cache->save($manufacturer, $cacheKey);
        }

        return $manufacturer;
    }

    /**
     * Get the manufacturer for the given product.
     *
     * @param ZMProduct product The product.
     * @return ZMManufacturer The manufacturer or </code>null</code>.
     */
    public function getManufacturerForProduct($product) {
		    return $this->getManufacturerForId($product->manufacturerId_);
    }

    /**
     * Update an existing manufacturer.
     *
     * @param ZMManufacturer manufacturer The manufacturer.
     * @return ZMManufacturer The updated manufacturer.
     */
    public function updateManufacturer($manufacturer) {
        ZMRuntime::getDatabase()->updateModel(TABLE_MANUFACTURERS, $manufacturer);
        ZMRuntime::getDatabase()->updateModel(TABLE_MANUFACTURERS_INFO, $manufacturer);
    }

    /**
     * Get all manufacturers.
     *
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array List of <code>ZMManufacturer</code> instances.
     */
    public function getManufacturers($languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT m.*, mi.*
                FROM " . TABLE_MANUFACTURERS . " m
                  LEFT JOIN " . TABLE_MANUFACTURERS_INFO . " mi
                  ON (m.manufacturers_id = mi.manufacturers_id AND mi.languages_id = :languageId)";
        $args = array('languageId' => $languageId);

        $cacheKey = '-'.$languageId;
        if (false === ($manufacturers = $this->cache->lookup($cacheKey))) {
            $manufacturers = ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_MANUFACTURERS, TABLE_MANUFACTURERS_INFO), 'Manufacturer');
            $this->cache->save($manufacturers, $cacheKey);
        }

        return $manufacturers;
    }

    /**
     * Update manufacturers click stats.
     *
     * @param int id The manufacturer id.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     */
    public function updateManufacturerClickCount($id, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        // remove from cache
        $cacheKey = $id.'-'.$languageId;
        $this->cache->remove($cacheKey);

        $sql = "UPDATE " . TABLE_MANUFACTURERS_INFO . "
                SET url_clicked = url_clicked+1, date_last_click = now() 
                WHERE manufacturers_id = :manufacturerId 
                AND languages_id = :languageId";
        $args = array('manufacturerId' => $id, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->update($sql, $args, array(TABLE_MANUFACTURERS, TABLE_MANUFACTURERS_INFO));
    }

}

?>
