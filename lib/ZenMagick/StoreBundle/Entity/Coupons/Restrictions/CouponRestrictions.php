<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

namespace ZenMagick\StoreBundle\Entity\Coupons\Restrictions;

use ZenMagick\Base\ZMObject;

/**
 * Collection of coupon restrictions.
 *
 * @author DerManoMann
 */
class CouponRestrictions extends ZMObject
{
    private $categories_;
    private $products_;

    /**
     * Create new instance.
     */
    public function __construct($categories=array(), $products=array())
    {
        parent::__construct();
        $this->categories_ = $categories;
        $this->products_ = $products;
    }

    /**
     * Checks if there are restrictions.
     *
     * @return boolean <code>true</code> if restrictions exist, <code>false</code> if not.
     */
    public function hasRestrictions()
    {
        return 0 != count($this->categories_) || 0 < count($this->products_);
    }

    /**
     * Checks if there are categories.
     *
     * @return boolean <code>true</code> if categories exist, <code>false</code> if not.
     */
    public function hasCategories()
    {
        return 0 != count($this->categories_);
    }

    /**
     * Checks if there are products.
     *
     * @return boolean <code>true</code> if products exist, <code>false</code> if not.
     */
    public function hasProducts()
    {
        return 0 < count($this->products_);
    }

    /**
     * Returns the category restrictions.
     *
     * @return array An array of <code>CouponRestricton</code> instances.
     */
    public function getCategories()
    {
        return $this->categories_;
    }

    /**
     * Returns the product restrictions.
     *
     * @return array An array of <code>CouponRestricton</code> instances.
     */
    public function getProducts()
    {
        return $this->products_;
    }

}
