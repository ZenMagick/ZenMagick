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
 * Request controller for ajax requests.
 *
 * <p>Requires PEAR Json for JSON support.</p>
 *
 * @author mano
 * @package org.zenmagick.rp.ajax
 * @version $Id$
 */
class ZMAjaxController extends ZMController {
    var $method_;
    var $jason_;

    /**
     * Create new instance.
     */
    function ZMAjaxController() {
    global $zm_request;

        parent::__construct();

        $this->method_ = $zm_request->getParameter('method', null);
        $this->json_ = new Services_JSON();
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->ZMAjaxController();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
    }


    /**
     * Process a HTTP GET request.
     *
     * <p>Just return <code>null</code>.</p>
     */
    function processGet() {
        echo "Invalid Ajax request - method '".$this->method_."' not found!";
        return null;
    }


    /**
     * Process a HTTP request.
     *
     * <p>This implementation will delegate request handling based on the method parameter in
     * the request. If no method is found, the default <em>parent</em> <code>process()</code> implementation
     * will be called.</p>
     *
     * <p>Also, if the passed method is not found, the controller will try to resolve the method by appending the
     * configured <em>ajaxFormat</em> string. So, if, for example, the method is <code>getCountries</code> and <em>ajaxFormat</em> is
     * <code>JSON</code>, the controller will first look for <code>getCountries</code> and then for <code>getCountriesJSON</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    function process() {
        $method = $this->method_;
        if (!method_exists($this, $this->method_) && method_exists($this, $this->method_.zm_setting('ajaxFormat'))) {
            $method = $this->method_.zm_setting('ajaxFormat');
        }

        if (method_exists($this, $method)) {
            call_user_func(array($this, $method));
            return null;
        }

        return parent::process();
    }


    /**
     * Set JSON response header ('X-JSON').
     *
     * @param string json The JSON data.
     */
    function setJSONHeader($json) {
        $this->setContentType('text/plain');
        if (zm_setting('isJSONHeader')) { header("X-JSON: ".$json); }
        if (zm_setting('isJSONEcho')) { echo $json; }
    }

    /**
     * Flattens any given object.
     *
     * <p>Criteria for the included data is the ZenMagick naming convention that access methods start with
     * either <code>get</code> or <code>is/has</code>.</p>
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
    function flattenObject($obj, $methods=null, $formatter=null) {
        $props = null;

        if (is_array($obj)) {
            $props = array();
            foreach ($obj as $k => $o) {
                $props[$k] = $this->flattenObject($o, $methods, $formatter);
            }
            return $props;
        }

        if (!is_object($obj)) {
            // as is
            return $obj;
        }

        if (null == $methods) {
            // all get and is methods
            $all = get_class_methods($obj);
            $methods = array();
            $prefixList = array('get', 'is', 'has');
            foreach ($all as $method) {
                foreach ($prefixList as $prefix) {
                    if (zm_starts_with($method, $prefix)) {
                        array_push($methods, substr($method, strlen($prefix)));
                    }
                }
            }
        }

        $props = array();
        foreach ($methods as $key => $value) {
            $method = $value;
            $sub = null;
            if (is_array($method)) {
                // use key to allow for recursive mappings
                $method = $key;
                // use value array for recursive mappings
                $sub = $value;
            }
            $getter = 'get'.ucfirst($method);
            if (method_exists($obj, $getter)) {
                $prop = $obj->$getter();
                if (is_object($prop) || is_array($prop)) {
                    $prop = $this->flattenObject($prop, $sub, $formatter);
                }
                $props[$method] = null != $formatter ? $formatter($obj, $method, $prop) : $prop;
            } else {
                $getter = 'is'.ucfirst($method);
                if (method_exists($obj, $getter)) {
                    $prop = $obj->$getter();
                    $props[$method] = null != $formatter ? $formatter($obj, $method, $prop) : $prop;
                }
            }
        }

        return $props;
    }

    /**
     * Serialize object to JSON.
     *
     * @param mixed obj The object to serialize; can also be an array of objects.
     * @return string The given object as JSON.
     */
    function toJSON($obj, $methods=null, $formatter=null) {
        return $this->json_->encode($obj);
    }

}

?>
