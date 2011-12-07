<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Coupon Translations
 *
 * @ORM\Entity
 * @ORM\Table(name="coupons_description")
 */
class ZMCouponTranslations extends ZMObject {
    /**
     * @var object $coupon
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ZMCoupon", inversedBy="translations")
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

    public function __construct($coupon, $name = '', $description = '', $languageId = 1)
    {
        $this->coupon = $coupon;
        $this->setLanguageId($languageId);
        $this->setName($name);
        $this->setDescription($description);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
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
}
