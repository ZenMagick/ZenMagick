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


/**
 * A shipping provider.
 *
 * <p>A shipping provider may offer 1-n shipping methods, depending on the
 * address, or other shipping conditions.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.checkout
 */
interface ZMShippingProvider {

    /**
     * Get the shipping provider id.
     *
     * @return int The shipping provider id.
     */
    public function getId();

    /**
     * Get the shipping provider name.
     *
     * @return string The shipping provider name.
     */
    public function getName();

    /**
     * Checks if an icon exists for this provider.
     *
     * @return boolean <code>true</code> if an icon, <code>false</code> if not.
     */
    public function hasIcon();

    /**
     * Get the icon.
     *
     * @return string The icon.
     */
    public function getIcon();

    /**
     * Flags whether this shipping provider is installed or not.
     *
     * @return boolean <code>true</code> if installed, <code>false</code> if not.
     */
    public function isInstalled();

    /**
     * Checks if errors are logged for this provider.
     *
     * @return boolean <code>true</code> if errors exist, <code>false</code> if not.
     */
    public function hasErrors();

    /**
     * Get the errors.
     *
     * @return array List of error messages.
     */
    public function getErrors();

    /**
     * Get a specific shipping method.
     *
     * @param string id The method id.
     * @param ZMShoppingCart shoppingCart The shopping cart.
     * @param ZMAddress address Optional shipping address; default is <code>null</code>.
     * @return ZMShippingMethod A shipping method or <code>null</code>.
     */
    public function getShippingMethodForId($id, $shoppingCart, $address=null);

    /**
     * Get available shipping methods for the given address.
     *
     * @param ZMShoppingCart shoppingCart The shopping cart.
     * @param ZMAddress address Optional shipping address; default is <code>null</code>.
     * @return array A list of <code>ZMShippingMethod</code> instances.
     */
    public function getShippingMethods($shoppingCart, $address=null);

}
