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
    function __construct() {
        parent::__construct();
        ZMDbTableMapper::instance()->setMappingForTable('product_group_pricing', array(
            'id' => 'column=group_pricing_id;type=integer;key=true;auto=true',
            'productId' => 'column=products_id;type=integer',
            'groupId' => 'column=group_id;type=integer',
            'discount' => 'column=discount;type=float',
            'type' => 'column=type;type=string',
            'allowSaleSpecial' => 'column=allow_sale_special;type=boolean',
            'startDate' => 'column=start_date;type=datetime',
            'endDate' => 'column=end_date;type=datetime',
        ));
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
        $args = array('productId' => $productId, 'groupId' => $groupId, 'endDate' => ZMDatabase::NULL_DATETIME);
        return ZMRuntime::getDatabase()->query($sql, $args, DB_PREFIX.'product_group_pricing', 'ZMProductGroupPricing');
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
