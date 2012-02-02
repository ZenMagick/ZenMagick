<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\apps\store\services\catalog;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Manufacturer service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ManufacturerService extends ZMObject {
    private $cache_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->cache_ = null;
    }


    /**
     * Set the cache.
     *
     * @param zenmagick\base\cache\Cache cache The cache.
     */
    public function setCache($cache) {
        $this->cache_ = $cache;
    }

    /**
     * Get the cache.
     *
     * @return zenmagick\base\cache\Cache The cache.
     */
    public function getCache() {
        return $this->cache_;
    }

    /**
     * Get manufacturer for id.
     *
     * @param int id The manufacturer id.
     * @param int languageId Language id.
     * @return Manufacturer The manufacturer or <code>null</code>.
     */
    public function getManufacturerForId($id, $languageId) {
        $sql = "SELECT mi.*, m.*
                FROM " . TABLE_MANUFACTURERS . " m
                  LEFT JOIN " . TABLE_MANUFACTURERS_INFO . " mi ON (m.manufacturers_id = mi.manufacturers_id AND mi.languages_id = :languageId)
                WHERE m.manufacturers_id = :manufacturerId";
        $args = array('manufacturerId' => $id, 'languageId' => $languageId);

        $cacheKey = \ZMLangUtils::mkUnique('manufacturer', $id, $languageId);
        if (false === ($manufacturer = $this->cache_->lookup($cacheKey))) {
            $manufacturer = \ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_MANUFACTURERS, TABLE_MANUFACTURERS_INFO), 'zenmagick\apps\store\entities\catalog\Manufacturer');
            $this->cache_->save($manufacturer, $cacheKey);
        }

        return $manufacturer;
    }

    /**
     * Get manufacturer for name.
     *
     * @param string name The manufacturer name.
     * @param int languageId Language id.
     * @return Manufacturer The manufacturer or <code>null</code>.
     */
    public function getManufacturerForName($name, $languageId) {
        $sql = "SELECT mi.*, m.*
                FROM " . TABLE_MANUFACTURERS . " m
                  LEFT JOIN " . TABLE_MANUFACTURERS_INFO . " mi ON (m.manufacturers_id = mi.manufacturers_id AND mi.languages_id = :languageId)
                WHERE m.manufacturers_name LIKE :name";
        $args = array('name' => $name, 'languageId' => $languageId);

        $cacheKey = \ZMLangUtils::mkUnique('manufacturer', $name, $languageId);
        if (false === ($manufacturer = $this->cache_->lookup($cacheKey))) {
            $manufacturer = \ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_MANUFACTURERS, TABLE_MANUFACTURERS_INFO), 'zenmagick\apps\store\entities\catalog\Manufacturer');
            $this->cache_->save($manufacturer, $cacheKey);
        }

        return $manufacturer;
    }

    /**
     * Get the manufacturer for the given product.
     *
     * @param Product product The product.
     * @return Manufacturer The manufacturer or </code>null</code>.
     */
    public function getManufacturerForProduct($product) {
		    return $this->getManufacturerForId($product->getManufacturerId(), $product->getLanguageId());
    }

    /**
     * Update an existing manufacturer.
     *
     * @param Manufacturer manufacturer The manufacturer.
     * @return Manufacturer The updated manufacturer.
     */
    public function updateManufacturer($manufacturer) {
        $manufacturer = \ZMRuntime::getDatabase()->updateModel(TABLE_MANUFACTURERS, $manufacturer);
        \ZMRuntime::getDatabase()->updateModel(TABLE_MANUFACTURERS_INFO, $manufacturer);
        return $manufacturer;
    }

    /**
     * Create a manufacturer.
     *
     * @param Manufacturer manufacturer The manufacturer.
     * @return Manufacturer The created manufacturer.
     */
    public function createManufacturer($manufacturer) {
        $manufacturer = \ZMRuntime::getDatabase()->createModel(TABLE_MANUFACTURERS, $manufacturer);
        \ZMRuntime::getDatabase()->createModel(TABLE_MANUFACTURERS_INFO, $manufacturer);
        return $manufacturer;
    }

    /**
     * Get all manufacturers.
     *
     * @param int languageId Language id.
     * @return array List of <code>Manufacturer</code> instances.
     */
    public function getManufacturers($languageId) {
        $sql = "SELECT mi.*, m.*
                FROM " . TABLE_MANUFACTURERS . " m
                  LEFT JOIN " . TABLE_MANUFACTURERS_INFO . " mi ON (m.manufacturers_id = mi.manufacturers_id AND mi.languages_id = :languageId)
                ORDER BY m.manufacturers_name";
        $args = array('languageId' => $languageId);

        $cacheKey = \ZMLangUtils::mkUnique('manufacturer', $languageId);
        if (false === ($manufacturers = $this->cache_->lookup($cacheKey))) {
            $manufacturers = \ZMRuntime::getDatabase()->fetchAll($sql, $args, array(TABLE_MANUFACTURERS, TABLE_MANUFACTURERS_INFO), 'zenmagick\apps\store\entities\catalog\Manufacturer');
            $this->cache_->save($manufacturers, $cacheKey);
        }

        return $manufacturers;
    }

    /**
     * Update manufacturers click stats.
     *
     * @param int id The manufacturer id.
     * @param int languageId Language id.
     */
    public function updateManufacturerClickCount($id, $languageId) {
        // clear global cache
        $cacheKey = \ZMLangUtils::mkUnique('manufacturer', $languageId);
        $this->cache_->remove($cacheKey);
        // remove from cache
        $cacheKey = \ZMLangUtils::mkUnique('manufacturer', $id, $languageId);
        $this->cache_->remove($cacheKey);

        $sql = "UPDATE " . TABLE_MANUFACTURERS_INFO . "
                SET url_clicked = url_clicked+1, date_last_click = now()
                WHERE manufacturers_id = :manufacturerId
                AND languages_id = :languageId";
        $args = array('manufacturerId' => $id, 'languageId' => $languageId);
        return \ZMRuntime::getDatabase()->updateObj($sql, $args, array(TABLE_MANUFACTURERS, TABLE_MANUFACTURERS_INFO));
    }

}
