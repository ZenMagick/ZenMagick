<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
 * Shipping method.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.checkout
 */
interface ZMShippingMethod
{
    /**
     * Get the shipping method id.
     *
     * @return int The shipping method id.
     */
    public function getId();

    /**
     * Get the shipping method name.
     *
     * @return string The shipping method name.
     */
    public function getName();

    /**
     * Get the shipping cost.
     *
     * @return float The shipping cost.
     */
    public function getCost();

    /**
     * Get the shipping provider.
     *
     * @return ZMShippingProvider The shipping provider.
     */
    public function getProvider();

    /**
     * Get the shipping id.
     *
     * @return string The shipping id as used by the shopping cart.
     */
    public function getShippingId();

}
