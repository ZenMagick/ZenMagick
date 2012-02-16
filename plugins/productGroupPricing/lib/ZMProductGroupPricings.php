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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;


/**
 * Service class for product based group pricing
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.productGroupPricing
 */
class ZMProductGroupPricings extends ZMObject {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        ZMRuntime::getDatabase()->getMapper()->setMappingForTable('product_group_pricing', array(
            'id' => array('column' => 'group_pricing_id', 'type' => 'integer', 'key' => true, 'auto' => true),
            'productId' => array('column' => 'products_id', 'type' => 'integer'),
            'groupId' => array('column' => 'group_id', 'type' => 'integer'),
            'discount' => array('column' => 'discount', 'type' => 'float'),
            'type' => array('column' => 'type', 'type' => 'string'),
            'allowSaleSpecial' => array('column' => 'allow_sale_special', 'type' => 'boolean'),
            'startDate' => array('column' => 'start_date', 'type' => 'datetime'),
            'endDate' => array('column' => 'end_date', 'type' => 'datetime'),
        ));
    }


    /**
     * Get instance.
     */
    public static function instance() {
        return Runtime::getContainer()->get('productGroupPricingService');
    }


    /**
     * Get product group pricings for the given product and group.
     *
     * @param int productId The source product id.
     * @param int groupId The group id.
     * @param boolean active If set to <code>true</code> consider active (date) pricings only; default is <code>true</code>.
     * @return array A list of <code>ProductGroupPricing</code> instances.
     */
    public function getProductGroupPricings($productId, $groupId, $active=true) {
        $dateLimit = '';
        if ($active) {
            $dateLimit = ' AND start_date <= now() AND (end_date > now() OR end_date is NULL OR end_date = :endDate) ';
        }
        $sql = "SELECT * FROM " . DB_PREFIX.'product_group_pricing' . "
                WHERE products_id = :productId
                AND group_id = :groupId".$dateLimit;
        $sql .= " ORDER BY start_date ASC";
        $args = array('productId' => $productId, 'groupId' => $groupId, 'endDate' => null);
        return ZMRuntime::getDatabase()->fetchAll($sql, $args, DB_PREFIX.'product_group_pricing', 'ZMProductGroupPricing');
    }

    /**
     * Get product group pricings for the given id.
     *
     * @param int groupPricingId The group pricing id.
     * @return ProductGroupPricing A <code>ProductGroupPricing</code> or <code>null</code>.
     */
    public function getProductGroupPricingForId($groupPricingId) {
        $sql = "SELECT * FROM " . DB_PREFIX.'product_group_pricing' . "
                WHERE group_pricing_id = :id";
        $args = array('id' => $groupPricingId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, DB_PREFIX.'product_group_pricing', 'ZMProductGroupPricing');
    }

    /**
     * Create a new group pricing.
     *
     * @param ProductGroupPricing groupPricing The new product group pricing.
     * @return ProductGroupPricing The created product group pricing incl. the id.
     */
    public function createProductGroupPricing($groupPricing) {
        return ZMRuntime::getDatabase()->createModel(DB_PREFIX.'product_group_pricing', $groupPricing);
    }

    /**
     * Update an existing product group pricing.
     *
     * @param ProductGroupPricing groupPricing The new product group pricing.
     * @return ProductGroupPricing The updated product group pricing.
     */
    public function updateProductGroupPricing($groupPricing) {
        ZMRuntime::getDatabase()->updateModel(DB_PREFIX.'product_group_pricing', $groupPricing);
        return $groupPricing;
    }

    /**
     * Remove a group pricing.
     *
     * @param ProductGroupPricing groupPricing The new product group pricing.
     * @return ProductGroupPricing The created product group pricing incl. the id.
     */
    public function removeProductGroupPricing($groupPricing) {
        return ZMRuntime::getDatabase()->removeModel(DB_PREFIX.'product_group_pricing', $groupPricing);
    }

}
