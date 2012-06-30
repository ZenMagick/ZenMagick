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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;

/**
 * Ajax utils.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.utils
 */
class ZMAjaxUtils {

    /**
     * Flattens any given object.
     *
     * <p>Criteria for the included data is the ZenMagick naming convention that access methods start with
     * either <code>get</code>, <code>is</code> or <code>has</code>.</p>
     *
     * <p>If the given object is an array, all elements will be converted, too. Generally speaking, this method works
     * recursively. Arrays are preserved, array values, in turn, will be flattened.</p>
     *
     * <p>The methods array may contain nested arrays to allow recursiv method mapping. The Ajax product controller is
     * a good example for this.</p>
     *
     * @param mixed obj The object.
     * @param array methods Optional list of methods to include as properties.
     * @param function formatter Optional formatting method for all values; signature is <code>formatter($obj, $name, $value)</code>.
     * @return array Associative array of methods values.
     */
    public static function flattenObject($obj, $properties=null, $formatter=null) {
        $props = null;

        if (is_array($obj)) {
            $props = array();
            foreach ($obj as $k => $o) {
                $props[$k] = self::flattenObject($o, $properties, $formatter);
            }
            return $props;
        }

        if (!is_object($obj)) {
            // as is
            return $obj;
        }

        // properties may be a mix of numeric and string key - ugh!
        $beanProperties = array();
        foreach ($properties as $key => $value) {
            $beanProperties[] = is_array($value) ? $key : $value;
        }
        $props = Beans::obj2map($obj, $beanProperties, false);
        foreach ($props as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $sub = is_array($properties[$key]) ? $properties[$key] : null;
                $value = self::flattenObject($value, $sub, $formatter);
            }
            $props[$key] = null != $formatter ? $formatter($obj, $key, $value) : $value;
        }
        return $props;
    }

}
