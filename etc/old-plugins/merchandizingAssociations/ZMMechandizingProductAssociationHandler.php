<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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


define('ZM_TABLE_PRODUCT_ASSOCIATIONS', ZM_DB_PREFIX . 'zm_product_associations');


/**
 * Product associations service.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.merchandizingAssociations
 */
class ZMMechandizingProductAssociationHandler extends ZMObject {
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
                'type' => 'column=association_type;type=string',
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
     * @param string type The association type.
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
     * @param string type The association type.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc.
     * return array A list of <code>ProductAssociation</code> instances.
     */
    public function getProductAssociationsForCategoryId($categoryId, $type, $all=false) {
        $associations = array();

        $productIds = ZMProducts::instance()->getProductIdsForCategoryId($categoryId, !$all, false);
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
     * @param string type The association type.
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
