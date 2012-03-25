<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\base;

use Serializable;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * ZenMagick base class.
 *
 * <p>This is the base class for all ZenMagick classes and contains some very basic
 * stuff that might be usefull for most/all classes.</p>
 *
 * <p>Included is generic support for properties via <code>get($name)</code>, <code>set($name, $value)</code>
 * and, via the corresponding methods <code>__get($name)</code> and <code>__set($name,$value)</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMObject extends ContainerAware implements Serializable {
    private static $methods_ = array();
    protected $properties_;


    /**
     * Create new instance.
     *
     * @param array properties Optional properties; default is an empty array;
     */
    public function __construct($properties=array()) {
        $this->properties_ = $properties;
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
     * @param boolean genericOnly If set, consider only generic properties (via get/set methods); default is <code>true</code>.
     * @return array List of custom properties set on this object.
     */
    public function getPropertyNames($genericOnly=true) {
        if ($genericOnly) {
            return array_keys($this->properties_);
        }

        return array_merge(array_keys($this->properties_), array_keys(Beans::getPropertyMap($this)));
    }

    /**
     * Get all custom properties.
     *
     * @return array Map of properties.
     */
    public function getProperties() {
        return $this->properties_;
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
        if (!isset(self::$methods_[$method])) {
            self::$methods_[$method] = array();
        }
        self::$methods_[$method][$className] = $target;
    }

    /**
     * Get all attached methods for this instance.
     *
     * @param array List of attached method names.
     */
    public function getAttachedMethods() {
        $methods = array();
        foreach (self::$methods_ as $method => $classInfo) {
            foreach (array_keys($classInfo) as $className) {
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
        if (array_key_exists($method, self::$methods_)) {
            // method found, so check if there is a class match
            foreach (array_keys(self::$methods_[$method]) as $className) {
                if ($this instanceof $className) {
                    $margs = array_merge(array($this), $args);
                    $target = self::$methods_[$method][$className];
                    return call_user_func_array($target, $margs);
                }
            }
        } else {
            foreach (Beans::$GETTER_PREFIX_LIST as $prefix) {
                if (0 === strpos($method, $prefix) && 0 == count($args)) {
                  $property = substr($method, strlen($prefix));
                  $property = strtolower($property[0]).substr($property, 1);
                  return $this->get($property);
                }
            }
            if (0 === strpos($method, Beans::$SETTER_PREFIX) && 1 == count($args)) {
                $property = substr($method, strlen(Beans::$SETTER_PREFIX));
                $property = strtolower($property[0]).substr($property, 1);
                return $this->set($property, $args[0]);
            }
        }
        throw new ZMException(sprintf('invalid method on: %s: method: "%s"', get_class($this), $method));
    }

    /**
     * Serialize this instance.
     */
    public function serialize() {
        $sprops = array();
        foreach ($this->getProperties() as $name => $obj) {
            $sprops[$name] = serialize($obj);
        }

        $serialized = serialize($sprops);

        if (function_exists('gzcompress')) {
            $serialized =  base64_encode(gzcompress($serialized));
        }

        return $serialized;
    }

    /**
     * Unserialize.
     *
     * @param string serialized The serialized data.
     */
    public function unserialize($serialized) {
        if (function_exists('gzcompress')) {
            $serialized = base64_decode(gzuncompress($serialized));
        }

        $sprops = base64_decode(unserialize($serialized));

        foreach ($sprops as $name => $sprop) {
            $this->set($name, unserialize($sprop));
        }
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
                    $s .= $key. '=>';
                    if (is_object($val)) {
                        $s .= '['.get_class($val).']';
                    } else {
                        $s .= $val;
                    }
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
