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

namespace ZenMagick\apps\store\model\coupons\restrictions;

use ZenMagick\base\ZMObject;

/**
 * Single coupon restriction.
 *
 * @author DerManoMann
 */
class CategoryCouponRestriction extends ZMObject {
    private $allowed_;
    private $categoryId_;


    /**
     * Create new instance.
     */
    public function __construct($allowed, $categoryId) {
        parent::__construct();
        $this->allowed_ = $allowed;
        $this->categoryId_ = $categoryId;
    }


    /**
     * Check if allowed.
     *
     * @return boolean <code>true</code> if allowed, <code>false</code> if not.
     */
    public function isAllowed() {
        return $this->allowed_;
    }

    /**
     * Returns the category.
     *
     * @param int languageId Language id.
     * @return A <code>ZMCategory</code> instance.
     */
    public function getCategory($languageId) {
        return $this->container->get('categoryService')->getCategoryForId($this->categoryId_, $languageId);
    }

}
