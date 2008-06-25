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
 * A product price group.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_group_pricing
 * @version $Id$
 */
class ProductGroupPricing extends ZMModel {
    var $id_;
    var $productId_;
    var $groupId_;
    var $discount_;
    var $type_;
    var $regularPriceOnly_;
    var $startDate_;
    var $endDate_;
    var $beforeTax_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        $this->id_ = 0;
        $this->productId_ = 0;
        $this->groupId_ = 0;
        $this->discount_ = 0;
        $this->type_ = '%';
        $this->regularPriceOnly_ = true;
        $this->startDate_ = null;
        $this->endDate_ = null;
        $this->beforeTax_ = true;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Populate all available fields from the given request.
     *
     * @param array req A request; if <code>null</code>, use the current <code>ZMRequest</code> instead.
     */
    function populate($req=null) {
        $this->id_ = ZMRequest::getParameter('groupPricingId', '0');
        $this->productId_ = ZMRequest::getParameter('productId', '0');
        $this->groupId_ = ZMRequest::getParameter('groupId', '0');
        $this->discount_ = ZMRequest::getParameter('discount', '0');
        $this->type_ = ZMRequest::getParameter('type', '%');
        $this->regularPriceOnly_ = ZMRequest::getParameter('regularPriceOnly', 0);
        $startDate = ZMRequest::getParameter('startDate');
        if (empty($startDate)) {
            // default to current date
            $startDate = ZMTools::translateDateString(date('Y-m-d'), 'yyyy-mm-dd', UI_DATE_FORMAT);
        }
        $this->startDate_ = ZMTools::translateDateString($startDate, UI_DATE_FORMAT, ZM_DATE_FORMAT);
        $this->endDate_ = ZMTools::translateDateString(ZMRequest::getParameter('endDate'), UI_DATE_FORMAT, ZM_DATE_FORMAT);
    }


    /**
     * Get the id.
     *
     * @return int The id.
     */
    function getId() { return $this->id_; }

    /**
     * Set the id.
     *
     * @param int id The id.
     */
    function setId($id) { $this->id_ = $id; }

    /**
     * Get the product id.
     *
     * @return int The product id.
     */
    function getProductId() { return $this->productId_; }

    /**
     * Set the product id.
     *
     * @param int productId The product id.
     */
    function setProductId($productId) { $this->productId_ = $productId; }

    /**
     * Get the group id.
     *
     * @return int The group id.
     */
    function getGroupId() { return $this->groupId_; }

    /**
     * Set the group id.
     *
     * @param int groupId The group id.
     */
    function setGroupId($groupId) { $this->groupId_ = $groupId; }

    /**
     * Get the discount.
     *
     * @return float The discount.
     */
    function getDiscount() { return $this->discount_; }

    /**
     * Set the before tax flag.
     *
     * @param boolean value The new value.
     */
    function setBeforeTax($value) { $this->beforeTax_ = $value; }

    /**
     * Get the before tax flag.
     *
     * @return boolean If <code>true</code> apply the discount before tax, otherwise after.
     */
    function isBeforeTax() { return $this->beforeTax_; }

    /**
     * Set the discount.
     *
     * @param float discount The discount.
     */
    function setDiscount($discount) { $this->discount_ = $discount; }

    /**
     * Get the discount type.
     *
     * @return string The type.
     */
    function getType() { return $this->type_; }

    /**
     * Set the discount type.
     *
     * @param string type The discount type.
     */
    function setType($type) { $this->type_ = $type; }

    /**
     * Check if discount applies to regular prices only.
     *
     * @return boolean <code>true<code> if this discount is valid for regular prices only, <code>false</code> if not.
     */
    function isRegularPriceOnly() { return $this->regularPriceOnly_; }

    /**
     * Configure whether the discount applies to regular prices only or not.
     *
     * @param boolean regularPriceOnly <code>true<code> if this discount is valid for regular prices only, <code>false</code> if not.
     */
    function setRegularPriceOnly($regularPriceOnly) { $this->regularPriceOnly_ = $regularPriceOnly; }

    /**
     * Get the start date.
     *
     * @return string The start date.
     */
    function getStartDate() { return $this->startDate_; }

    /**
     * Set the start date.
     *
     * @param string date The start date.
     */
    function setStartDate($date) { $this->startDate_ = $date; }

    /**
     * Get the end date.
     *
     * @return string The end date.
     */
    function getEndDate() { return $this->endDate_; }

    /**
     * Set the end date.
     *
     * @param string date The end date.
     */
    function setEndDate($date) { $this->endDate_ = $date; }

}

?>
