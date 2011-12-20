<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\base\dependencyInjection\Container;
use zenmagick\base\Beans;

/**
 * Bean utility.
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
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package org.zenmagick.core.utils
 */
class ZMBeanUtils extends ZMObject {
    private static $GETTER_PREFIX_LIST = array('get', 'is', 'has');
    private static $SETTER_PREFIX = 'set';
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
        return Beans::getPropertyMap($obj, $properties);
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
        return Beans::obj2map($obj, $properties, $addGeneric);
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
        return Beans::setAll($obj, $data, $keys, $setGeneric);
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
        return Beans::map2obj($clazz, $data, $keys);
    }

    /**
     * Build an object based on the bean definition.
     *
     * <p>The syntax for bean definitions is: <em>[class name]#[property1=value1&property2=value2&...]<em>.</p>
     *
     * @param string definition The bean definition.
     * @param boolean useBeanMapping If set, the definition (class only) will be used to lookup a custom bean definition; default is <code>true</code>.
     * @return mixed An object or <code>null</code>.
     */
    public static function getBean($definition, $useBeanMapping=true) {
        return Beans::getBean($definition);
    }

}
