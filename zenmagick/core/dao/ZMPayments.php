<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
class ZMPayments {
    var $zenModules_;


    // create new instance
    function ZMPayments() {
    global $payment_modules;
        if (!isset($payment_modules)) {
            require_once(DIR_WS_CLASSES . 'payment.php');
            $this->zenModules_ = new payment;
        } else {
            $this->zenModules_ = $payment_modules;
        }
    }

    // create new instance
    function __construct() {
        $this->ZMPayments();
    }

    function __destruct() {
    }


    function getZenModules() { return $this->zenModules_; }

    // get payment types
    function getPaymentTypes() {
        $zenTypes = $this->zenModules_->selection();
        $paymentTypes = array();
        foreach ($zenTypes as $zenType) {
            $paymentType = new ZMPaymentType($zenType['id'], $zenType['module']);
            if (isset($zenType['error'])) {
                $paymentType->error_ = $zenType['error'];
            }
            if (isset($zenType['fields'])) {
                foreach ($zenType['fields'] as $zenField) {
                    $paymentType->addField(new ZMPaymentField($zenField['title'], $zenField['field']));
                }
            }
            array_push($paymentTypes, $paymentType);
            //echo "<pre>"; print_r($zenType); echo "</pre>";
        }

        return $paymentTypes;
    }

    // JS validation code as provided by the payment modules
    function getPaymentsJavaScript($echo=true) {
        $js = $this->zenModules_->javascript_validation();

        if ($echo) echo $js;
        return $js;
    }


    // get selected payment type
    function getSelectedPaymentType() {
        $zenModule = $GLOBALS[$this->zenModules_->selected_module];
        $confirmation = $zenModule->confirmation();

        $paymentType = new ZMPaymentType($zenModule->code, $zenModule->title);
        if (is_array($confirmation) && array_key_exists('fields', $confirmation)) {
            foreach ($confirmation['fields'] as $zenField) {
                $paymentType->addField(new ZMPaymentField($zenField['title'], $zenField['field']));
            }
        }

        return $paymentType;
    }

}

?>
