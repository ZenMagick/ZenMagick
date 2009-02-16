<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Form widget base class.
 *
 * <p>Form widgets are widgets that represent various HTML form input elements.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.widgets.form
 * @version $Id$
 */
abstract class ZMFormWidget extends ZMWidget {
    private $name_;
    private $value_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->name_ = '';
        $this->value_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the name.
     *
     * @param string name The name.
     */
    public function setName($name) {
        $this->name_ = $name;
    }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function getName() {
        return $this->name_;
    }

    /**
     * Set the value.
     *
     * @param mixed value The value.
     */
    public function setValue($value) {
        $this->value_ = $value;
    }

    /**
     * Get the value.
     *
     * @return mixed The value.
     */
    public function getValue() {
        return $this->value_;
    }

    /**
     * Handle form data.
     *
     * <p>This default implementation will jsut return the given data.<p>
     *
     * @param array data The form data.
     * @return The processed form data.
     */
    public function handleFormData($data) {
        return $data;
    }

    /**
     * Compare the given value with the widget value.
     *
     * @param string value A string value.
     * @return boolean <code>true</code> if the given value evaluates to the
     * same value as the widget value.
     */
    public abstract function compare($value);

}

?>
