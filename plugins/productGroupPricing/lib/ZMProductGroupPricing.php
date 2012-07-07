<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\Toolbox;
use zenmagick\base\ZMObject;

/**
 * A product price group.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.productGroupPricing
 */
class ZMProductGroupPricing extends ZMObject {
    private $id_;
    private $productId_;
    private $groupId_;
    private $discount_;
    private $type_;
    private $allowSaleSpecial_;
    private $startDate_;
    private $endDate_;
    private $beforeTax_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();

        $this->id_ = 0;
        $this->productId_ = 0;
        $this->groupId_ = 0;
        $this->discount_ = 0;
        $this->type_ = '%';
        $this->allowSaleSpecial_ = false;
        $this->startDate_ = null;
        $this->endDate_ = null;
        $this->beforeTax_ = true;
    }


    /**
     * Populate all available fields from the given request.
     *
     * @param zenmagick\http\Request request The current request.
     */
    public function populate($request) {
        $this->id_ = $request->getParameter('groupPricingId', '0');
        $this->productId_ = $request->get('productId');
        $this->groupId_ = $request->getParameter('groupId', '0');
        $this->discount_ = $request->getParameter('discount', '0');
        $this->type_ = $request->getParameter('type', '%');
        $this->allowSaleSpecial_ = Toolbox::asBoolean($request->getParameter('allowSaleSpecial', false));
        $startDate = $request->getParameter('startDate');
        if (empty($startDate)) {
            // default to current date
            $startDate = new DateTime();
        }
        $localeService = $this->container->get('localeService');
        $this->startDate_ = DateTime::createFromFormat($localeService->getLocale()->getFormat('date', 'short'), $startDate);
        $this->endDate_ = DateTime::createFromFormat($localeService->getLocale()->getFormat('date', 'short'), $request->getParameter('endDate'));
    }


    /**
     * Get the id.
     *
     * @return int The id.
     */
    public function getId() { return $this->id_; }

    /**
     * Set the id.
     *
     * @param int id The id.
     */
    public function setId($id) { $this->id_ = $id; }

    /**
     * Get the product id.
     *
     * @return int The product id.
     */
    public function getProductId() { return $this->productId_; }

    /**
     * Set the product id.
     *
     * @param int productId The product id.
     */
    public function setProductId($productId) { $this->productId_ = $productId; }

    /**
     * Get the group id.
     *
     * @return int The group id.
     */
    public function getGroupId() { return $this->groupId_; }

    /**
     * Set the group id.
     *
     * @param int groupId The group id.
     */
    public function setGroupId($groupId) { $this->groupId_ = $groupId; }

    /**
     * Get the discount.
     *
     * @return float The discount.
     */
    public function getDiscount() { return $this->discount_; }

    /**
     * Set the before tax flag.
     *
     * @param boolean value The new value.
     */
    public function setBeforeTax($value) { $this->beforeTax_ = $value; }

    /**
     * Get the before tax flag.
     *
     * @return boolean If <code>true</code> apply the discount before tax, otherwise after.
     */
    public function isBeforeTax() { return $this->beforeTax_; }

    /**
     * Set the discount.
     *
     * @param float discount The discount.
     */
    public function setDiscount($discount) { $this->discount_ = $discount; }

    /**
     * Get the discount type.
     *
     * @return string The type.
     */
    public function getType() { return $this->type_; }

    /**
     * Set the discount type.
     *
     * @param string type The discount type.
     */
    public function setType($type) { $this->type_ = $type; }

    /**
     * Check if discount also applies to sale/special prices.
     *
     * @return boolean <code>true<code> if this discount is valid for sale/special prices too, <code>false</code> if not.
     */
    public function isAllowSaleSpecial() { return $this->allowSaleSpecial_; }

    /**
     * Configure whether the discount also applies to sale/special prices or not.
     *
     * @param boolean allowSaleSpecial <code>true<code> if this discount is also valid for sale/special prices, <code>false</code> if not.
     */
    public function setAllowSaleSpecial($allowSaleSpecial) { $this->allowSaleSpecial_ = $allowSaleSpecial; }

    /**
     * Get the start date.
     *
     * @return string The start date.
     */
    public function getStartDate() { return $this->startDate_; }

    /**
     * Set the start date.
     *
     * @param string date The start date.
     */
    public function setStartDate($date) { $this->startDate_ = $date; }

    /**
     * Get the end date.
     *
     * @return string The end date.
     */
    public function getEndDate() { return $this->endDate_; }

    /**
     * Set the end date.
     *
     * @param string date The end date.
     */
    public function setEndDate($date) { $this->endDate_ = $date; }

}
