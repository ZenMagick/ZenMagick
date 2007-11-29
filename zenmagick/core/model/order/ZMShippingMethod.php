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
 * Shipping method.
 *
 * @author mano
 * @package org.zenmagick.model.order
 * @version $Id$
 */
class ZMShippingMethod extends ZMModel {
    var $provider_;
    var $zenMethod_;


    /**
     * Create new shipping method.
     *
     * @param ZMShippingProvider provider The shipping provider for this method.
     * @param array zenMethod The zen-cart method infos.
     */
    function ZMShippingMethod($provider, $zenMethod) {
        parent::__construct();

        $this->provider_ = $provider;
        $this->zenMethod_ = $zenMethod;
    }

    /**
     * Create new shipping method.
     *
     * @param ZMShippingProvider provider The shipping provider for this method.
     * @param array zenMethod The zen-cart method infos.
     */
    function __construct($provider, $zenMethod) {
        $this->ZMShippingMethod($provider, $zenMethod);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the shipping method id.
     *
     * @return int The shipping method id.
     */
    function getId() { return $this->zenMethod_['id']; }

    /**
     * Get the shipping method name.
     *
     * @return string The shipping method name.
     */
    function getName() { return $this->zenMethod_['title']; }

    /**
     * Get the shipping cost.
     *
     * @return float The shipping cost.
     */
    function getCost() { $taxRate = $this->provider_->getTaxRate(); return $taxRate->addTax($this->zenMethod_['cost']); }

    /**
     * Get the shipping provider.
     *
     * @return ZMShippingProvider The shipping provider.
     */
    function getProvider() { return $this->provider_; }

    /**
     * Get the shipping id.
     *
     * @return string The shipping id as used by the shopping cart.
     */
    function getShippingId() {
        $id = $this->provider_->getId() . '_' . $this->getId();
        return $id;
    }

}

?>
