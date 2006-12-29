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
 * Shipping provider.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
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

    // create new instance
    function __construct($zenQuote) {
        $this->ZMShippingProvider($zenQuote);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    function getId() { return $this->zenQuote_['id']; }
    function getName() { return $this->zenQuote_['module']; }
    function getTaxRate() { return isset($this->zenQuote_['tax']) ? $this->zenQuote_['tax'] : 0; }
    function hasIcon() { return isset($this->zenQuote_['icon']); }
    function getIcon() { return $this->hasIcon() ? $this->zenQuote_['icon'] : null; }
    function hasError() { return isset($this->zenQuote_['error']); }
    function getError() { return $this->hasError() ?  $this->zenQuote_['error'] : null; }

    function hasShippingMethods() { return 0 < count ($this->methods_); }
    function getShippingMethods() { return $this->methods_; }

}

?>
