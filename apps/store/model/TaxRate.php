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
namespace ZenMagick\apps\store\model;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Info for a single tax rate.
 *
 * <p>The tax rate id is build from the tax classId, countryId and zoneId to make it unique.</p>
 *
 * @author DerManoMann
 * @ORM\Table(name="tax_rates")
 * @ORM\Entity
 */
class TaxRate extends ZMObject {
    const TAX_BASE_STORE = 'Store';
    const TAX_BASE_SHIPPING = 'Shipping';
    const TAX_BASE_BILLING = 'Billing';

    /**
     * @var integer $taxRatesId
     *
     * @ORM\Column(name="tax_rates_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @var integer $classId
     *
     * @ORM\Column(name="tax_class_id", type="integer", nullable=false)
     */
    private $classId;
    private $countryId;
    /**
     * @var integer $zoneId
     *
     * @ORM\Column(name="tax_zone_id", type="integer", nullable=false)
     */
    private $zoneId;

    /**
     * @var integer $priority
     *
     * @ORM\Column(name="tax_priority", type="integer", nullable=true)
     */
    private $priority;

    /**
     * @var decimal $rate
     *
     * @ORM\Column(name="tax_rate", type="decimal", nullable=false)
     */
    private $rate;
    /**
     * @var string $description
     *
     * @ORM\Column(name="tax_description", type="string", length=255, nullable=false)
     */
    private $description;
    /**
     * @var datetime $lastModified
     *
     * @ORM\Column(name="last_modified", type="datetime", nullable=true)
     */
    private $lastModified;

    /**
     * @var datetime $dateAdded
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->id = null;
        $this->rate = 0.00;
        $this->description = null;
        $this->classId = 0;
        $this->countryId = 0;
        $this->zoneId = 0;
        $this->priority = 0;
        $this->setDateAdded(null);
        $this->setLastModified(null);
    }


    /**
     * Get the tax rate idendtifier
     *
     * @return integer $id The tax rate idendtifier.
     */
    public function getId() { return $this->id; }

    /**
     * Set the tax rate identifier
     *
     * @param integer $id The tax rate identifier.
     */
    public function setId($id) { $this->id = $id; }

    /**
     * Get the tax description.
     *
     * @return string $description The tax description.
     */
    public function getDescription() {
        if (null == $this->description) {
            $this->description = $this->container->get('taxService')->getTaxDescription($this->classId, $this->countryId, $this->zoneId);
        }
        return $this->description;
    }

    /**
     * Get the tax rate.
     *
     * @return float $rate The tax rate.
     */
    public function getRate() { return $this->rate; }

    /**
     * Set the tax description.
     *
     * @param string $description The tax description.
     */
    public function setDescription($description) { $this->description = $description; }

    /**
     * Set the tax rate.
     *
     * @param float $rate The tax rate.
     */
    public function setRate($rate) { $this->rate = round($rate, $this->container->get('settingsService')->get('calculationDecimals') + 2); }

    /**
     * Get the tax class id.
     *
     * @return integer  $classId The tax class id or <em>0</em>.
     */
    public function getClassId() { return $this->classId; }

    /**
     * Set the tax class id.
     *
     * @param integer $classId The tax class id.
     */
    public function setClassId($classId) { $this->classId = $classId; }

    /**
     * Get the country id.
     *
     * @return int The country id or <em>0</em>.
     */
    public function getCountryId() { return $this->countryId; }

    /**
     * Set the country id.
     *
     * @param int countryId The country id.
     */
    public function setCountryId($countryId) { $this->countryId = $countryId; }

    /**
     * Get the zone id.
     *
     * @return integer  $zoneId The zone id or <em>0</em>.
     */
    public function getZoneId() { return $this->zoneId; }

    /**
     * Set the zone id.
     *
     * @param integer $zoneId The zone id.
     */
    public function setZoneId($zoneId) { $this->zoneId = $zoneId; }

    /**
     * Get tax priority.
     *
     * @return integer $priority
     */
    public function getPriority() { return $this->priority; }

    /**
     * Set tax priority.
     *
     * @param integer $priority
     */
    public function setPriority($priority) { $this->priority = $priority; }

    /**
     * Get dateAdded
     *
     * @return datetime $dateAdded
     */
    public function getDateAdded() { return $this->dateAdded; }

    /**
     * Set dateAdded
     *
     * @param datetime $dateAdded
     */
    public function setDateAdded($dateAdded) { $this->dateAdded = $dateAdded; }

    /**
     * Get lastModified
     *
     * @author DerManoMann
     * @return datetime $lastModified
     */
    public function getLastModified() { return $this->lastModified; }

    /**
     * Set lastModified
     *
     * @author  DerManoMann
     * @param datetime $lastModified
     */
    public function setLastModified($lastModified) { $this->lastModified = $lastModified; }

    /**
     * Add tax to the given amount.
     *
     * @param double amount The amount.
     * @return double The amount incl. tax.
     */
    public function addTax($amount) {
        $currency = $this->getCurrency();
        if ($this->container->get('settingsService')->get('showPricesTaxIncluded') && 0 < $this->rate) {
            return round($amount + $this->getTaxAmount($amount), $currency->getDecimalPlaces());
        }

        return round($amount, $currency->getDecimalPlaces());
    }

    /**
     * Caclulate tax for the given amount.
     *
     * @param double amount The amount.
     * @return double The (non rounded) tax value.
     */
    public function getTaxAmount($amount) {
        $currency = $this->getCurrency();
        return $amount * $this->rate / 100;
    }

    /**
     * Get the best matching currency.
     *
     * @return Currency A currency.
     */
    protected function getCurrency() {
        $currencyService = $this->container->get('currencyService');
        //TODO: decouple price calculations from product, etc into a place where language/currency/etc are provided in a sane way!
        $session = Runtime::getContainer()->get('session');
        $currency = $currencyService->getCurrencyForCode($session->getCurrencyCode());
        if (null == $currency) {
            Runtime::getLogging()->warn('no currency found - using default currency');
            $currency = $currencyService->getCurrencyForCode($this->container->get('settingsService')->get('defaultCurrency'));
        }

        return $currency;
    }
}
