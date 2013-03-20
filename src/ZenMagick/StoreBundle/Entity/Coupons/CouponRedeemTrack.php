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
 * @ORM\Table(name="coupon_redeem_track",
 *  indexes={
 *      @ORM\Index(name="idx_coupon_id_zen", columns={"coupon_id"}),
 *  })
 * @ORM\Entity
 */
class CouponRedeemTrack
{
    /**
     * @var integer $couponRedeemId
     *
     * @ORM\Column(name="unique_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $couponRedeemId;

    /**
     * @var integer $couponId
     *
     * @ORM\Column(name="coupon_id", type="integer", nullable=false)
     */
    private $couponId;

    /**
     * @var integer $accountId
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=false)
     */
    private $accountId;

    /**
     * @var \DateTime $redeemDate
     *
     * @ORM\Column(name="redeem_date", type="datetime", nullable=false)
     */
    private $redeemDate;

    /**
     * @var string $redeemIp
     *
     * @ORM\Column(name="redeem_ip", type="string", length=45, nullable=false)
     */
    private $redeemIp;

    /**
     * @var integer $orderId
     *
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     */
    private $orderId;

}
