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
namespace zenmagick\apps\store\entities\locale;

use zenmagick\base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * A single currency.
 *
 * @author DerManoMann
 * @ORM\Table(name="currencies")
 * @ORM\Entity
 */
class Currency extends ZMObject {
    /**
     * @var integer $currencyId
     *
     * @ORM\Column(name="currencies_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $currencyId;
    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=3, nullable=false)
     */
    private $code;
    /**
     * @var string $name
     *
     * @ORM\Column(name="title", type="string", length=32, nullable=false)
     */
    private $name;
    /**
     * @var string $symbolLeft
     *
     * @ORM\Column(name="symbol_left", type="string", length=24, nullable=true)
     */
    private $symbolLeft;
    /**
     * @var string $symbolRight
     *
     * @ORM\Column(name="symbol_right", type="string", length=24, nullable=true)
     */
    private $symbolRight;
    /**
     * @var string $decimalPoint
     *
     * @ORM\Column(name="decimal_point", type="string", length=1, nullable=true)
     */
    private $decimalPoint;
    /**
     * @var string $thousandsPoint
     *
     * @ORM\Column(name="thousands_point", type="string", length=1, nullable=true)
     */
    private $thousandsPoint;
    /**
     * @var string $decimalPlaces
     *
     * @ORM\Column(name="decimal_places", type="string", length=1, nullable=true)
     */
    private $decimalPlaces;
    /**
     * @var float $rate
     *
     * @ORM\Column(name="value", type="float", nullable=true)
     */
    private $rate;
    /**
     * @var datetime $lastUpdate
     *
     * @ORM\Column(name="last_updated", type="datetime", nullable=true)
     */
    private $lastUpdate;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();

        $this->setId(0);
        $this->code = null;
        $this->name = null;
        $this->decimalPlaces = 2;
        $this->thousandsPoint = null;
        $this->rate = 1;
    }


    /**
     * Get the currency id.
     *
     * @return integer $currencyId The currency id.
     */
    public function getId() { return $this->currencyId; }

    /**
     * Get the currency code.
     *
     * @return string $code The currency code.
     */
    public function getCode() { return $this->code; }

    /**
     * Get the currency name.
     *
     * @return string $name The currency name.
     */
    public function getName() { return $this->name; }

    /**
     * Get the currency symbox (left).
     *
     * @return string The currency symbol (left).
     */
    public function getSymbolLeft() { return $this->symbolLeft; }

    /**
     * Get the currency symbox (right).
     *
     * @return string The currency symbol (right).
     */
    public function getSymbolRight() { return $this->symbolRight; }

    /**
     * Get the currency decimal point.
     *
     * @return string The currency decimal point.
     */
    public function getDecimalPoint() { return $this->decimalPoint; }

    /**
     * Get the currency thousands point.
     *
     * @return string The currency thousands point.
     */
    public function getThousandsPoint() { return $this->thousandsPoint; }

    /**
     * Get the currency decimal places.
     *
     * @return int The currency decimal places.
     */
    public function getDecimalPlaces() { return $this->decimalPlaces; }

    /**
     * Get the currency rate.
     *
     * <p>This is the rate in relation to the default currency.</p>
     *
     * @return double The currency rate.
     */
    public function getRate() { return $this->rate; }

    /**
     * Get date of last currency rate update
     *
     * @author DerManoMann
     * @return datetime $lastUpdate
     */
    public function getLastUpdate() { return $this->lastUpdate; }

    /**
     * Set the currency id.
     *
     * @param int id The currency id.
     */
    public function setId($id) { $this->currencyId = $id; }

    /**
     * Set the currency code.
     *
     * @param string $code The currency code.
     */
    public function setCode($code) { $this->code = $code; }

    /**
     * Set the currency name.
     *
     * @param string $name The currency name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set the currency symbox (left).
     *
     * @param string $symbol The currency symbol (left).
     */
    public function setSymbolLeft($symbol) { $this->symbolLeft = $symbol; }

    /**
     * Set the currency symbox (right).
     *
     * @param string symbol The currency symbol (right).
     */
    public function setSymbolRight($symbol) { return $this->symbolRight = $symbol; }

    /**
     * Set the currency decimal point.
     *
     * @param string $point The currency decimal point.
     */
    public function setDecimalPoint($point) { $this->decimalPoint = $point;}

    /**
     * Set the currency thousands point.
     *
     * @param string $point The currency thousands point.
     */
    public function setThousandsPoint($point) { $this->thousandsPoint = $point; }

    /**
     * Set the currency decimal places.
     *
     * @param int $decimals The currency decimal places.
     */
    public function setDecimalPlaces($decimals) { $this->decimalPlaces = $decimals; }

    /**
     * Set the currency rate.
     *
     * <p>This is the rate in relation to the default currency.</p>
     *
     * @param double $rate The currency rate.
     */
    public function setRate($rate) { $this->rate = $rate; }

    /**
     * Set lastUpdate
     *
     * @param datetime $lastUpdate
     */
    public function setLastUpdate($lastUpdate) { $this->lastUpdate = $lastUpdate; }

    /**
     * Format the given amount according to this currency's rate and formatting rules.
     *
     * @param float amount The amount.
     * @param boolean convert If <code>true</code>, consider <code>$amount</code> to be in default currency and
     *  convert before formatting.
     * @return string The formatted amount.
     */
    public function format($amount, $convert=true) {
        $ratedValue = $convert ? $this->convertTo($amount) : $amount;
        if ($isNegative = 0 > $ratedValue) {
            $ratedValue *= -1;
        }
        $formattedAmount = number_format($ratedValue, $this->decimalPlaces, $this->decimalPoint, $this->thousandsPoint);
        return ($isNegative ? '-' : '').$this->symbolLeft .  $formattedAmount . $this->symbolRight;
    }

    /**
     * Convert from default currency into this currency.
     *
     * @param float amount The amount in the default currency.
     * @return float The converted amount.
     */
    public function convertTo($amount) {
        return round($amount * $this->rate, $this->decimalPlaces);
    }

    /**
     * Convert from this currency into default currency.
     *
     * @param float amount The amount in this currency.
     * @return float The converted amount.
     */
    public function convertFrom($amount) {
        return round($amount * (1/$this->rate), $this->decimalPlaces);
    }

    /**
     * Parse a formatted currency amount.
     *
     * @param string value The formatted currency value.
     * @return float The amount.
     */
    public function parse($value) {
        $value = preg_replace('/[^0-9\\'.$this->decimalPoint.']/', '', $value);
        $value = str_replace($this->decimalPoint, '.', $value);

        if (0 != preg_match('[^0-9\.]', $value)) {
            return null;
        }

        return (float)$value;
    }
}
