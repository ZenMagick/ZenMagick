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
class GroupPricingService extends ZMService {

    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function GroupPricingService() {
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
     * return ProductGroupPricing A <code>ProductGroupPricing</code> instance or <code>null</code>.
     */
    function getProductGroupPricing($productId, $groupId) {
        $db = $this->getDB();
        $sql = "select * from " . ZM_TABLE_GROUP_PRICING . "
                where products_id = :productId
                and group_id =:groupId";
        $sql = $db->bindVars($sql, ":productId", $productId, "integer");
        $sql = $db->bindVars($sql, ":groupId", $groupId, "integer");

        $productGroupPricing = null;
        $results = $db->Execute($sql);
        if (!$results->EOF) {
            $productGroupPricing = $this->_newProductGroupPricing($results->fields);
        }

        return $productGroupPricing;
    }


    /**
     * Create a new group pricing.
     *
     * @param ProductGroupPricing groupPricing The new product group pricing.
     * @return ProductGroupPricing The created product group pricing incl. the id.
     */
    function &createProductGroupPricing(&$groupPricing) {
        $db = $this->getDB();
        $sql = "insert into " . ZM_TABLE_GROUP_PRICING . "(
                 products_id, group_id,
                 discount, type, regular_price_only,
                 start_date, endt_date
                ) values (:productId;integer, :groupId;integer,
                  :discount;float, :type;string, :regularPriceOnly;integer,
                  :startDate;date, :endDate;date)";
        $sql = $this->bindObject($sql, $groupPricing);
        $db->Execute($sql);
        $groupPricing->id_ = $db->Insert_ID();

        return $groupPricing;
    }

    /**
     * Create new product group pricing.
     */
    function &_newProductGroupPricing($fields) {
        $productGroupPricing = $this->create("ProductGroupPricing");
        $productGroupPricing->id_ = $fields['group_pricing_id'];
        $productGroupPricing->productId_ = $fields['products_id'];
        $productGroupPricing->groupId_ = $fields['group_id'];
        $productGroupPricing->discount_ = $fields['discount'];
        $productGroupPricing->type_ = $fields['type'];
        $productGroupPricing->regularPriceOnly_ = $fields['regular_price_only'];
        $productGroupPricing->startDate_ = $fields['start_date'];
        $productGroupPricing->endDate_ = $fields['endt_date'];
        return $productGroupPricing;
    }

}

?>
