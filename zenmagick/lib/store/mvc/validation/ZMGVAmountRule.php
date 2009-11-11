<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.validation
 * @version $Id: ZMGVAmountRule.php 1966 2009-02-14 10:52:50Z dermanomann $
 */
class ZMGVAmountRule extends ZMRule {

    /**
     * Create new required rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    function __construct($name, $msg=null) {
        parent::__construct($name, "Invalid Gift Certificate value.", $msg);
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
     * @param ZMRequest request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    public function validate($request, $data) {
        if (empty($data[$this->getName()])) {
            return true;
        }

        $amount = $data[$this->getName()];

        $account = ZMRequest::instance()->getAccount();
        $balance = $account->getVoucherBalance();

        $currentCurrencyCode = ZMRequest::instance()->getCurrencyCode();
        if (ZMSettings::get('defaultCurrency') != $currentCurrencyCode) {
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
    public function toJSString() {
        return '';
    }

}

?>
