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
?>
<?php
namespace zenmagick\base;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

use zenmagick\base\dependencyInjection\Container;

/**
 * ZenMagick beans.
 *
 * <p>Bean definitions and properties handled by this class may one of the following special (magic) name prefixes
 * to create/set objects rather than strings:</p>
 * <dl>
 *  <dt>bean::</dt>
 *  <dd>The string (without the prefix) will be taken as bean definition; special case is a bean definition of <em>null</em> which
 *   will be converted to a PHP <code>null</code>.</dd>
 *  <dt>ref::</dt>
 *  <dd>This prefix indicates that the following string is to be taken as bean definition. However, the instance created/obtained
 *   will first be looked up as singleton instance. It is important to remember that by setting properties on references these settings
 *   will be permanent for all subsequent code using that singleton.</dd>
 * </dl>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Beans {
    public static $GETTER_PREFIX_LIST = array('get', 'is', 'has');
    public static $SETTER_PREFIX = 'set';
    private static $propertyMap_ = array();


    /**
     * Get the property mapping for a given class/object.
     *
     * <p>If explicitely set (via <code>$properties</code>), generic properties will be considered too.</p>
     *
     * @param mixed obj The class instance.
     * @param array properties Optional list of properties to use; default is <code>null</code> for all.
     * @return array A property / method map.
     */
    public static function getPropertyMap($obj, $properties=null) {
        $clazz = get_class($obj);
        // key depends on both class and properties
        $cacheKey = $clazz.serialize($properties);
        if (!array_key_exists($cacheKey, self::$propertyMap_)) {
            $propertiesMap = array();
            if (null === $properties) {
                foreach (get_class_methods($obj) as $method) {
                    foreach (self::$GETTER_PREFIX_LIST as $prefix) {
                        if (0 === strpos($method, $prefix) && $method != $prefix) {
                            $property = substr($method, strlen($prefix));
                            $property = strtolower($property[0]) . substr($property, 1);
                            $propertiesMap[$property] = $method;
                        }
                    }
                }
            } else {
                // convert into the expected 'property' => method format
                $generic = ($obj instanceof ZMObject) ? $obj->getPropertyNames() : array();
                foreach ($properties as $property) {
                    foreach (self::$GETTER_PREFIX_LIST as $prefix) {
                        $method = $prefix.ucfirst($property);
                        if (method_exists($obj, $method) || in_array($property, $generic)) {
                            $propertiesMap[$property] = $method;
                            break;
                        }
                    }
                }
            }
            self::$propertyMap_[$cacheKey] = $propertiesMap;
        }

        return self::$propertyMap_[$cacheKey];
    }

    /**
     * Convert an object into a map.
     *
     * <p>If explicitely set (via <code>$properties</code>), generic properties will be considered, even if
     * <code>$addGeneric</code> is set to <code>false</code>.</p>
     *
     * @param mixed obj The class instance.
     * @param array properties Optional list of properties to use; default is <code>null</code> for all.
     * @param addGeneric Optional flag to indicate whether generic <code>ZMObject</code> properties should be
     *  included or not; default is <code>true</code> to include generic properties.
     * @return array The object data as map.
     */
    public static function obj2map($obj, $properties=null, $addGeneric=true) {
        if (is_array($obj)) {
            $map = array();
            if (null !== $properties) {
                foreach ($properties as $key) {
                    if (array_key_exists($key, $obj)) {
                        $map[$key] = $obj[$key];
                    }
                }
            }
            return $map;
        }

        $propertiesMap = self::getPropertyMap($obj, $properties);

        // now run all methods and build the map
        $map = array();
        foreach ($propertiesMap as $property => $method) {
            $map[$property] = $obj->$method();
        }

        // special case for ZMObject instances
        if ($addGeneric && $obj instanceof ZMObject) {
            foreach ($obj->getPropertyNames() as $property) {
                $map[$property] = $obj->get($property);
            }
        }

        return $map;
    }

    /**
     * Set a given map of key/value pairs on an object.
     *
     * @param mixed obj The class instance or array.
     * @param array data The data map.
     * @param array keys Optional list of data keys to be used; default is <code>null</code> to use all.
     * @param setGeneric Optional flag to indicate whether generic <code>ZMObject</code> properties should be
     *  included or not; default is <code>true</code> to include generic properties.
     * @return mixed The (modified) <code>$obj</code>.
     */
    public static function setAll($obj, $data, $keys=null, $setGeneric=true) {
        $isGeneric = ($obj instanceof ZMObject);
        foreach ($data as $property => $value) {
            if (is_string($value) && (0 === strpos($value, 'ref::') || 0 === strpos($value, 'bean::'))) {
                $value = self::getBean($value);
            }
            if (null === $keys || in_array($property, $keys)) {
                $method = self::$SETTER_PREFIX.ucfirst($property);
                if (method_exists($obj, $method)) {
                    $obj->$method($value);
                } else if ($isGeneric && $setGeneric) {
                    $obj->set($property, $value);
                } else if (is_array($obj)) {
                    $obj[$property] = $value;
                }
            }
        }
        return $obj;
    }

    /**
     * Create a new instance of the given class and populate with the provided data.
     *
     * @param string clazz The class name.
     * @param array data The data map.
     * @param array keys Optional list of data keys to be used; default is <code>null</code> to use all.
     * @return mixed An instance of the given class or <code>null</code>.
     */
    public static function map2obj($clazz, $data, $keys=null) {
        if (null != ($obj = Runtime::getContainer()->get($clazz, Container::NULL_ON_INVALID_REFERENCE))) {
            self::setAll($obj, $data, $keys);
            return $obj;
        }
        return null;
    }

    /**
     * Build an object based on the bean definition.
     *
     * <p>The syntax for bean definitions is: <em>[class name]#[property1=value1&property2=value2&...]<em>.</p>
     *
     * @param string definition The bean definition.
     * @return mixed An object or <code>null</code>.
     */
    public static function getBean($definition) {
        if (empty($definition)) {
            return null;
        }

        $isRef = false;
        $isPlugin = false;
        if (0 === strpos($definition, 'bean::')) {
            $definition = substr($definition, 6);
            if ('null' == $definition) {
                return null;
            }
        } else if (0 === strpos($definition, 'plugin::')) {
            $definition = substr($definition, 8);
            $isPlugin = true;
        } else if (0 === strpos($definition, 'ref::')) {
            $definition = substr($definition, 5);
            $isRef = true;
        }

        // got a valid definition, so let's look that up in the context, just in case
        $tokens = explode('#', $definition, 2);

        if (1 < count($tokens)) {
            parse_str($tokens[1], $properties);
        } else {
            $properties = array();
        }

        if ($isRef && null != ($ref = Runtime::getContainer()->get($tokens[0]))) {
            self::setAll($ref, $properties);
            return $ref;
        }

        if ($isPlugin && null != ($plugin = Runtime::getContainer()->get('pluginService')->getPluginForId($tokens[0]))) {
            self::setAll($plugin, $properties);
            return $plugin;
        }

        return self::map2obj($tokens[0], $properties);
    }

}
