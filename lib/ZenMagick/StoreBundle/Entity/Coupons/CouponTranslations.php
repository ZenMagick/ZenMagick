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

use ZenMagick\Base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Coupon Translations
 *
 * @ORM\Entity
 * @ORM\Table(name="coupons_description")
 */
class CouponTranslations extends ZMObject {
    /**
     * @var object $coupon
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Coupon", inversedBy="translations")
     * @ORM\JoinColumn(name="coupon_id", referencedColumnName="coupon_id")
     */
    private $coupon;

    /**
     * @var integer $languageId
     * @ORM\Column(name="language_id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $languageId;

    /**
     * @var string $name
     * @ORM\Column(name="coupon_name", type="string", length=32, nullable=false)
     */
    private $name;

    /**
     * @var text $description
     * @ORM\Column(name="coupon_description", type="text", nullable=true)
     */
    private $description;

    /**
     * Create new instance
     *
     * @todo: languageId default???
     */
    public function __construct($coupon, $name='', $description='', $languageId=1) {
        parent::__construct();
        $this->coupon = $coupon;
        $this->setLanguageId($languageId);
        $this->setName($name);
        $this->setDescription($description);
    }

    /**
     * Get couponId
     *
     * @return integer $couponId
     */
    public function getCouponId() { return $this->coupon->getId(); }

    /**
     * Get languageId
     *
     * @return integer $languageId
     */
    public function getLanguageId() { return $this->languageId; }

    /**
     * Get coupon name
     *
     * @return string $name
     */
    public function getName() { return $this->name; }

    /**
     * Get coupon description
     *
     * @return text $description
     */
    public function getDescription() { return $this->description; }

    /**
     * Set name of the coupon
     *
     * @param string $name
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set languageId of the coupon
     *
     * @param integer $languageId
     */
    public function setLanguageId($languageId) { $this->languageId = $languageId; }

    /**
     * Set description of the coupon
     *
     * @param text $description
     */
    public function setDescription($description) { $this->description = $description; }

    /**
     * Set coupon
     *
     * @param ZenMagick\StoreBundle\Entity\Coupons\Coupon $coupon
     * @return CouponTranslations
     */
    public function setCoupon(\ZenMagick\StoreBundle\Entity\Coupons\Coupon $coupon)
    {
        $this->coupon = $coupon;
        return $this;
    }

    /**
     * Get coupon
     *
     * @return ZenMagick\StoreBundle\Entity\Coupons\Coupon
     */
    public function getCoupon()
    {
        return $this->coupon;
    }
}
