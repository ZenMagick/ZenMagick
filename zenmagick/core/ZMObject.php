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
 * ZenMagick base class.
 *
 * <p>This is the base class for all ZenMagick classes and contains some very basic
 * stuff that might be usefull for most/all classes.</p>
 *
 * <p>Included is generic support for properties via <code>get($name)</code>, <code>set($name, $value)</code>
 * and, via the corresponding methods <code>__get($name)</code> and <code>__set($name,$value)</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick
 */
class ZMObject {
    private static $singletons_ = array();
    protected $properties_;


    /**
     * Create new instance.
     */
    function __construct() {
        $this->properties_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
    }


    /**
     * Shortcut to create new class instances.
     *
     * @param string name The class name.
     * @param var args A variable number of arguments that will be used as arguments for
     * @return mixed An instance of the class denoted by <code>$name</code> or <code>null</code>.
     * @deprecated Use <code>ZMLoader::make(..)</code> instead.
     */
    public static function create() {
        ZMLogging::instance()->trace('deprecated');
        $args = func_get_args();
        return ZMLoader::make($args);
    }

    /**
     * Get a singleton instance of the calling class.
     *
     * @param string name The class name.
     * @param string instance If set, register the given object, unless the name is already taken.
     * @return mixed A singleton object.
     */
    protected static function singleton($name, $instance=null) {
        if (null != $instance && !isset(ZMObject::$singletons_[$name])) {
            ZMObject::$singletons_[$name] = $instance;
        } else if (!array_key_exists($name, ZMObject::$singletons_)) {
            ZMObject::$singletons_[$name] = ZMLoader::make($name);
        }

        return ZMObject::$singletons_[$name];
    }


    /**
     * Support generic getter method for additional properties.
     *
     * @param string name The property name.
     * @return mixed The value or <code>null</code>.
     */
   public function __get($name) {
        if (array_key_exists($name, $this->properties_)) {
            return $this->properties_[$name];
        }
        return null;
    }

    /**
     * Support to access property values by name.
     *
     * @param string name The property name.
     * @param mixed default A default value; default value is <code>null</code>.
     * @return mixed The value or <code>null</code>.
     */
    public function get($name, $default=null) {
        if (array_key_exists($name, $this->properties_)) {
            return $this->properties_[$name];
        }
        return $default;
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
        $this->properties_[$name] = $value;
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
        } else if (0 === strpos($method, 'is') && 0 == count($args)) {
            $property = str_replace('is', '', $method);
            $property = strtolower($property[0]).substr($property, 1);
            return $this->get($property);
        } else if (0 === strpos($method, 'set') && 1 == count($args)) {
            $property = str_replace('set', '', $method);
            $property = strtolower($property[0]).substr($property, 1);
            return $this->set($property, $args[0]);
        }

        throw ZMLoader::make('ZMException', 'invalid method: '.$method);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString() {
        $s =  '['.get_class($this);
        $first = true;
        foreach (get_object_vars($this) as $name => $value) {
            if ($first) {
                $s .= ' ';
            } else {
                $s .= ', ';
            }
            $s .= $name.'=';
            if (is_object($value)) {
                $s .= '['.get_class($value).']';
            } else if (is_array($value)) {
                $s .= '{';
                $afirst = true;
                foreach ($value as $key => $val) {
                    if (!$afirst) {
                        $s .= ', ';
                    }
                    $s .= $key. '=>'.$val;
                    $afirst = false;
                }
                $s .= '}';
            } else {
                $s .= $value;
            }
            $first = false;
        }
        $s .= ']';
        return $s;
    }

}

?>
