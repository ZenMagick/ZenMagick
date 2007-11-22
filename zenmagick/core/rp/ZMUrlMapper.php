<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Handle URL mappings.
 *
 * <p>URL mappings control the actual view being displayed after the controller
 * is finished with its part of the request processing. The mapping is used
 * by the controller method <code>findView(...)</code>.</p>
 *
 * @author mano
 * @package org.zenmagick.rp
 * @version $Id$
 */
class ZMUrlMapper extends ZMObject {
    // global views; key is viewId
    var $globalViews_;
    // controller specific views; key is controller
    var $controllerViews_;
    var $mapping_;


    /**
     * Default c'tor.
     */
    function ZMUrlMapper() {
        parent::__construct();

        $this->mapping_ = array();
        $this->globalViews_ = array();
        $this->controllerViews_ = array();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMUrlMapper();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set a mapping.
     *
     * @param string controller The controller; <code>null</code> may be used to lookup shared mappings.
     * @param string viewId The view id; this is the key the controller is using to lookup the view; default is <code>null</code>.
     * @param string view The mapped view name; default is <code>null</code> to default to the controller name.
     * @param string viewClass The class to be used; default is <code>PageView</code>
     * @param array parameter Optional map of name/value pairs to further configure the view; default is <code>null</code>.
     */
    function setMapping($controller, $viewId=null, $view=null, $viewClass='PageView', $parameter=null) {
        if (null == $controller && (null == $view || null == $viewId)) {
            zm_backtrace("invalid url mapping");
        }
        $viewId = null != $viewId ? $viewId : $controller;

        // first, build the view info
        $viewInfo = array();
        $viewInfo['view'] = (null != $view ? $view : $controller);
        $viewInfo['class'] = $viewClass;
        $viewInfo['parameter'] = $parameter;

        if (null === $controller) {
            // global mapping
            $this->globalViews_[$viewId] = $viewInfo;
        } else {
            if (!isset($this->controllerViews_[$controller])) {
                $this->controllerViews_[$controller] = array();
            }
            $this->controllerViews_[$controller][$viewId] = $viewInfo;
        }
    }

    /**
     * Find a URL mapping for the given controller and viewId.
     *
     * @param string controller The controller.
     * @param string viewId The viewId; defaults to <code>null</code> to use the controller.
     * @param array parameter Optional map of name/value pairs (or URL query format string) 
     *  to further configure the view; default is <code>null</code>.
     * @return ZMView The actual view to be used to render the response.
     */
    function &findView($controller, $viewId=null, $parameter=null) {
        $viewInfo = null;

        $viewId = null != $viewId ? $viewId : $controller;

        // check controller
        if (isset($this->controllerViews_[$controller])) {
            $controllerViews = $this->controllerViews_[$controller];
            $viewInfo = (isset($controllerViews[$viewId]) ? $controllerViews[$viewId] : null);
        }

        if (null == $viewInfo) {
            // try global mappings
            $viewInfo = (isset($this->globalViews_[$viewId]) ? $this->globalViews_[$viewId] : null);
        }

        if (null == $viewInfo) {
            // set some sensible defaults
            $viewInfo = array('view' => $controller, 'class' => 'PageView', 'parameter' => null);
        }

        $view = $this->create($viewInfo['class'], $viewInfo['view']);
        $view->setMappingId($viewId);
        $parameterMap = $this->_toArray($viewInfo['parameter']);
        $parameterMap = array_merge($parameterMap, $this->_toArray($parameter));
        if (0 < count($parameterMap)) {
            foreach ($parameterMap as $name => $value) {
                $method = 'set'.ucwords($name);
                if (method_exists($view, $method)) {
                    $view->$method($value);
                }
            }
        }

        return $view;
    }

    /**
     * Little helper to handle either string or array.
     *
     * @param mixed value The value to convert; either already an array or a URL query form string.
     * @return array The value as array.
     */
    function _toArray($value) {
        if (null == $value) {
            return array();
        }
        if (is_array($value)) {
            return $value;
        }
        parse_str($value, $map);
        return $map;
    }

}

?>
