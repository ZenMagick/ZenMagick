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
 * @author mano
 * @package net.radebatz.zenmagick.model.order
 * @version $Id$
 */
class ZMShippingProvider extends ZMModel {
    var $zenQuote_;
    var $methods_;


    /**
     * Create a new shipping provider.
     *
     * @param array zenQuote The zen-cart shipping quote infos for this provider.
     */
    function ZMShippingProvider($zenQuote) {
        parent::__construct();

        $this->zenQuote_ = $zenQuote;
        $this->methods_ = array();
        foreach ($this->zenQuote_['methods'] as $method) {
            $method = $this->create("ShippingMethod", $this, $method);
            $this->methods_[$method->getId()] = $method;
        }
    }

    /**
     * Create a new shipping provider.
     *
     * @param array zenQuote The zen-cart shipping quote infos for this provider.
     */
    function __construct($zenQuote) {
        $this->ZMShippingProvider($zenQuote);
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
    function getId() { return $this->zenQuote_['id']; }

    /**
     * Get the shipping provider name.
     *
     * @return string The shipping provider name.
     */
    function getName() { return $this->zenQuote_['module']; }

    /**
     * Get the shipping tax rate.
     *
     * @return float The shipping tax rate.
     */
    function getTaxRate() { return isset($this->zenQuote_['tax']) ? $this->zenQuote_['tax'] : 0; }

    /**
     * Checks if an icon exists for this provider.
     *
     * @return bool <code>true</code> if an icon, <code>false</code> if not.
     */
    function hasIcon() { return isset($this->zenQuote_['icon']); }

    /**
     * Get the icon.
     *
     * @return string The icon.
     */
    function getIcon() { return $this->hasIcon() ? $this->zenQuote_['icon'] : null; }

    /**
     * Checks if errors are logged for this provider.
     *
     * @return bool <code>true</code> if errors exist, <code>false</code> if not.
     */
    function hasError() { return isset($this->zenQuote_['error']); }

    /**
     * Get the errors.
     *
     * @return array List of error messages.
     */
    function getError() { return $this->hasError() ?  $this->zenQuote_['error'] : null; }

    /**
     * Checks if shipping methods are available from this provider.
     *
     * @return bool <code>true</code> if shipping methods exist, <code>false</code> if not.
     */
    function hasShippingMethods() { return 0 < count ($this->methods_); }

    /**
     * Get the available shipping methods.
     *
     * @return array A list of <code>ZMShippingMethod</code> instances.
     */
    function getShippingMethods() { return $this->methods_; }

}

?>
