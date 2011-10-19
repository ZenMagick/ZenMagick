<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * A payment type.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.checkout
 */
interface ZMPaymentType {

    /**
     * Get the payment type id.
     *
     * @return int The payment type id.
     */
    public function getId();

    /**
     * Get the payment UI name.
     *
     * <p><strong>This may contain HTML, depending on the module.</strong></p>
     *
     * @return string The payment name.
     */
    public function getName();

    /**
     * Get the payment title.
     *
     * <p><strong>This may contain HTML, depending on the module.</strong></p>
     *
     * @return string The payment title.
     */
    public function getTitle();

    /**
     * Get the description.
     *
     * <p><strong>This may contain HTML, depending on the module.</strong></p>
     *
     * @return string More details description.
     */
    public function getDescription();

    /**
     * Get the payment error (if any).
     *
     * @return string The payment error message.
     */
    public function getError();

    /**
     * Get the payment form fields.
     *
     * @return array A list of <code>ZMPaymentField</code> instances.
     */
    public function getFields();

    /**
     * Get the info field.
     *
     * @return string Additional information or <code>null</code>.
     */
    public function getInfo();

    /**
     * Get form validation javaScript for this payment type.
     *
     * @param ZMRequest request The current request.
     * @return string JavaScript code.
     */
    public function getFormValidationJS($request);

    /**
     * Get the content (HTML) for the actual order form (button).
     *
     * @param ZMRequest request The current request.
     * @return string The order form content for this payment type.
     */
    public function getOrderFormContent($request);

    /**
     * Get the url to be used for the actual order form.
     *
     * <p>This is mostly relevant for payment types with externally hosted payment forms.</p>
     *
     * @param ZMRequest request The current request.
     * @return string A url or <code>null</code>.
     */
    public function getOrderFormUrl($request);

    /**
     * Get the default order status for orders using this payment type.
     *
     * @return int A custom order status value or <code>null</code> to use the default.
     */
    public function getOrderStatus();

}
