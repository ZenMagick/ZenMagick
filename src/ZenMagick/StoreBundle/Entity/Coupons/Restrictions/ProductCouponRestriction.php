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
 * Single coupon restriction.
 *
 * @author DerManoMann
 */
class ProductCouponRestriction extends ZMObject
{
    private $allowed;
    private $productId;

    /**
     * Create new coupon restriction.
     *
     * @param boolean allowed The allowed flag.
     * @param int productId The product id this restriction applies to.
     */
    public function __construct($allowed, $productId)
    {
        parent::__construct();
        $this->allowed = $allowed;
        $this->productId = $productId;
    }

    /**
     * Checks if this coupon restriction is allowed.
     *
     * @return boolean <code>true</code> if this coupon restriction is allowed, <code>false</code> if not.
     */
    public function isAllowed()
    {
        return $this->allowed;
    }

    /**
     * Returns the product.
     *
     * @param int languageId Language id.
     * @return ZenMagick\StoreBundle\Entity\Product A <code>Product</code> instance.
     */
    public function getProduct($languageId)
    {
        return $this->container->get('productService')->getProductForId($this->productId, $languageId);
    }

}
