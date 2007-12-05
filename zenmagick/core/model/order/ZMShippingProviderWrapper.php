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
 * Shipping provider.
 *
 * <p>A shipping provider may offer 1-n shipping methods, depending on the
 * address, etc.</p>
 *
 * <p>This is eventually going to be a replacement for the current <code>ZMShippingProvider</code> class,
 * in combination with the new <code>ZMShippingProviders</code> service.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.model.order
 * @version $Id$
 */
class ZMShippingProviderWrapper extends ZMModel {
    var $zenModule_;
    var $methods_;
    var $taxRate_;
    var $errors_;


    /**
     * Create a new shipping provider.
     *
     * @param mixed zenModule A zen-cart shipping module instance.
     */
    function __construct($zenModule) {
        parent::__construct();

        $this->zenModule_ = $zenModule;
        $this->methods_ = null;
        $this->taxRate_ = null;
        $this->errors_ = array();
    }

    /**
     * Create a new shipping provider.
     *
     * @param mixed zenModule A zen-cart shipping module instance.
     */
    function ZMShippingProviderWrapper($zenModule) {
        $this->__construct($zenModule);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the shipping provider id.
     *
     * @return int The shipping provider id.
     */
    function getId() { return $this->zenModule_->code; }

    /**
     * Get the shipping provider name.
     *
     * @return string The shipping provider name.
     */
    function getName() { return $this->zenModule_->title; }

    /**
     * Get the shipping tax rate.
     *
     * @return float The shipping tax rate.
     */
    function &getTaxRate() {
        if (null == $this->ratRate_) {
            $this->taxRate_ = $this->create("TaxRate"); 
            $this->taxRate_->setRate(0);
        }

        return $this->taxRate_;
    }

    /**
     * Checks if an icon exists for this provider.
     *
     * @return boolean <code>true</code> if an icon, <code>false</code> if not.
     */
    function hasIcon() { return !zm_is_empty($this->zenModule_->icon); }

    /**
     * Get the icon.
     *
     * @return string The icon.
     */
    function getIcon() { return $this->hasIcon() ? $this->zenModule_->icon : null; }

    /**
     * Flags whether this shipping provider is enabled or not.
     *
     * @return boolean <code>true</code> if enabled, <code>false</code> if not.
     */
    function isEnabled() { return $this->zenModule_->enabled; }

    /**
     * Checks if errors are logged for this provider.
     *
     * @return boolean <code>true</code> if errors exist, <code>false</code> if not.
     */
    function hasError() { return false; }

    /**
     * Get the errors.
     *
     * @return array List of error messages.
     */
    function getError() { return $this->hasError() ?  $this->zenQuote_['error'] : null; }

    /**
     * Checks if shipping methods are available from this provider.
     *
     * @return boolean <code>true</code> if shipping methods exist, <code>false</code> if not.
     */
    function hasShippingMethods() { return 0 < count ($this->getShippingMethods()); }

    /**
     * Get the available shipping methods.
     *
     * @return array A list of <code>ZMShippingMethod</code> instances.
     */
    function getShippingMethods() { 
        $this->errors_ = array();

        if (!$this->isEnabled()) {
            return array();
        }

        if (null === $this->methods_) {
          $this->methods_ = array();

            // uses globals to access cart, order, etc...
            $quotes = $this->zenModule_->quote();

            // capture errors
            $this->errors_ = isset($quotes['errors']) ? $quotes['errors'] : $this->errors_;
            if (null == $this->taxRate_) {
                $this->taxRate_ = $this->create("TaxRate"); 
            }

            // capture tax
            $this->taxRate_->setRate(isset($quotes['tax']) ? $quotes['tax'] : 0);

            foreach ($quotes['methods'] as $method) {
                $shippingMethod = $this->create("ShippingMethod", $this, $method);
                $this->methods_[$shippingMethod->getId()] = $shippingMethod;
            }
        }

        return $this->methods_;
    }

}

?>
