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

use ZenMagick\base\ZMObject;

/**
 * Order total line details as returned by a plugin implementing the <code>ZMOrderTotal</code>
 * interface.
 *
 * <p>The <em>title</em> and <em>amount</em> are the minimum requirements for an order
 * total. Other available properties are optional.</p>
 *
 * <p>Order totals are not supposed to calculate tax on their own. Instead, each calculated detail includes a
 * corresponding tax class id. That way tax can be calculated later on the subtotals for each tax class to avoid
 * rounding errors. The <em>tax</em>, <em>subtotal</em> and <em>total</em> order totals are responsible for
 * handling all of that.</p>
 *
 * <p>The default value for the tax class id is <em>0</em> for no tax.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.checkout
 */
class ZMOrderTotalLineDetails extends ZMObject {
    private $title_;
    private $amount_;
    private $value_;
    private $taxClassId_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->title_ = '';
        $this->amount_ = 0;
        $this->value_ = 0;
        $this->taxClassId_ = 0;
    }


    /**
     * Get the title.
     *
     * @return string The title.
     */
    public function getTitle() { return $this->title_; }

    /**
     * Set the title.
     *
     * <p>The title is the text to disapppear in the order total display.</p>
     *
     * @param string title The title.
     */
    public function setTitle($title) { $this->title_ = $title; }

    /**
     * Get the amount.
     *
     * <p>The amount to be used for subtotal/total calculation. This may be different from <code>value</code>.</p>
     *
     * @return float The amount.
     */
    public function getAmount() { return $this->amount_; }

    /**
     * Set the amount.
     *
     * @param float amount The amount.
     */
    public function setAmount($amount) { $this->amount_ = $amount; }

    /**
     * Get the display value.
     *
     * <p>The money/value to be displayed. This will be different from the amount in the case of order totals that
     * are purely for display. Examples are subtotal and total. In those cases the <code>amount</code> will be <em<0</em>,
     * while the <code>value</code> will the some calculated money amount.</p>
     *
     * @return float The value.
     */
    public function getDisplayValue() { return $this->value_; }

    /**
     * Set the display value.
     *
     * @param float value The value.
     */
    public function setDisplayValue($value) { $this->value_ = $value; }

    /**
     * Get the tax class id.
     *
     * @return int The tax class id.
     */
    public function getTaxClassId() { return $this->taxClassId_; }

    /**
     * Set the tax class id.
     *
     * @param int taxClassId The tax class id.
     */
    public function setTaxClassId($taxClassId) { $this->taxClassId_ = $taxClassId; }

}
