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


define('ZM_TABLE_GROUP_PRICING', ZM_DB_PREFIX . 'zm_group_pricing');


/**
 * Service class for product based grou pricing
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_group_pricing
 * @version $Id$
 */
class ProductGroupPricingService extends ZMService {
    var $fieldMap_ = array(
        // db column, model property name, data type
        array('group_pricing_id', 'id', 'integer'),
        array('products_id', 'productId', 'integer'),
        array('group_id', 'groupId', 'integer'),
        array('discount', 'discount', 'float'),
        array('type', 'type', 'string'), 
        array('regular_price_only', 'regularPriceOnly', 'integer'),
        array('start_date', 'startDate', 'date'),
        array('end_date', 'endDate', 'date')
    );


    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function ProductGroupPricingService() {
        $this->__construct();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get product group pricing for the given product and group.
     *
     * @param int productId The source product id.
     * @param int groupId The group id.
     * @param boolean active If set to <code>true</code> consider active (date) pricings only; default is <code>true</code>.
     * return ProductGroupPricing A <code>ProductGroupPricing</code> instance or <code>null</code>.
     */
    function getProductGroupPricing($productId, $groupId, $active=true) {
        $db = $this->getDB();

        if ($active) {
            $dateLimit = ' and start_date <= now() and (end_date > now() or end_date is NULL) ';
        }
        $sql = "select * from " . ZM_TABLE_GROUP_PRICING . "
                where products_id = :productId
                and group_id = :groupId".$dateLimit;
        $sql = $db->bindVars($sql, ":productId", $productId, "integer");
        $sql = $db->bindVars($sql, ":groupId", $groupId, "integer");

        $productGroupPricing = null;
        $results = $db->Execute($sql);
        if (!$results->EOF) {
            $productGroupPricing = $this->map2obj('ProductGroupPricing', $results->fields);
        }

        return $productGroupPricing;
    }


    /**
     * Create a new group pricing.
     *
     * @param ProductGroupPricing groupPricing The new product group pricing.
     * @return ProductGroupPricing The created product group pricing incl. the id.
     */
    function createProductGroupPricing(&$groupPricing) {
        $db = $this->getDB();
        $sql = "insert into " . ZM_TABLE_GROUP_PRICING . "(
                 products_id, group_id,
                 discount, type, regular_price_only,
                 start_date, end_date
                ) values (:productId;integer, :groupId;integer,
                  :discount;float, :type;string, :regularPriceOnly;integer,
                  :startDate;date, :endDate;date)";
        $sql = $this->bindObject($sql, $groupPricing, false);
        $db->Execute($sql);
        $groupPricing->id_ = $db->Insert_ID();

        return $groupPricing;
    }

    /**
     * Update an existing product group pricing.
     *
     * @param ProductGroupPricing groupPricing The new product group pricing.
     * @return ProductGroupPricing The created product group pricing incl. the id.
     */
    function updateProductGroupPricing(&$groupPricing) {
        $db = $this->getDB();
        $sql = "update " . ZM_TABLE_GROUP_PRICING . " set
                products_id = :productId;integer,
                group_id = :groupId;integer,
                discount = :discount;float,
                type = :type;string, 
                regular_price_only = :regularPriceOnly;integer,
                start_date = :startDate;date,
                end_date = :endDate;date
                where group_pricing_id = :groupPricingId";
        $sql = $db->bindVars($sql, ":groupPricingId", $groupPricing->getId(), "integer");
        $sql = $this->bindObject($sql, $groupPricing, false);
        $db->Execute($sql);

        return $groupPricing;
    }

}

?>
