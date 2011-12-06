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

use zenmagick\base\ClassLoader;
use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\logging\Logging;

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
 */
class ZMUrlManager extends ZMObject {
    private static $MAPPING_KEYS = array('controller', 'formId', 'form', 'view', 'template', 'layout');
    private static $TYPE_KEYS = array('global', 'page', 'alias');
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
        return Runtime::getContainer()->get('urlManager');
    }


    /**
     * Clear all mappings.
     */
    public function clear() {
        $this->mappings_ = array();
        foreach (self::$TYPE_KEYS as $key) {
            $this->mappings_[$key] = array();
        }
    }

    /**
     * Load mappings from a YAML style string.
     *
     * @param string yaml The yaml style mappings.
     * @param boolean override Optional flag to control whether to override existing mappings or to merge;
     *  default is <code>true</code> to override.
     */
    public function load($yaml, $override=true) {
        $this->mappings_ = ZMRuntime::yamlParse($yaml, $this->mappings_, $override);
    }

    /**
     * Set mapping details for a given request id.
     *
     * @param string requestId The request id to configure or <code>null</code> for a global mapping.
     * @param mixed mapping The mapping, either as YAML string fragment or nested array.
     * @param boolean replace Optional flag to control whether to replace an existing mapping or to merge;
     *  default is <code>true</code> to replace.
     */
    public function setMapping($requestId, $mapping, $replace=true) {
        $type = null == $requestId ? 'global' : 'page';
        if (!is_array($mapping)) {
            $mapping = ZMRuntime::yamlParse($mapping);
        }

        if (null == $requestId) {
            // global
            $this->mappings_[$type] = ZMLangUtils::arrayMergeRecursive($this->mappings_[$type], $mapping);
        } else {
            if ($replace) {
                $this->mappings_[$type][$requestId] = $mapping;
            } else {
                if (!array_key_exists($requestId, $this->mappings_[$type])) {
                    $this->mappings_[$type][$requestId] = array();
                }
                $this->mappings_[$type][$requestId] = ZMLangUtils::arrayMergeRecursive($this->mappings_[$type][$requestId], $mapping);
            }
        }

        // ensure we do have both required keys
        foreach (self::$TYPE_KEYS as $type) {
            if (!array_key_exists($type, $this->mappings_)) {
                $this->mappings_[$type] = array();
            }
        }
    }

    /**
     * Set multiple mappings.
     *
     * @param mixed mappings The mappings, either as YAML string fragment or nested array.
     * @param boolean replace Optional flag to control whether to replace existing mappings or to merge;
     *  default is <code>false</code> to merge.
     */
    public function setMappings($mappings, $replace=false) {
        if (is_array($mappings)) {
            if ($replace) {
                $this->mappings_ = $mappings;
            } else {
                $this->mappings_ = ZMLangUtils::arrayMergeRecursive($this->mappings_, $mappings);
            }
        } else {
            $this->mappings_ = ZMRuntime::yamlParse($mappings, $this->mappings_, $replace);
        }

        // ensure we do have both required keys
        foreach (self::$TYPE_KEYS as $type) {
            if (!array_key_exists($type, $this->mappings_)) {
                $this->mappings_[$type] = array();
            }
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
        Runtime::getLogging()->log('find mapping: requestId='.$requestId.', viewId='.$viewId.', parameter='.$parameter, Logging::TRACE);
        if (null == $requestId && null == $viewId) {
            throw new ZMException('invalid arguments');
        }

        // all matching mapping data
        $data = array();

        if (null != $viewId) {
            if (null != $requestId) {
                // both
                if (array_key_exists($requestId, $this->mappings_['page'])) {
                    // a start: requestId defaults
                    $data = $this->mappings_['page'][$requestId];
                    if (array_key_exists($viewId, $this->mappings_['page'][$requestId])) {
                        // requestId specific viewId
                        $data = array_merge($data, $this->mappings_['page'][$requestId][$viewId]);
                    } else if (array_key_exists($viewId, $this->mappings_['global'])) {
                        // global viewId
                        $data = array_merge($data, $this->mappings_['global'][$viewId]);
                    }
                } else if (array_key_exists($viewId, $this->mappings_['global'])) {
                    // global viewId
                    $data = array_merge($data, $this->mappings_['global'][$viewId]);
                }
            } else {
                if (array_key_exists($viewId, $this->mappings_['global'])) {
                    // viewId only
                    $data = $this->mappings_['global'][$viewId];
                }
            }
        } else {
            // requestId only
            if (array_key_exists($requestId, $this->mappings_['page'])) {
                // a start: requestId defaults
                $data = $this->mappings_['page'][$requestId];
            } else {
                if (array_key_exists($requestId, $this->mappings_['global'])) {
                    // all there is
                    $data = $this->mappings_['global'][$requestId];
                }
            }
        }

        $mapping = array();
        // set defaults for all missing keys
        foreach (self::$MAPPING_KEYS as $key) {
            if (array_key_exists($key, $data)) {
                $mapping[$key] = $data[$key];
            } else {
                $mapping[$key] = null;
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
        Runtime::getLogging()->log('find controller: requestId='.$requestId, Logging::TRACE);
        $mapping = $this->findMapping($requestId);
        if (null != $mapping['controller']) {
            // configured
            $definition = $mapping['controller'];
        } else {
            $definition = 'ZM'.CLassLoader::className($requestId.'Controller');
        }

        Runtime::getLogging()->log('controller definition: '.$definition, Logging::TRACE);
        if (null == ($controller = Beans::getBean($definition))) {
            $controller = Beans::getBean(ZMSettings::get('zenmagick.mvc.controller.default', 'ZMController'));
        }

        return $controller;
    }

    /**
     * Find and instantiate a view object for the given request id (and view id).
     *
     * <p>If no mapping is found, some sensible defaults will be used.</p>
     *
     * @param string requestId The request id.
     * @param string viewId Optional view id; defaults to <code>null</code> to use defaults.
     * @param mixed parameter Optional map of name/value pairs (or URL query format string)
     *  to further configure the view; default is <code>null</code>.
     * @return ZMView A <em>best match</em> view.
     */
    public function findView($requestId, $viewId=null, $parameter=null) {
        Runtime::getLogging()->log('find view: requestId='.$requestId.', viewId='.$viewId.', parameter='.$parameter, Logging::TRACE);
        $mapping = $this->findMapping($requestId, $viewId, $parameter);

        if (null === $mapping) {
            Runtime::getLogging()->log('no view found for: requestId='.$requestId.', viewId='.$viewId.', parameter='.$parameter, Logging::TRACE);
            $mapping = array();
        }
        if (!array_key_exists('template', $mapping) || null == $mapping['template']) {
            // default template name to requestId
            $mapping['template'] = $requestId;
        }
        $view = null;
        if (array_key_exists('view', $mapping) && null != $mapping['view']) {
            $view = Beans::getBean($mapping['view']);
        }
        if (null == $view) {
            // default view
            $view = Runtime::getContainer()->get('defaultView');
        }

        if (is_array($parameter)) {
            $parameter = http_build_query($parameter);
        }
        $layout = ((array_key_exists('layout', $mapping) && null !== $mapping['layout'])
              ? $mapping['layout'] : ZMSettings::get('zenmagick.mvc.view.defaultLayout', null));
        $definition = $parameter.'&template='.$mapping['template'].'&layout='.$layout.'&viewId='.$viewId;
        Runtime::getLogging()->debug('view: '.$definition);

        parse_str($definition, $properties);
        Beans::setAll($view, $properties);
        return $view;
    }

    /**
     * Get alias for the given request id.
     *
     * @param string requestId The given request id.
     * @param array The alias mapping or <code>null</code>.
     */
    public function getAlias($requestId) {
        if (array_key_exists($requestId, $this->mappings_['alias'])) {
            // make sure all keys are there
            if (!array_key_exists('requestId', $this->mappings_['alias'][$requestId])) {
                // might happen if there is only aliasing to add parameter
                $this->mappings_['alias'][$requestId]['requestId'] = $requestId;
            }
            if (!array_key_exists('parameter', $this->mappings_['alias'][$requestId])) {
                $this->mappings_['alias'][$requestId]['parameter'] = '';
            }
            return $this->mappings_['alias'][$requestId];
        }

        return null;
    }

}
