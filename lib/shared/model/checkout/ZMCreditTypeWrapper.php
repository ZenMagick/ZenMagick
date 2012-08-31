<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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

use ZenMagick\base\ZMObject;

/**
 * A credit type wrapper for Zen Cart credit classes.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.checkout
 */
class ZMCreditTypeWrapper extends ZMObject {
    private $id_;
    private $name_;
    private $instructions_;
    private $error_;
    private $fields_;


    /**
     * Create a new payment type.
     *
     * @param int id The id.
     * @param string name The name.
     * @param string instructions Optional instructions.
     */
    public function __construct($id, $name, $instructions='') {
        parent::__construct();
        $this->id_ = $id;
        $this->name_ = $name;
        $this->instructions_ = $instructions;
        $this->error_ = null;
        $this->fields_ = array();
    }


    /**
     * Get the payment type id.
     *
     * @return int The payment type id.
     */
    public function getId() { return $this->id_; }

    /**
     * Get the payment name.
     *
     * <p><strong>This may contain HTML, depending on the module.</strong></p>
     *
     * @return string The payment name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the optional payment instructions.
     *
     * <p><strong>This may contain HTML, depending on the module.</strong></p>
     *
     * @return string Payment instructions.
     */
    public function getInstructions() { return $this->instructions_; }

    /**
     * Get the payment error (if any).
     *
     * @return string The payment error message.
     */
    public function getError() { return $this->error_; }

    /**
     * Get the payment form fields.
     *
     * @return array A list of <code>ZMPaymentField</code> instances.
     */
    public function getFields() { return $this->fields_; }

    /**
     * Add a form field to this payment type.
     *
     * @param ZMPaymentField field The new form field.
     */
    public function addField($field) { array_push($this->fields_, $field); }

}

?>
