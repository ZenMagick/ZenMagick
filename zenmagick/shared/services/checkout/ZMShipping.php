<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Access class for shipping options.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services.checkout
 * @version $Id$
 */
class ZMShipping extends ZMObject {
    var $provider_;


    /**
     * Create new instance.
     */
    function __construct() {
    global $shipping_modules;

        parent::__construct();

        $this->provider_ = array();
        if (!class_exists('shipping')) {
            ZMTools::resolveZCClass('shipping');
            $zenShipping = new shipping();
        } else {
            $zenShipping = $shipping_modules;
        }
        $quotes = $zenShipping->quote();
        foreach ($quotes as $quote) {
            array_push($this->provider_, ZMLoader::make("ShippingProviderOld", $quote));
        }

    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Shipping');
    }


    // shipping available
    function hasShippingProvider() { return 0 < count($this->provider_); }

    // number of shipping providers
    function getShippingProvider() { return $this->provider_; }


    // find a particular shipping method
    function getShippingMethodForId($id) {
    }

}
