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
 * Credit class interface.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.checkout
 * @todo return types
 */
interface ZMCreditClass
{
    /**
     * Get credit form data for this plugin.
     *
     * <p>In the context of a credit class, this usually consists of form elements to enter a gift voucher number or
     * redemption code.</p>
     *
     * @return array Form data details.
     * @todo specify?
     */
    public function getRedemptionBlockInfo();

    /**
     * Update credit account.
     *
     * <p>It's purpose is to decide whether each product in the cart should add something to a credit account.
     *  e.g. for the Gift Voucher it checks whether the product is a Gift voucher and then adds the amount
     *  to the Gift Voucher account.</p>
     *  <p>Another use would be to check if the product would give reward points and add these to the points/reward account.</p>
     *
     * @param ShoppingCartItem item A single item.
     * @todo drop item, give cart and leave it to implementatio; also, return some reference object to  be used later to apply...
     */
    public function updateCreditAccount($item);

    /**
     * Process checkout.
     *
     * <p>Allows a plugin to process the checkout data (ie. gift voucher, coupon code or other). This is the place to validate that data.</p>
     *
     * @param ZenMagick\Http\Request request The current request.
     * @return boolean <code>true</code> if the request data is valid with respect to this plugins credit data.
     */
    public function validateRequest($request);

    /**
     * Check if the cart is compeletely covered by credits and skip payments if so.
     *
     * @todo this is to be implemented in ot_zenmagic only!
     */
    public function cartIsCovered();

    /**
     * Apply the available credit.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @todo should there be some reference object coming from validateRequest or updateCreditAccount?
     */
    public function applyCredit();

    /**
     * Cleanup.
     */
    public function cleanup();

}
