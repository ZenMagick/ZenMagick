<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Payments.
 *
 * @author mano
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMPayments extends ZMDao {
    var $zenModules_;


    /**
     * Default c'tor.
     */
    function ZMPayments() {
    global $payment_modules;

        parent::__construct();

        if (!isset($payment_modules)) {
            zm_resolve_zc_class('payment');
            $this->zenModules_ = new payment;
        } else {
            $this->zenModules_ = $payment_modules;
        }
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMPayments();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the zen-cart payment modules.
     */
    function getZenModules() { return $this->zenModules_; }

    /**
     * Get the available payment types.
     *
     * @return array List of <code>ZMPaymentType</code> instances.
     */
    function getPaymentTypes() {
        $zenTypes = $this->zenModules_->selection();
        $paymentTypes = array();
        foreach ($zenTypes as $zenType) {
            $paymentType =& $this->create("PaymentType", $zenType['id'], $zenType['module']);
            if (isset($zenType['error'])) {
                $paymentType->error_ = $zenType['error'];
            }
            if (isset($zenType['fields'])) {
                foreach ($zenType['fields'] as $zenField) {
                    $paymentType->addField($this->create("PaymentField", $zenField['title'], $zenField['field']));
                }
            }
            array_push($paymentTypes, $paymentType);
        }

        return $paymentTypes;
    }

    /**
     * Generate the JavaScript for the payment form validation.
     *
     * @param bool echo If <code>true</code>, echo the code.
     * @return string Fully formatted JavaScript incl. of wrapping &lt;script&gt; tag.
     */
    function getPaymentsJavaScript($echo=true) {
        $js = $this->zenModules_->javascript_validation();

        if ($echo) echo $js;
        return $js;
    }

    /**
     * Get the selected payment type.
     *
     * @return ZMPaymentType The payment type.
     */
    function getSelectedPaymentType() {
        $zenModule = $GLOBALS[$this->zenModules_->selected_module];
        if (!$zenModule) return null;
        $confirmation = $zenModule->confirmation();

        $paymentType =& $this->create("PaymentType", $zenModule->code, $zenModule->title);
        if (is_array($confirmation) && array_key_exists('fields', $confirmation)) {
            foreach ($confirmation['fields'] as $zenField) {
                $paymentType->addField($this->create("PaymentField", $zenField['title'], $zenField['field']));
            }
        }

        return $paymentType;
    }

}

?>
