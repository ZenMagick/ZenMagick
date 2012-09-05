<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\plugins\paymentSurcharge;

use ZenMagick\Base\Plugins\Plugin;
use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\apps\store\Model\Checkout\ShoppingCart;

/**
 * ZenMagick order total module to add a payment surcharge totals based on configurable conditions.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class PaymentSurchargePlugin extends Plugin implements \ZMOrderTotal {

    /**
     * {@inheritDoc}
     */
    public function calculate($request, ShoppingCart $shoppingCart) {
        $paymentType = $shoppingCart->getSelectedPaymentType();

        // iterate over all conditions
        $output = array();
        foreach ($this->container->get('settingsService')->get('plugins.paymentSurcharge.conditions', array()) as $condition) {
            if ($paymentType->getId() == $condition['code'] || null === $condition['code']) {
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
                        $cvalue = null;
                        foreach ($cvalueNames as $name) {
                            if (isset($payment->$name)) {
                                $cvalue = $payment->$name;
                            } else {
                                $cvalue = $request->getParameter($name, null);
                            }
                            if (null !== $cvalue) {
                                break;
                            }
                        }
                        break;
                    default:
                        Runtime::getLogging()->error('invalid condition value type: ' . $cvalueType);
                        return null;
                    }
                }

                // check eregexp
                if ((null == $condition['cvalue'] && null == $condition['regexp']) || ereg($condition['regexp'], $cvalue)) {
                    // match, so apply condition

                    // evaluate the condition's value
                    $amount = 0;
                    if (is_numeric($condition['value'])) {
                        $amount = (float)$condition['value'];
                    }
                    if (0 === strpos($condition['value'], '%:')) {
                        $amount = trim(str_replace('%:', '', $condition['value']));
                        $amount =  $shoppingCart->getSubtotal() * ($amount/100);
                    }

                    $details = Beans::getBean('ZMOrderTotalLineDetails');
                    $details->setTitle($condition['title']);
                    $details->setAmount($amount);
                    $details->setDisplayValue($amount);
                    $output[] = $details;
                }
            }
        }

        return $output;
    }

}
