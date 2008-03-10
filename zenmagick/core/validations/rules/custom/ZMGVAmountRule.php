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
 * Validate the amount against the current account's gv balance.
 *
 * <p><strong>NOTE:</strong> The amount is expected to be of type float and must not
 * contains any currency formatting.</p>
 *
 * @author mano
 * @package org.zenmagick.validations.rules.custom
 * @version $Id$
 */
class ZMGVAmountRule extends ZMRule {


    /**
     * Create new required rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    function ZMGVAmountRule($name, $msg=null) {
        parent::__construct($name, "Invalid Gift Certificate value.", $msg);
    }

    /**
     * Create new required rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    function __construct($name, $msg=null) {
        $this->ZMGVAmountRule($name, $msg);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Validate the given request data.
     *
     * @param array req The request data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    function validate($req) {
    global $zm_request;

        if (empty($req[$this->name_])) {
            return true;
        }

        $amount = $req[$this->name_];

        $account = ZMRequest::getAccount();
        $balance = $account->getVoucherBalance();

        $currentCurrencyCode = ZMRequest::getCurrencyCode();
        if (zm_setting('defaultCurrency') != $currentCurrencyCode) {
            // need to convert amount to default currency as GV values are in default currency
            $currency = ZMCurrencies::instance()->getCurrencyForCode($currentCurrencyCode);
            $amount = $currency->convertFrom($amount);
        }

        if (0 == $amount || $amount > $balance) {
            return false;
        }

        return true;
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    function toJSString() {
        return '';
    }

}

?>
