<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Model base class.
 *
 * <p>This class provides generic support for properties via <code>get($name)</code>, <code>set($name, $value)</code>
 * and, for PHP5, via the corresponding methods <code>__get($name)</code> and <code>__set($name,$value)</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick
 * @version $Id$
 */
class ZMModel extends ZMObject {
    protected $properties_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->properties_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Populate all available fields from the given request.
     *
     * @param array req A request; if <code>null</code>, use the current <code>ZMRequest</code> instead.
     */
    public function populate($req=null) {
    }

    /**
     * Populate custom fields from the given request.
     *
     * @param string table The table name.
     */
    public function populateCustom($table) {
        $fields = ZMDbUtils::getCustomFields($table);
        $parameter = ZMRequest::getParameterMap();
        foreach ($fields as $field) {
            $name = $field[0];
            if (isset($parameter[$name])) {
                $this->set($name, ZMRequest::getParameter($name));
            }
        }
    }

    /**
     * Support generic getter method for additional properties.
     *
     * @param string name The property name.
     * @return mixed The value or <code>null</code>.
     */
    public function __get($name) {
        if (isset($this->properties_[$name])) {
            return $this->properties_[$name];
        }
        return null;
    }

    /**
     * Support to access property values by name.
     *
     * @param string name The property name.
     * @return mixed The value or <code>null</code>.
     */
    public function get($name) {
        return $this->__get($name);
    }

    /**
     * Support generic setter method for additional properties.
     *
     * @param string name The property name.
     * @param mixed value The value.
     */
    public function __set($name, $value) {
        $this->properties_[$name] = $value;
    }

    /**
     * Support to set property values by name.
     *
     * @param string name The property name.
     * @param mixed value The value.
     */
    public function set($name, $value) {
        $this->__set($name, $value);
    }

    /**
     * Get a list of all custom properties.
     *
     * @return array List of custom properties set on this object.
     */
    public function getPropertyNames() {
        return array_keys($this->properties_);
    }

    /**
     * Handle generic getXXX()/setXXX()/isXXX() methods.
     *
     * @param string method The method name.
     * @param array args Optional arguments.
     * @return mixed The result of the supported method or null.
     */
    public function __call($method, $args) {
        if (0 === strpos($method, 'get') && 0 == count($args)) {
            $property = str_replace('get', '', $method);
            $property = strtolower($property[0]).substr($property, 1);
            return $this->get($property);
        } else 
        if (0 === strpos($method, 'is') && 0 == count($args)) {
            $property = str_replace('is', '', $method);
            $property = strtolower($property[0]).substr($property, 1);
            return $this->get($property);
        } else if (0 === strpos($method, 'set') && 1 == count($args)) {
            $property = str_replace('set', '', $method);
            $property = strtolower($property[0]).substr($property, 1);
            return $this->set($property, $args[0]);
        } else {
            ZMObject::backtrace('invalid method: '.$method);
        }
        return $null;
    }

}

?>
