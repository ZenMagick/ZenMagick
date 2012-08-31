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

use ZenMagick\Base\ZMObject;

/**
 * A order total line.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.order
 */
class ZMOrderTotalLine extends ZMObject {
    private $name_;
    private $value_;
    private $amount_;
    private $type_;


    /**
     * Create new total line.
     *
     * @param string name The total name.
     * @param string value The total value.
     * @param float amount The total amount.
     * @param string type The total type.
     */
    public function __construct($name=null, $value=null, $amount=0, $type=null) {
        parent::__construct();
        $this->setId(0);
        $this->name_ = $name;
        $this->value_ = $value;
        $this->amount_ = $amount;
        $this->type_ = $type;
    }


    /**
     * Get the order total id.
     *
     * @return int The order total id.
     */
    public function getId() { return $this->get('orderTotalId'); }

    /**
     * Get the order total name.
     *
     * @return string The order total name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the order total value.
     *
     * @return string The formatted order total value.
     */
    public function getValue() { return $this->value_; }

    /**
     * Get the order total amount.
     *
     * @return float The order total amount.
     */
    public function getAmount() { return $this->amount_; }

    /**
     * Get the order total type.
     *
     * @return string The order total type.
     */
    public function getType() { return $this->type_; }

    /**
     * Set the order total id.
     *
     * @param int id The order total id.
     */
    public function setId($id) { return $this->set('orderTotalId', $id); }

    /**
     * Set the order total name.
     *
     * @oparam string name The order total name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the order total value.
     *
     * @param string value The formatted order total value.
     */
    public function setValue($value) { $this->value_ = $value; }

    /**
     * Set the order total amount.
     *
     * @param float amount The order total amount.
     */
    public function setAmount($amount) { $this->amount_ = $amount; }

    /**
     * Set the order total type.
     *
     * @param string type The order total type.
     */
    public function setType($type) { $this->type_ = $type; }

}
