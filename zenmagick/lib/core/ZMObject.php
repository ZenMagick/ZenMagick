<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * @package org.zenmagick.core
 * @version $Id$
 */
class ZMObject {
    private static $singletons_ = array();
    private static $methods_ = array();
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
     * Get a singleton instance of the named class.
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
     * Support generic getter method for additional properties.
     *
     * @param string name The property name.
     * @return mixed The value or <code>null</code>.
     */
    public function __get($name) {
        ZMLogging::instance()->trace('accessing undeclated class property(get): '.$name.' on: '.get_class($this), ZMLogging::WARN);
        if (array_key_exists($name, $this->properties_)) {
            return $this->properties_[$name];
        }
        return null;
    }

    /**
     * Support generic setter method for additional properties.
     *
     * @param string name The property name.
     * @param mixed value The value.
     */
    public function __set($name, $value) {
        ZMLogging::instance()->trace('accessing undeclated class property(set): '.$name.' on: '.get_class($this), ZMLogging::WARN);
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
     * Get a list of all properties.
     *
     * @param boolean customOnly If set, consider only custom properties; default is <code>true</code>.
     * @return array List of custom properties set on this object.
     */
    public function getPropertyNames($customOnly=true) {
        if ($customOnly) {
            return array_keys($this->properties_);
        }

        return array_merge(array_keys($this->properties_), array_keys(ZMBeanUtils::getPropertyMap($this)));
    }

    /**
     * Attach a dynamic method to objects.
     *
     * <p>Target functions/methods have the following signature: <code>($ref, ...)</code>. <code>$ref</code> is
     * the current instance (<code>$this</code>), and the remaining arguments are the actual call parameter.</p>
     *
     * @param string method The method name.
     * @param string className Name of the class to allow the method.
     * @param mixed target The target function or method.
     */
    public static function attachMethod($method, $className, $target) {
        if (!isset(ZMObject::$methods_[$method])) {
            ZMObject::$methods_[$method] = array();
        }
        ZMObject::$methods_[$method][$className] = $target;
    }

    /**
     * Get all attached methods for this instance.
     *
     * @param array List of attached method names.
     */
    public function getAttachedMethods() {
        $methods = array();
        foreach (ZMObject::$methods_ as $method => $classInfo) {
            foreach (array_keys($classInfo) as $className) {
                //XXX: use the best match
                if ($this instanceof $className) {
                    $methods[] = $method;
                    break;
                }
            }
        }

        return $methods;
    }

    /**
     * Handle dynamic methods incl. generic setXXX(), getXXX(), isXXX() and hasXXX().
     *
     * @param string method The method name.
     * @param array args Optional arguments.
     * @return mixed The result of the supported method or null.
     */
    public function __call($method, $args) {
        // start with dynamic methods to allow attaching methods that start with 'get', etc...
        if (isset(ZMObject::$methods_[$method])) {
            // method found, so check if there is a class match
            foreach (array_keys(ZMObject::$methods_[$method]) as $className) {
                //XXX: use the best match
                if ($this instanceof $className) {
                    $margs = array_merge(array($this), $args);
                    $target = ZMObject::$methods_[$method][$className];
                    //XXX: consider adding explicit support for calls up to 3 parameters to avoid
                    // usin call_user_func_array()
                    return call_user_func_array($target, $margs);
                }
            }
        } else if (0 === strpos($method, 'get') && 0 == count($args)) {
            $property = substr($method, 3);
            $property = strtolower($property[0]).substr($property, 1);
            return $this->get($property);
        } else if (0 === strpos($method, 'is') && 0 == count($args)) {
            $property = substr($method, 2);
            $property = strtolower($property[0]).substr($property, 1);
            return $this->get($property);
        } else if (0 === strpos($method, 'has') && 0 == count($args)) {
            $property = substr($method, 3);
            $property = strtolower($property[0]).substr($property, 1);
            return $this->get($property);
        } else if (0 === strpos($method, 'set') && 1 == count($args)) {
            $property = substr($method, 3);
            $property = strtolower($property[0]).substr($property, 1);
            return $this->set($property, $args[0]);
        }

        throw new ZMException('invalid method on: '.get_class($this).': '.$method);
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
