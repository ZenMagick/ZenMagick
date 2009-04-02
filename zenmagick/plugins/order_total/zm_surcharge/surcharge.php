<?php
/*
 * mixedmatter extensions for zen-cart
 * Copyright (C) 2007-2009 mixedmatter.co.nz
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
     * code: Either the code of a payment module or null
     * cvalue: The fieldname (or ';' separated list) of the payment module to evaluate; may be prefixed with 'field:'
     * regexp: The regular expression to evalue the field value
     * value: The value; numeric value; if prefixed with '%:' a percent value will be calculated, otherwise the amount taken as is
     * title: The display title
     */
    $conditions_ = array(
        array('code' => 'eway', 'cvalue' => 'field:cc_card_number;cc_number', 'regexp' => '^3[47][0-9]{13}$', 'value' => '%:3', 'title' => 'AMEX Surcharge'),
    );


    /**
     * Evaluate.
     */
    global $order, $currencies, $payment_modules;

        $payment = $GLOBALS[$payment_modules->selected_module];

        // iterate over all conditions
        foreach ($this->conditions_ as $condition) {
            if ($payment->code == $condition['code'] || null === $condition['code']) {
                // payment module match
                if (null != $condition['cvalue']) {
                    $cvalueToken = explode(':', $condition['cvalue']);
                    if (2 == count($cvalueToken)) {
                        $cvalueType = $cvalueToken[0];
                        $cvalueName = $cvalueToken[1];
                    } else {
                        $cvalueType = 'field';
                        $cvalueName = $cvalueToken[0];
                    }

                    // evaluate the value to use with the regexp
                    $cvalueNames = explode(';', $cvalueName);
                    switch ($cvalueType) {
                    case 'field':
                        foreach ($cvalueNames as $name) {
                            if (isset($payment->$name)) {
                                $cvalue = $payment->$name;
                            } else if (isset($_POST[$name])) {
                                $cvalue = $_POST[$name];
                            }
                            if (isset($cvalue)) {
                                break;
                            }
                        }
                        break;
                    default:
                        die('invalid condition value type: ' . $cvalueType);
                        break;
                    }
                }

                // check eregexp
                if ((null == $condition['cvalue'] && null == $condition['regexp']) || ereg($condition['regexp'], $cvalue)) {
                    // match
                    // apply condition
                    $value = $this->_getValueAmount($condition['value']);
                    $order->info['total'] += $value;
                    $this->output[] = array('title' => $condition['title'],
                                            'text' => $currencies->format($value, true, $order->info['currency'], $order->info['currency_value']),
                                            'value' => $value);
                }
            }
        }
    }

    /**
     * Evaluate value for a given value setting.
     *
     * @param string value The value string.
     * @return float The amount.
     */
    function _getValueAmount($value) {
    global $order;

        if (is_numeric($value)) {
            return (float)$value;
        }

        if (0 === strpos($value, '%:')) {
            $value = trim(str_replace('%:', '', $value));
            return ($order->info['subtotal'] * ($value/100));
        }

        return 0;
    }

?>
