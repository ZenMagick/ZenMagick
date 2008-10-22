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
 * Bean/model utilities.
 *
 * @author DerManoMann
 * @package org.zenmagick.misc
 * @version $Id$
 * @todo caching
 */
class ZMBeanUtils {
    private static $GETTER_PREFIX_LIST = array('get', 'is', 'has');
    private static $SETTER_PREFIX = 'set';
    private static $propertyMap_ = array();


    /**
     * Get the property mapping for a given class/object.
     *
     * @param mixed obj The class instance.
     * @param array properties Optional list of properties to use; default is <code>null</code> for all.
     * @return array A property / method map.
     */
    protected function getPropertyMap($obj, $properties=null) {
        $clazz = get_class($obj);
        if (!array_key_exists($clazz, self::$propertyMap_)) {
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
                foreach ($properties as $property) {
                    foreach (self::$GETTER_PREFIX_LIST as $prefix) {
                        $method = $prefix.ucfirst($property);
                        if (method_exists($obj, $method)) {
                            $propertiesMap[$property] = $method;
                            break;
                        }
                    }
                }
            }
            self::$propertyMap_[$clazz] = $propertiesMap;
        }

        return self::$propertyMap_[$clazz];
    }

    /**
     * Convert an object into a map.
     *
     * @param mixed obj The class instance.
     * @param array properties Optional list of properties to use; default is <code>null</code> for all.
     * @return array The object data as map.
     */
    public static function obj2map($obj, $properties=null) {
        $propertiesMap = self::getPropertyMap($obj, $properties);

        // now run all methods and build the map
        $map = array();
        foreach ($propertiesMap as $property => $method) {
            $map[$property] = $obj->$method();
        }

        // special case for ZMModel instances
        if ($obj instanceof ZMModel) {
            foreach ($obj->getPropertyNames() as $property) {
                //if (!array_key_exists($property, $map) && (null === $properties || array_key_exists($property, $properties))) {
                    $map[$property] = $obj->get($property);
                //}
            }
        }

        return $map;
    }

    /**
     * Set a given map of key/value pairs on an object.
     *
     * @param mixed obj The class instance.
     * @param array data The data map.
     * @param array keys Optional list of data keys to be used; default is <code>null</code> to use all.
     */
    public static function setAll($obj, $data, $keys=null) {
        if (null !== $keys) {
            $keys = array_flip($keys);
        }
        $isModel = ($obj instanceof ZMModel);
        foreach ($data as $property => $value) {
            if (null === $keys || array_key_exists($property, $keys)) {
                $method = self::$SETTER_PREFIX.ucfirst($property);
                if (method_exists($obj, $method)) {
                    $obj->$method($value);
                } else if ($isModel) {
                    $obj->set($property, $value);
                }
            }
        }
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
        $obj = ZMLoader::make($clazz);
        if (null !== $obj) {
            self::setAll($obj, $data, $keys);
        }

        return $obj;
    }

}

?>
