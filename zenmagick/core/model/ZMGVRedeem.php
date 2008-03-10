<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Gift voucher redeem info.
 *
 * @author mano
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMGVRedeem extends ZMModel {
    var $code_;
    var $amount_;
    var $redeemed_;


    /**
     * Create new instance.
     */
    function ZMGVRedeem() {
        parent::__construct();

        $this->code_ = '';
        $this->amount_ = 0;
        $this->redeemed_ = false;
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->ZMGVRedeem();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Populate all available fields from the given request.
     *
     * @param array req A request; if <code>null</code>, use the current <code>ZMRequest</code> instead.
     */
    function populate($req=null) {
    global $zm_request;

        $this->code_ = ZMRequest::getParameter('couponCode', '');
    }


    /**
     * Get the coupon code.
     *
     * @return string The coupon code.
     */
    function getCode() { return $this->code_; }

    /**
     * Get the amount.
     *
     * @return float The amount.
     */
    function getAmount() { return $this->amount_; }

    /**
     * Check if the coupon was redeemed succsessfully.
     *
     * @return boolean <code>true</code> if the coupon was redeemed succsessfully, <code>false</code> if not.
     */
    function isRedeemed() { return $this->redeemed_; }

    /**
     * Set the coupon code.
     *
     * @param string code The coupon code.
     */
    function setCode($code) { $this->code_ = $code; }

    /**
     * Set the amount.
     *
     * @param float amount The amount.
     */
    function setAmount($amount) { $this->amount_ = $amount; }

    /**
     * Set the redeem flag.
     *
     * @param boolean redeemed <code>true</code> if the coupon was redeemed succsessfully, <code>false</code> if not.
     */
    function setRedeemed($redeemed) { $this->redeemed_ = $redeemed; }

}

?>
