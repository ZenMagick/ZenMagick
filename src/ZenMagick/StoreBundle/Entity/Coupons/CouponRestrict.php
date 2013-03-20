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

namespace ZenMagick\StoreBundle\Entity\Coupons;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="coupon_restrict",
 *  indexes={
 *      @ORM\Index(name="idx_coup_id_prod_id_zen", columns={"coupon_id", "product_id"}),
 *  })
 * @ORM\Entity
 */
class CouponRestrict
{
    /**
     * @var integer $couponRestrictionId
     *
     * @ORM\Column(name="restrict_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $couponRestrictionId;

    /**
     * @var integer $couponId
     *
     * @ORM\Column(name="coupon_id", type="integer", nullable=false)
     */
    private $couponId;

    /**
     * @var integer $productId
     *
     * @ORM\Column(name="product_id", type="integer", nullable=false)
     */
    private $productId;

    /**
     * @var integer $categoryId
     *
     * @ORM\Column(name="category_id", type="integer", nullable=false)
     */
    private $categoryId;

    /**
     * @var string $couponRestrict
     *
     * @ORM\Column(name="coupon_restrict", type="string", length=1, nullable=false)
     */
    private $couponRestrict;

}
