<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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


define('ZM_TABLE_PRODUCT_ASSOCIATION_TYPES', ZM_DB_PREFIX . 'zm_product_association_types');
define('ZM_TABLE_PRODUCT_ASSOCIATIONS', ZM_DB_PREFIX . 'zm_product_associations');


/**
 * Product associations service.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_product_associations
 * @version $Id$
 */
class ZMProductAssociations extends ZMObject {
    private $associationTypes_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->associationTypes_ = array();

        ZMDbTableMapper::instance()->setMappingForTable('zm_product_associations',
            array(
                'id' => 'column=association_id;type=integer;key=true;auto=true',
                'type' => 'column=association_type;type=integer',
                'sourceId' => 'column=source_product_id;type=integer',
                'targetId' => 'column=target_product_id;type=integer',
                'startDate' => 'column=start_date;type=datetime',
                'endDate' => 'column=end_date;type=datetime',
                'defaultQuantity' => 'column=default_quantity;type=float',
                'sortOrder' => 'column=sort_order;type=integer'
            )
        );
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('ProductAssociations');
    }


    /**
     * Check if required tables are installed.
     *
     * @return boolean <code>true</code> if installed, <code>false</code> if not.
     */
    public function isInstalled() {
        $count = 0;
        $metaData = ZMRuntime::getDatabase()->getMetaData(null);
        foreach ($metaData['tables'] as $table) {
            if (ZM_TABLE_PRODUCT_ASSOCIATION_TYPES == $table || ZM_TABLE_PRODUCT_ASSOCIATIONS == $table) {
                ++$count;
            }
        }

        return 2 == $count;
    }


    /**
     * Set up all product association types as defines.
     *
     * <p>Names are build following these rules:</p>
     * <ol>
     *  <li>replace dash ('-') and space (' ') with underscores ('_')</li>
     *  <li>convert to uppercase</li>
     *  <li>prefix with <code>ZM_PA_</code></li>
     * </ol>
     *zm_product_association_types
     */
    public function prepareAssociationTypes() {
        $sql = "SELECT * FROM " . ZM_TABLE_PRODUCT_ASSOCIATION_TYPES;
        foreach (ZMRuntime::getDatabase()->query($sql, array(), array(), ZMDatabase::MODEL_RAW) as $result) {
            $type = $result['association_type'];
            $clearName = $result['association_type_name'];
            $name = str_replace('-', '_', $clearName);
            $name = str_replace(' ', '_', $name);
            $name = strtoupper($name);
            $name = 'ZM_PA_'.$name;
            define($name, $type);
            $this->associationTypes_[$name] = $clearName;
        }
    }

    /**
     * Get association types.
     *
     * @return array Map of association type => name.
     */
    public function getAssociationTypes() {
        return $this->associationTypes_;
    }

    /**
     * Get product associations for the given product and type.
     *
     * @param int productId The source product id.
     * @param int type The association type.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc.
     * return array A list of <code>ZMProductAssociation</code> instances.
     */
    public function getProductAssociationsForProductId($productId, $type, $all=false) {
        $dateLimit = '';
        if (!$all) {
            $dateLimit = ' AND start_date <= now() AND (end_date > now() OR end_date IS NULL) ';
        }
        $sql = "SELECT DISTINCT * FROM " . ZM_TABLE_PRODUCT_ASSOCIATIONS . "
                WHERE source_product_id = :sourceId
                  AND association_type =:type" . $dateLimit . "
                ORDER BY sort_order ASC";
        return ZMRuntime::getDatabase()->query($sql, array('sourceId' => $productId, 'type' => $type), ZM_TABLE_PRODUCT_ASSOCIATIONS, 'ProductAssociation');
    }

    /**
     * Get associated products for the given category.
     *
     * @param int categoryId The category.
     * @param int type The association type.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc.
     * return array A list of <code>ProductAssociation</code> instances.
     */
    public function getProductAssociationsForCategoryId($categoryId, $type, $all=false) {
        $associations = array();

        $productIds = ZMProducts::instance()->getProductIdsForCategoryId($categoryId, !$all);
        if (0 == count($productIds)) {
            return $associations;
        }

        $dateLimit = '';
        if (!$all) {
            $dateLimit = ' AND start_date <= now() AND (end_date > now() OR end_date IS NULL) ';
        }
        $sql = "SELECT DISTINCT * FROM " . ZM_TABLE_PRODUCT_ASSOCIATIONS . "
                WHERE source_product_id in (:sourceId)
                  AND association_type =:type" . $dateLimit . "
                ORDER BY sort_order ASC";
        return ZMRuntime::getDatabase()->query($sql, array('sourceId' => $productIds, 'type' => $type), ZM_TABLE_PRODUCT_ASSOCIATIONS, 'ProductAssociation');
    }

    /**
     * Get associated products for the given shopping cart.
     *
     * @param ZMShoppingCart shoppingCart The shopping cart.
     * @param int type The association type.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc.
     * return array A list of <code>ProductAssociation</code> instances.
     */
    public function getProductAssociationsForShoppingCart($shoppingCart, $type, $all=false) {

        $associations = array();
        $productIds = array();
        foreach ($shoppingCart->getItems() as $item) {
            $productIds[] = $item->getId();
        }

        if (0 == count($productIds)) {
            return $associations;
        }

        $dateLimit = '';
        if (!$all) {
            $dateLimit = ' AND start_date <= now() AND (end_date > now() OR end_date IS NULL) ';
        }
        $sql = "SELECT DISTINCT * FROM " . ZM_TABLE_PRODUCT_ASSOCIATIONS . "
                WHERE source_product_id in (:productIdList)
                  AND association_type =:type" . $dateLimit . "
                ORDER BY sort_order ASC";
        return ZMRuntime::getDatabase()->query($sql, array('sourceId' => $productIds, 'type' => $type), ZM_TABLE_PRODUCT_ASSOCIATIONS, 'ProductAssociation');
    }

}

?>
