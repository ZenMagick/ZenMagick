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
 * Central storage of url mappings.
 *
 * <p>URL mappings map things like the controller, view and template used to a requestId.</p>
 *
 * <p>To simplify, there are a lot of conventions and defaults to minimize the need for using
 * mappings.</p>
 *
 * <p>Mappings may be set explicitely via the <code>setMapping()</code> method. However, the
 * preferred way is to load mappings from a configuration (YAML) file.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc
 * @version $Id$
 */
class ZMUrlManager extends ZMObject {
    private static $MAPPING_KEYS = array('controller', 'formId' , 'form' , 'view', 'template');
    private $mappings_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->clear();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('UrlManager');
    }


    /**
     * Clear all mappings.
     */
    public function clear() {
        $this->mappings_ = array();
    }

    /**
     * Load mappings from a YAML style string.
     *
     * @param string yaml The yaml style mappings.
     * @param boolean override Optional flag to control whether to override existing mappings or to merge;
     *  default is <code>true</code> to override.
     */
    public function load($yaml, $override=true) {
        $this->mappings_ = ZMRuntime::yamlLoad($yaml, $this->mappings_, $override);
    }

    /**
     * Set mapping details for a given request id.
     *
     * @param string requestId The request id to configure.
     * @param mixed mapping The mapping, either as YAML string fragment or nested array.
     * @param boolean override Optional flag to control whether to override existing mappings or to merge;
     *  default is <code>true</code> to override.
     */
    public function setMapping($requestId, $mapping, $override=true) {
        if (null == $requestId) {
            // globals
            $requestId = 'null';
        }
        if (!is_array($mapping)) {
            $mapping = Spyc::YAMLLoadString($mapping);
        }
        if ($override || !array_key_exists($requestId, $this->mappings_)) {
            $this->mappings_[$requestId] = $mapping;
        } else {
            $this->mappings_[$requestId] = array_merge_recursive($this->mappings_[$requestId], $mapping);
        }
    }

    /**
     * Find a mapping for the given requestId (and viewId).
     *
     * <p>This method will use a number of fallback/default conventions for missing mappings:</p>
     *
     * <p>If no mapping is found for the given <em>requestId</em>, the global mappings will be queried.
     * Should that fail as well, <code>null</code> will be returned.</p>
     *
     * <p>If mappings are found, the most specific values are returned. Mapping keys that do not exit will be
     * populated with a value of <code>null</code>.</p>
     *
     * @param string requestId The request id.
     * @param string viewId Optional view id; defaults to <code>null</code> to use defaults.
     * @param mixed parameter Optional map of name/value pairs (or URL query format string) 
     *  to further configure the view; default is <code>null</code>.
     * @return array A mapping.
     */
    public function findMapping($requestId, $viewId=null, $parameter=null) {
        ZMLogging::instance()->log('find mapping: requestId='.$requestId.', viewId='.$viewId.', parameter='.$parameter, ZMLogging::TRACE);
        if (null == $requestId && null == $viewId) {
            throw new ZMException('invalid arguments');
        }

        $mapping = null;
        if (!array_key_exists($requestId, $this->mappings_) || (null != $viewId && array_key_exists($viewId, $this->mappings_['null']))) {
            // try global mappings
            $requestId = 'null';
        }

        if (array_key_exists($requestId, $this->mappings_) && (null == $viewId || array_key_exists($viewId, $this->mappings_[$requestId]))) {
            // either default mappings or viewId specific mappings...
            if (null == $viewId) {
                $from = $this->mappings_[$requestId];
            } else {
                $from = array_merge($this->mappings_[$requestId][$viewId], $this->mappings_[$requestId]);
            }
            $mapping = array();
            foreach (self::$MAPPING_KEYS as $key) {
                if (array_key_exists($key, $from)) {
                    $mapping[$key] = $from[$key];
                } else {
                    $mapping[$key] = null;
                }
            }
        }

        return $mapping;
    }

    /**
     * Find and instantiate a controller object for the given request id.
     *
     * <p>Determining the controller class is a three stage process:</p>
     * <ol>
     *  <li>Check if a controller definition is mapped to the given request id</li>
     *  <li>Derive a controller class name from the request id and check if the resulting class exists</li>
     *  <li>Use the configured default controller definition, as set via <em>'zenmagick.mvc.controller.default'</em></li>
     * </ol>
     *
     * @param string requestId The request id.
     * @return ZMController A controller instance to handle the request.
     */
    public function findController($requestId) {
        ZMLogging::instance()->log('find controller: requestId='.$requestId, ZMLogging::TRACE);
        $definition = null;
        if (array_key_exists($requestId, $this->mappings_) && array_key_exists('controller', $this->mappings_[$requestId])) {
            // configured
            $definition = $this->mappings_[$requestId]['controller'];
        } else {
            $definition = ZMLoader::makeClassname($requestId.'Controller');
        }

        ZMLogging::instance()->log('controller definition: '.$definition, ZMLogging::TRACE);
        if (null == ($controller = ZMBeanUtils::getBean($definition))) {
            $controller = ZMBeanUtils::getBean(ZMSettings::get('zenmagick.mvc.controller.default', 'Controller'));
        }

        return $controller;
    }

    /**
     * Find and instantiate a view object for the given request id (and view id).
     *
     * <p>If no mapping is found, some sensible defaults will be used.</p>
     *
     * <p>The default view (definition) will is taken from the setting <em>'zenmagick.mvc.view.default'</em>.</p>
     *
     * @param string requestId The request id.
     * @param string viewId Optional view id; defaults to <code>null</code> to use defaults.
     * @param mixed parameter Optional map of name/value pairs (or URL query format string) 
     *  to further configure the view; default is <code>null</code>.
     * @return ZMView A <em>best match</em> view.
     */
    public function findView($requestId, $viewId=null, $parameter=null) {
        ZMLogging::instance()->log('find view: requestId='.$requestId.', viewId='.$viewId.', parameter='.$parameter, ZMLogging::TRACE);
        $mapping = $this->findMapping($requestId, $viewId, $parameter);

        if (null === $mapping) {
            ZMLogging::instance()->log('no view found for: requestId='.$requestId.', viewId='.$viewId.', parameter='.$parameter, ZMLogging::TRACE);
            $mapping = array();
        }
        if (!array_key_exists('template', $mapping) || null == $mapping['template']) {
            $mapping['template'] = $requestId;
        }
        // default
        $view = ZMSettings::get('zenmagick.mvc.view.default', 'SavantView');
        if (array_key_exists('view', $mapping) && null != $mapping['view']) {
            $view = $mapping['view'];
        }

        if (is_array($parameter)) {
            $parameter = http_build_query($parameter);
        }
        $definition = $view.(false === strpos($view, '#') ? '#' : '&').$parameter.'&template='.$mapping['template'];
        ZMLogging::instance()->log('view definition: '.$definition, ZMLogging::TRACE);
        return ZMBeanUtils::getBean($definition);
    }

}

?>
