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
 * Request controller for ajax requests.
 *
 * <p>Uses native PHP function <code>json_encode</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.ajax
 * @version $Id$
 */
class ZMAjaxController extends ZMController {
    private $method_;


    /**
     * Create new instance.
     *
     * @param string id Optional id; default is <code>null</code> to use the request name.
     */
    function __construct($id=null) {
        parent::__construct($id);
        $this->method_ = ZMRequest::getParameter('method', null);
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
    public function processGet() {
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
    public function process() {
        $method = $this->method_;
        if (!method_exists($this, $this->method_)) {
            $method = $this->method_.ZMSettings::get('ajaxFormat');
        }

        // check access on controller level
        ZMSacsMapper::instance()->ensureAuthorization($this->getId());

        // (re-)check on method level
        $page = $this->getId().'#'.$method;
        ZMSacsMapper::instance()->ensureAccessMethod($page);
        ZMSacsMapper::instance()->ensureAuthorization($page);

        if (method_exists($this, $method) || in_array($method, $this->getAttachedMethods())) {
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
    public function setJSONHeader($json) {
        $this->setContentType('text/plain');
        if (ZMSettings::get('isJSONHeader')) { header("X-JSON: ".$json); }
        if (ZMSettings::get('isJSONEcho')) { echo $json; }
    }

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
    public function flattenObject($obj, $properties=null, $formatter=null) {
        $props = null;

        if (is_array($obj)) {
            $props = array();
            foreach ($obj as $k => $o) {
                $props[$k] = $this->flattenObject($o, $properties, $formatter);
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
        $props = ZMBeanUtils::obj2map($obj, $beanProperties, false);
        foreach ($props as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $sub = is_array($properties[$key]) ? $properties[$key] : null;
                $value = $this->flattenObject($value, $sub, $formatter);
            }
            $props[$key] = null != $formatter ? $formatter($obj, $key, $value) : $value;
        }
        return $props;
    }

    /**
     * Serialize object to JSON.
     *
     * @param mixed obj The object to serialize; can also be an array of objects.
     * @return string The given object as JSON.
     */
    public function toJSON($obj) {
        return json_encode($obj);
    }

}

?>
