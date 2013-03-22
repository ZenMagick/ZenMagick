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

namespace ZenMagick\plugins\productGroupPricing\model;

use DateTime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;

/**
 * A product price group.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ProductGroupPricing extends ZMObject
{
    private $id;
    private $productId;
    private $groupId;
    private $discount;
    private $type;
    private $allowSaleSpecial;
    private $startDate;
    private $endDate;
    private $beforeTax;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->id = 0;
        $this->productId = 0;
        $this->groupId = 0;
        $this->discount = 0;
        $this->type = '%';
        $this->allowSaleSpecial = false;
        $this->startDate = null;
        $this->endDate = null;
        $this->beforeTax = true;
    }

    /**
     * Populate all available fields from the given request.
     *
     * @param ZenMagick\Http\Request request The current request.
     */
    public function populate($request)
    {
        $this->id = $request->getParameter('groupPricingId', '0');
        $this->productId = $request->get('productId');
        $this->groupId = $request->getParameter('groupId', '0');
        $this->discount = $request->getParameter('discount', '0');
        $this->type = $request->getParameter('type', '%');
        $this->allowSaleSpecial = Toolbox::asBoolean($request->getParameter('allowSaleSpecial', false));
        $startDate = $request->getParameter('startDate');
        if (empty($startDate)) {
            // default to current date
            $startDate = new DateTime();
        }
        $localeService = $this->container->get('localeService');
        $this->startDate = DateTime::createFromFormat($localeService->getFormat('date', 'short'), $startDate);
        $this->endDate = DateTime::createFromFormat($localeService->getFormat('date', 'short'), $request->getParameter('endDate'));
    }

    /**
     * Get the id.
     *
     * @return int The id.
     */
    public function getId() { return $this->id; }

    /**
     * Set the id.
     *
     * @param int id The id.
     */
    public function setId($id) { $this->id = $id; }

    /**
     * Get the product id.
     *
     * @return int The product id.
     */
    public function getProductId() { return $this->productId; }

    /**
     * Set the product id.
     *
     * @param int productId The product id.
     */
    public function setProductId($productId) { $this->productId = $productId; }

    /**
     * Get the group id.
     *
     * @return int The group id.
     */
    public function getGroupId() { return $this->groupId; }

    /**
     * Set the group id.
     *
     * @param int groupId The group id.
     */
    public function setGroupId($groupId) { $this->groupId = $groupId; }

    /**
     * Get the discount.
     *
     * @return float The discount.
     */
    public function getDiscount() { return $this->discount; }

    /**
     * Set the before tax flag.
     *
     * @param boolean value The new value.
     */
    public function setBeforeTax($value) { $this->beforeTax = $value; }

    /**
     * Get the before tax flag.
     *
     * @return boolean If <code>true</code> apply the discount before tax, otherwise after.
     */
    public function isBeforeTax() { return $this->beforeTax; }

    /**
     * Set the discount.
     *
     * @param float discount The discount.
     */
    public function setDiscount($discount) { $this->discount = $discount; }

    /**
     * Get the discount type.
     *
     * @return string The type.
     */
    public function getType() { return $this->type; }

    /**
     * Set the discount type.
     *
     * @param string type The discount type.
     */
    public function setType($type) { $this->type = $type; }

    /**
     * Check if discount also applies to sale/special prices.
     *
     * @return boolean <code>true<code> if this discount is valid for sale/special prices too, <code>false</code> if not.
     */
    public function isAllowSaleSpecial() { return $this->allowSaleSpecial; }

    /**
     * Configure whether the discount also applies to sale/special prices or not.
     *
     * @param boolean allowSaleSpecial <code>true<code> if this discount is also valid for sale/special prices, <code>false</code> if not.
     */
    public function setAllowSaleSpecial($allowSaleSpecial) { $this->allowSaleSpecial = $allowSaleSpecial; }

    /**
     * Get the start date.
     *
     * @return string The start date.
     */
    public function getStartDate() { return $this->startDate; }

    /**
     * Set the start date.
     *
     * @param string date The start date.
     */
    public function setStartDate($date) { $this->startDate = $date; }

    /**
     * Get the end date.
     *
     * @return string The end date.
     */
    public function getEndDate() { return $this->endDate; }

    /**
     * Set the end date.
     *
     * @param string date The end date.
     */
    public function setEndDate($date) { $this->endDate = $date; }

}
