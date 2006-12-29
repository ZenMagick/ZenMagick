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
 * Shipping method.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMShippingMethod {
    var $provider_;
    var $zenMethod_;


    /**
     * Create new shipping method.
     *
     * @param ZMShippingProvider provider The shipping provider for this method.
     * @param array zenMethod The zen-cart method infos.
     */
    function ZMShippingMethod($provider, $zenMethod) {
        $this->provider_ = $provider;
        $this->zenMethod_ = $zenMethod;
    }

    // create new instance
    function __construct($provider, $zenMethod) {
        $this->ZMShippingMethod($provider, $zenMethod);
    }

    function __destruct() {
    }


    function getId() { return $this->zenMethod_['id']; }
    function getName() { return $this->zenMethod_['title']; }
    function getCost() { return zm_add_tax($this->zenMethod_['cost'], $this->provider_->getTaxRate()); }
    function getProvider() { return $this->provider_; }

}

?>
