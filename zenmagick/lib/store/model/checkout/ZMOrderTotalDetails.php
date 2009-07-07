<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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


/**
 * Order total details as returned by an <code>ZMOrderTotalPlugin</code>.
 *
 * <p>The <em>title</em> and <em>amount</em> are the minimum requirements for an order
 * total. All other available properties are optional.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.model.checkout
 * @version $Id: ZMOrderTotalDetails.php 2133 2009-04-02 22:36:08Z dermanomann $
 */
class ZMOrderTotalDetails extends ZMObject {
    private $title_;
    private $amount_;
    private $tax_;
    private $subtotal_;


    /**
     * Create new instance.
     *
     * @param string title The title.
     * @param float amount The amount.
     */
    function __construct($title, $amount) {
        parent::__construct();
        $this->title_ = $title;
        $this->amount_ = $amount;
        $this->tax_ = 0;
        $this->subtotal_ = 0;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the title.
     *
     * @return string The title.
     */
    public function getTitle() { return $this->title_; }

    /**
     * Get the amount.
     *
     * @return float The amount.
     */
    public function getAmount() { return $this->amount_; }

    /**
     * Get the subtotal.
     *
     * @return float The subtotal.
     */
    public function getSubtotal() { return $this->subtotal_; }

    /**
     * Set the subtotal.
     *
     * @param float subtotal The subtotal.
     */
    public function setSubtotal($subtotal) { $this->subtotal_ = $subtotal; }

    /**
     * Get the tax.
     *
     * @return float The tax.
     */
    public function getTax() { return $this->tax_; }

    /**
     * Set the tax.
     *
     * @param float tax The tax.
     */
    public function setTax($tax) { $this->tax_ = $tax; }

}

?>
