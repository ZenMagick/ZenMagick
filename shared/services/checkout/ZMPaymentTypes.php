<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Payments.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.checkout
 */
class ZMPaymentTypes extends ZMObject {
    private $paymentTypes_;


    /**
     * Create new instance.
     */
    public function __construct() {
        $this->paymentTypes_ = null;
    }


    /**
     * Get instance.
     */
    public static function instance() {
        return Runtime::getContainer()->get('paymentTypeService');
    }


    /**
     * Get all (available) payment types.
     *
     * @param boolean all Optional flag to return all installed; default is <code>false</code> to return enabled only.
     * @return array List of <code>ZMPaymentType</code> instances.
     */
    public function getPaymentTypes($all=false) {
        if (null === $this->paymentTypes_) {
            $this->paymentTypes_ = array();
            if (defined('MODULE_PAYMENT_INSTALLED') && !ZMLangUtils::isEmpty(MODULE_PAYMENT_INSTALLED)) {
                // get a list of modules and stuff
                $moduleInfos = array();
                foreach (explode(';', MODULE_PAYMENT_INSTALLED) as $filename) {
                    $path = ZC_INSTALL_PATH.'includes/modules/payment/'.$filename;
                    if (file_exists($path)) {
                        $class = substr($filename, 0, strrpos($filename, '.'));
                        $moduleInfos[] = array('class' => $class, 'filename' => $filename, 'path' => $path);
                    }
                }

                foreach ($moduleInfos as $info) {
                    if (isset($GLOBALS[$info['class']])) {
                        $module = $GLOBALS[$info['class']];
                        if ($all || $module->enabled) {
                            $wrapper = Runtime::getContainer()->get('zenmagick\apps\store\bundles\ZenCartBundle\wrapper\PaymentTypeWrapper');
                            $wrapper->setModule($module);
                            $this->paymentTypes_[$module->code] = $wrapper;
                        }
                        continue;
                    }

                    $lang_file = ZC_INSTALL_PATH.'includes/languages/'.$_SESSION['language'].'/modules/payment/'.$info['filename'];
                    if (@file_exists($lang_file)) {
                        include_once $lang_file;
                    }
                    include_once $info['path'];
                    $module = new $info['class'];
                    $module->update_status();
                    if ($all || $module->enabled) {
                        $wrapper = Runtime::getContainer()->get('zenmagick\apps\store\bundles\ZenCartBundle\wrapper\PaymentTypeWrapper');
                        $wrapper->setModule($module);
                        $this->paymentTypes_[$module->code] = $wrapper;
                    }
                }
            }
        }

        return $this->paymentTypes_;
    }

    /**
     * Get the payment type for the give id.
     *
     * @param string id The payment type id.
     * @return ZMPaymentType A <code>ZMPaymentType</code> instance or <code>null</code>.
     */
    public function getPaymentTypeForId($id) {
        $paymentTypes = $this->getPaymentTypes();
        return array_key_exists($id, $paymentTypes) ? $paymentTypes[$id] : null;
    }

    /**
     * Generate the JavaScript for the payment form validation.
     *
     * <p>This method is only defined in <em>storefront</em> context.</p>
     *
     * @param ZMRequest request The current request.
     * @return string Fully formatted JavaScript incl. of wrapping &lt;script&gt; tag.
     */
    public function getPaymentFormValidationJS($request) {
        $shoppingCart = $request->getShoppingCart();
        $paymentTypes = $shoppingCart->getPaymentTypes();
        $js = '';
        if (0 < count($paymentTypes)) {
            // translatable text accepts a lf/cr as first parameter
            $js = '<script type="text/javascript">' . "\n" .
            'function check_form() {' . "\n" .
            '  var error = 0;' . "\n" .
            '  var error_message = "' . sprintf(_zm('Errors have occurred during the processing of your form.%1$s%1$sPlease make the following corrections:%1$s%1$s'), '\n') . '";' . "\n" .
            '  var payment_value = null;' . "\n" .
            '  if (document.checkout_payment.payment) {' . "\n" .
            '    if (document.checkout_payment.payment.length) {' . "\n" .
            '      for (var i=0; i<document.checkout_payment.payment.length; i++) {' . "\n" .
            '        if (document.checkout_payment.payment[i].checked) {' . "\n" .
            '          payment_value = document.checkout_payment.payment[i].value;' . "\n" .
            '        }' . "\n" .
            '      }' . "\n" .
            '    } else if (document.checkout_payment.payment.checked) {' . "\n" .
            '      payment_value = document.checkout_payment.payment.value;' . "\n" .
            '    } else if (document.checkout_payment.payment.value) {' . "\n" .
            '      payment_value = document.checkout_payment.payment.value;' . "\n" .
            '    }' . "\n" .
            '  }' . "\n\n";

            foreach ($paymentTypes as $paymentType) {
                $js .= $paymentType->getFormValidationJS($request);
            }

            $js .= "\n" . '  if (payment_value == null && submitter != 1) {' . "\n" .
            '    error_message = error_message + "' . _zm('* Please select a payment method for your order.') . '";' . "\n" .
            '    error = 1;' . "\n" .
            '  }' . "\n\n" .
            '  if (error == 1 && submitter != 1) {' . "\n" .
            '    alert(error_message);' . "\n" .
            '    return false;' . "\n" .
            '  } else {' . "\n" .
            '    return true;' . "\n" .
            '  }' . "\n" .
            '}' . "\n" .
            '</script>' . "\n";
            $js = str_replace('document.checkout_payment', 'document.forms.checkout_payment', $js);
        }

        return $js;
    }

}
