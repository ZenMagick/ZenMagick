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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * A single coupon.
 *
 * <p><strong>NOTE:</strong> Depending on the coupon type, not all values might
 * be set.</p>
 * <p>For example, gift vouchers do only have a <em>code</em> and <em>amount</em>.</p>
 *
 * @author DerManoMann
 * @ORM\Table(name="coupons",
 *  indexes={
 *      @ORM\Index(name="idx_active_type_zen", columns={"coupon_active", "coupon_type"}),
 *      @ORM\Index(name="idx_coupon_code_zen", columns={"coupon_code"}),
 *      @ORM\Index(name="idx_coupon_type_zen", columns={"coupon_type"}),
 *  })
 * @ORM\Entity
 */
class Coupon extends ZMObject {
    const BALANCE_SET = 'balance_set';
    const BALANCE_ADD = 'balance_add';
    const FLAG_APPROVED = 'Y';
    const FLAG_WAITING = 'N';
    const TYPPE_GV = 'G';
    const TYPPE_FIXED = 'F';
    const TYPPE_PERCENT = 'P';
    const TYPPE_SHIPPING = 'S';

    /**
     * @var integer $id
     *
     * @ORM\Column(name="coupon_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
   /**
     * @var string $type
     *
     * @ORM\Column(name="coupon_type", type="string", length=1, nullable=false)
     */
    private $type;
    /**
     * @var string $code
     *
     * @ORM\Column(name="coupon_code", type="string", length=32, unique=true, nullable=false)
     */
    private $code;
     /**
     * @var decimal $amount
     *
     * @ORM\Column(name="coupon_amount", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $amount;
    /**
     * @var decimal $minOrderAmount
     *
     * @ORM\Column(name="coupon_minimum_order", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $minOrderAmount;
    /**
     * @var datetime $startDate
     *
     * @ORM\Column(name="coupon_start_date", type="datetime", nullable=false)
     */
    private $startDate;
    /**
     * @var datetime $expiryDate
     *
     * @ORM\Column(name="coupon_expire_date", type="datetime", nullable=false)
     */
    private $expiryDate;
    /**
     * @var integer $usesPerCoupon
     *
     * @ORM\Column(name="uses_per_coupon", type="integer", nullable=false)
     */
    private $usesPerCoupon;
    /**
     * @var integer $usesPerUser
     *
     * @ORM\Column(name="uses_per_user", type="integer", nullable=false)
     */
    private $usesPerUser;
    /**
     * @var string $restrictToProducts
     *
     * @ORM\Column(name="restrict_to_products", type="string", length=255, nullable=true)
     */
    private $restrictToProducts;
    /**
     * @var string $restrictToCategories
     *
     * @ORM\Column(name="restrict_to_categories", type="string", length=255, nullable=true)
     */
    private $restrictToCategories;
    /**
     * @var text $restrictToCustomers
     *
     * @ORM\Column(name="restrict_to_customers", type="text", nullable=true)
     */
    private $restrictToCustomers;
    /**
     * @var string $active
     *
     * @ORM\Column(name="coupon_active", type="string", length=1, nullable=false)
     */
    private $active;
    /**
     * @var datetime $dateCreated
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    private $dateCreated;
    /**
     * @var datetime $dateModified
     *
     * @ORM\Column(name="date_modified", type="datetime", nullable=false)
     */
    private $dateModified;
    /**
     * @var integer $restrictToZone
     *
     * @ORM\Column(name="coupon_zone_restriction", type="integer", nullable=false)
     */
    private $restrictToZone;

    /**
     * @var object $translations
     * @ORM\OneToMany(targetEntity="CouponTranslations", mappedBy="coupon", cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * @deprecated $name and $description must still be supported since TableMapper won't hydrate our "sub" objects
     */
    private $name;
    private $description;


    /**
     * Create new instance
     *
     * @param int id The coupon id; default is <em>0</em>.
     * @param string code The coupon code; default is <em>''</em>.
     * @param string type The coupon type; default is <em>'F'</em>.
     */
    public function __construct($id=0, $code='', $type = self::TYPPE_FIXED) {
        parent::__construct();
        $this->setId($id);
        $this->code = $code;
        $this->type = $type;
        $this->active = 'Y';
        $this->minOrderAmount = 0;
        $this->startDate = '0001-01-01 00:00:00';
        $this->expiryDate = '0001-01-01 00:00:00';
        $this->usesPerCoupon = 1;
        $this->usesPerUser = 0;
        $this->restrictToZone = 0;
        $this->translations = new ArrayCollection();
    }


    /**
     * Get the coupon id.
     *
     * @return int $couponId The coupon id.
     */
    public function getId() { return $this->id; }

    // @todo deprecated doctrine backwards compatiblity
    public function getCouponId() { return $this->getId(); }

    /**
     * Get the coupon code.
     *
     * @return string $code The coupon code.
     */
    public function getCode() { return $this->code; }

    /**
     * Get the coupon type.
     *
     * @return string $type The coupon type.
     */
    public function getType() { return $this->type; }

    /**
     * Get the amount.
     *
     * @return float $amount The coupon amount.
     */
    public function getAmount() { return $this->amount; }

    /**
     * Get the coupon name.
     *
     * @return string The coupon name.
     */
    public function getName() { return $this->name; }

    /**
     * Get the coupon description.
     *
     * @return string The coupon description.
     */
    public function getDescription() { return $this->description; }

    /**
     * Get the coupon translations.
     *
     * @return object CouponTranslation
     */
    public function getTranslation($languageId = 1) {
        return $this->translations[$languageId];
    }

    /**
     * Get the minimum order amount.
     *
     * @return float $minOrderAmount The minimum order amount.
     */
    public function getMinOrderAmount() { return $this->minOrderAmount; }

    /**
     * Get the coupon start date.
     *
     * @return datetime $startDate The coupon start date.
     */
    public function getStartDate() { return $this->startDate; }

    /**
     * Get the coupon expiry date.
     *
     * @return datetime $expiryDate The coupon expiry date.
     */
    public function getExpiryDate() { return $this->expiryDate; }

    /**
     * Get the uses per coupon.
     *
     * @return int $usesPerCoupon The uses per coupon.
     */
    public function getUsesPerCoupon() { return $this->usesPerCoupon; }

    /**
     * Get the uses per coupon.
     *
     * @return int $usesPerUser The uses per coupon.
     */
    public function getUsesPerUser() { return $this->usesPerUser; }

    /**
     * Check if this coupon qualifies for free shipping.
     *
     * @return boolean <code>true</code> if this coupon qualifies for free shipping, <code>false</code> if not.
     */
    public function isFreeShipping() { return 'S' == $this->type; }

    /**
     * Check if this coupon is active.
     *
     * @return boolean $active <code>true</code> if this coupon is active.
     */
    public function isActive() { return 'Y' == $this->active; }

    /**
     * Check if this a fixed amount coupon.
     *
     * @return boolean <code>true</code> if this coupon has a fixed amount assigned, <code>false</code> if not.
     */
    public function isFixedAmount() { return 'F' == $this->type; }

    /**
     * Check if this a percentage amount coupon.
     *
     * @return boolean <code>true</code> if this coupon has a percentage amount assigned, <code>false</code> if not.
     */
    public function isPercentage() { return 'P' == $this->type; }

    /**
     * Get the date the coupon was created.
     *
     * @return datetime $dateCreated
     */
    public function getDateCreated() { return $this->dateCreated; }

    /**
     * Get the date the coupon was last modified.
     *
     * @return datetime $dateModified
     */
    public function getDateModified() { return $this->dateModified; }

    /**
     * Get coupon restrictions.
     *
     * @return array An array of <code>CouponRestriction</code> instances.
     */
    public function getRestrictions() {
        return $this->container->get('couponService')->getRestrictionsForCouponId($this->couponId);
    }

    /**
     * Set the coupon id.
     *
     * @param int $id The coupon id.
     */
    public function setId($id) { $this->id = $id; }

    // @todo deperecated doctrine backwards compatibility
    public function setCouponId($id) { $this->setId($id); }

    /**
     * Set the coupon code.
     *
     * @param string $code The coupon code.
     */
    public function setCode($code) { $this->code = $code; }

    /**
     * Set the coupon type.
     *
     * @param string $type The coupon type.
     */
    public function setType($type) { $this->type = $type; }

    /**
     * Set the amount.
     *
     * @param float $amount The coupon amount.
     */
    public function setAmount($amount) { $this->amount = $amount; }

    /**
     * Set the coupon name.
     *
     * @param string name The coupon name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set the coupon description.
     *
     * @param string description The coupon description.
     */
    public function setDescription($description) { $this->description = $description; }

    /**
     * Set the minimum order amount.
     *
     * @param float $amount The new minimum order amount.
     */
    public function setMinOrderAmount($amount) { $this->minOrderAmount = $amount; }

    /**
     * Set the coupon start date.
     *
     * @param string $date The coupon start date.
     */
    public function setStartDate($date) { $this->startDate = $date; }

    /**
     * Set the coupon expiry date.
     *
     * @param string $date The coupon expiry date.
     */
    public function setExpiryDate($date) { $this->expiryDate = $date; }

    /**
     * Set the uses per coupon.
     *
     * @param int $uses The uses per coupon.
     */
    public function setUsesPerCoupon($uses) { $this->usesPerCoupon = $uses; }

    /**
     * Set the uses per user.
     *
     * @param int $uses The uses per user.
     */
    public function setUsesPerUser($uses) { $this->usesPerUser = $uses; }

    /**
     * Set the active flag.
     *
     * @param string $active The new flag.
     */
    public function setActive($active) { $this->active = $active; }

    /**
     * Set the date the coupon was created
     *
     * @param datetime $dateCreated
     */
    public function setDateCreated($dateCreated) { $this->dateCreated = $dateCreated; }

    /**
     * Set the date the coupon was modified.
     * @param datetime $dateModified
     */
    public function setDateModified($dateModified) { $this->dateModified = $dateModified; }

    /**
     * Set the coupon translation.
     *
     * @return string The coupon description.
     */
    public function setTranslation($name, $description = '', $languageId = 1) {
        $this->translations[$languageId] = new CouponTranslations($this, $name, $description, $languageId);
    }

    /**
     * Get active
     *
     * @return string
     */
    public function getActive() {
        return $this->active;
    }

    /**
     * Add translations
     *
     * @param  ZenMagick\StoreBundle\Entity\Coupons\CouponTranslations $translations
     * @return Coupon
     */
    public function addTranslation(\ZenMagick\StoreBundle\Entity\Coupons\CouponTranslations $translations) {
        $this->translations[] = $translations;
        return $this;
    }

    /**
     * Remove translations
     *
     * @param ZenMagick\StoreBundle\Entity\Coupons\CouponTranslations $translations
     */
    public function removeTranslation(\ZenMagick\StoreBundle\Entity\Coupons\CouponTranslations $translations) {
        $this->translations->removeElement($translations);
    }

    /**
     * Get translations
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getTranslations() {
        return $this->translations;
    }

    /**
     * Set restrictToProducts
     *
     * @param  string $restrictToProducts
     * @return Coupon
     */
    public function setRestrictToProducts($restrictToProducts) {
        $this->restrictToProducts = $restrictToProducts;
        return $this;
    }

    /**
     * Get restrictToProducts
     *
     * @return string
     */
    public function getRestrictToProducts() {
        return $this->restrictToProducts;
    }

    /**
     * Set restrictToCategories
     *
     * @param  string $restrictToCategories
     * @return Coupon
     */
    public function setRestrictToCategories($restrictToCategories) {
        $this->restrictToCategories = $restrictToCategories;
        return $this;
    }

    /**
     * Get restrictToCategories
     *
     * @return string
     */
    public function getRestrictToCategories() {
        return $this->restrictToCategories;
    }

    /**
     * Set restrictToCustomers
     *
     * @param  string $restrictToCustomers
     * @return Coupon
     */
    public function setRestrictToCustomers($restrictToCustomers) {
        $this->restrictToCustomers = $restrictToCustomers;
        return $this;
    }

    /**
     * Get restrictToCustomers
     *
     * @return string
     */
    public function getRestrictToCustomers() {
        return $this->restrictToCustomers;
    }

    /**
     * Set restrictToZone
     *
     * @param  integer $restrictToZone
     * @return Coupon
     */
    public function setRestrictToZone($restrictToZone) {
        $this->restrictToZone = $restrictToZone;
        return $this;
    }

    /**
     * Get restrictToZone
     *
     * @return integer
     */
    public function getRestrictToZone() {
        return $this->restrictToZone;
    }
}
