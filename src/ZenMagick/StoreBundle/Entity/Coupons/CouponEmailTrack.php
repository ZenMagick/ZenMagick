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
 * @ORM\Table(name="coupon_email_track",
 *  indexes={
 *      @ORM\Index(name="idx_coupon_id_zen", columns={"coupon_id"}),
 *  })
 * @ORM\Entity
 */
class CouponEmailTrack
{
    /**
     * @var integer $couponEmailId
     *
     * @ORM\Column(name="unique_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $couponEmailId;

    /**
     * @var integer $couponId
     *
     * @ORM\Column(name="coupon_id", type="integer", nullable=false)
     */
    private $couponId;

    /**
     * @var integer $accountId
     *
     * @ORM\Column(name="customer_id_sent", type="integer", nullable=false)
     */
    private $accountId;

    /**
     * @var string $firstName
     *
     * @ORM\Column(name="sent_firstname", type="string", length=32, nullable=true)
     */
    private $firstName;

    /**
     * @var string $lastName
     *
     * @ORM\Column(name="sent_lastname", type="string", length=32, nullable=true)
     */
    private $lastName;

    /**
     * @var string $emailTo
     *
     * @ORM\Column(name="emailed_to", type="string", length=32, nullable=true)
     */
    private $emailTo;

    /**
     * @var \DateTime $dateSent
     *
     * @ORM\Column(name="date_sent", type="datetime", nullable=false)
     */
    private $dateSent;

}
