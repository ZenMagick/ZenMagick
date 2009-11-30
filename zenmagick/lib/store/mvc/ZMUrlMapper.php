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
 * Handle URL mappings.
 *
 * <p>The URL mappings connect the requested page (value of <em>ZM_PAGE_KEY</em>)
 * with a view (template) and optionally a controller to handle the request.</p>
 *
 * <p>The default behaviour for controller is to build the controller class using
 * the <em>ZM_PAGE_KEY</em> value.</p>
 *
 * <p>Furthermore, depending on the processing of a request in a controller, different
 * views may be returned. This is archived by mapping the actual template name not to
 * a request (<em>ZM_PAGE_KEY</em> value) or controller class, but to a logical key that
 * is used by the controller to lookup the actual view template.</p>
 *
 * <p>Finally, there are optional settings for form validation that are not used (yet).</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc
 * @version $Id$
 */
class ZMUrlMapper extends ZMObject {
    // global views; key is viewId
    private $globalViews_;
    // page specific views; key is page
    private $pageViews_;


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
        return ZMObject::singleton('UrlMapper');
    }

    /**
     * Set a mapping for the given page <code>$page</code>.
     *
     * <p>Supported <code>$viewInfo</code> keys (<em>viewDefinition</code>, <em>controllerDefinition</code> and <em>formClass</code> 
     *  allow either a bean definitions or a plain class name):</p>
     *
     * <dl>
     *  <dt>viewId</dt><dd>The view id. This is the key the controller will use to lookup a view template name; 
     *   default is the value of <code>$page</code>.</dd>
     *  <dt>view</dt><dd>The corresponding view template; this is the name of the view filename without the file extension;</dd>
     *   default is the value of <code>$page</code>.</dd>
     *  <dt>viewDefinition</dt><dd>The actual <code>ZMView</code> implementation class to use; default is 
     *   <code>ZMSettings::get('defaultViewClass')</code>.</dd>
     *  <dt>controllerDefinition</dt><dd>The class name for the controller to handle this page; default is <code>null</code> 
     *   to use <code>$page</code> to build a classname (see <code>ZMLoader::makeClassname(string)</code> for details).</dd>
     *  <dt>formId</dt><dd>Optional name of the form for automatic request validation; default is <code>null</code> for none.</dd>
     *  <dt>formDefinition</dt><dd>Optional form model definition; default is <code>null</code> for none.</dd>
     * </dl>
     *
     * <p>In the case of <code>$page</code> being <code>null</code>, at least <em>viewId</em> is required.</p>
     *
     * @param string page The page name; <code>null</code> may be used to lookup shared mappings.
     * @param array viewInfo View details; default is an empty array - <code>array()</code>.
     */
    public function setMappingInfo($page, $viewInfo=array()) {
        $mappingDefaults = array(
            'viewId' => $page,
            'template' => $page,
            'controllerDefinition' => null, // leave null here to first try building the class based on $page 
            'viewDefinition' => ZMSettings::get('defaultViewClass') . '#',
            'formId' => null,
            'formDefinition' => null
        );

        // merge with defaults
        $viewInfo = array_merge($mappingDefaults, $viewInfo);
        // need this to store the data
        $viewId = $viewInfo['viewId'];
        if (!isset($viewInfo['template']) && !empty($viewId)) {
            $viewInfo['template'] = $viewId;
        }
        // sanity check
        if (null == $page && (null == $viewInfo['template'] || null == $viewId)) {
            $msg = zm_l10n_get("invalid url mapping; page=%s, template=%s, viewId=%s", $page, $viewInfo['template'], $viewId);
            throw new ZMException($msg);
        }

        if (null == $page) {
            // global mapping
            $this->globalViews_[$viewId] = $viewInfo;
        } else {
            if (!isset($this->pageViews_[$page])) {
                $this->pageViews_[$page] = array();
            }
            $this->pageViews_[$page][$viewId] = $viewInfo;
        }
    }

    /**
     * Find the controller (class) mapped to the given page.
     *
     * <p>Unless explicitely configured, the controller class name will be
     * build from the given page name.</p>
     *
     * <p>If no page specific controller is found, an instance of 
     * <code>ZMSettings::get('zenmagick.mvc.controller.defaultClass')</code> will be returned.</p>
     *
     * @param string page The page name.
     * @return ZMController A controller instance to handle the request.
     */
    public function findController($page) {
        ZMLogging::instance()->log('find controller: page='.$page, ZMLogging::TRACE);
        $definition = null;
        if (array_key_exists($page, $this->pageViews_) 
            && array_key_exists($page, $this->pageViews_[$page]) && null != $this->pageViews_[$page][$page]['controllerDefinition']) {
            // configured
            $definition = $this->pageViews_[$page][$page]['controllerDefinition'];
        } else {
            $definition = ZMLoader::makeClassname($page.'Controller');
        }

        ZMLogging::instance()->log('controller definition: '.$definition, ZMLogging::TRACE);
        if (null == ($controller = ZMBeanUtils::getBean($definition))) {
            $controller = ZMBeanUtils::getBean(ZMSettings::get('zenmagick.mvc.controller.defaultClass', 'DefaultController'));
        }

        return $controller;
    }

    /**
     * Find a URL mapping for the given controller (and viewId).
     *
     * @param string page The page name.
     * @param string viewId The viewId; defaults to <code>null</code> to use the controller.
     * @param mixed parameter Optional map of name/value pairs (or URL query format string) 
     *  to further configure the view; default is <code>null</code>.
     * @return array A mapping or <code>null</code>.
     */
    public function findMapping($page, $viewId=null, $parameter=null) {
        ZMLogging::instance()->log('find mapping: page='.$page.', viewId='.$viewId.', parameter='.$parameter, ZMLogging::TRACE);

        $viewId = null != $viewId ? $viewId : $page;

        $viewInfo = null;

        // check controller
        if (isset($this->pageViews_[$page])) {
            $view = $this->pageViews_[$page];
            $viewInfo = (isset($view[$viewId]) ? $view[$viewId] : null);
        }

        if (null == $viewInfo) {
            // try global mappings
            $viewInfo = (isset($this->globalViews_[$viewId]) ? $this->globalViews_[$viewId] : null);
        }

        return $viewInfo;
    }

    /**
     * Get the view definition string for the given controller (and viewId).
     *
     * @param string page The page name.
     * @param string viewId The viewId; defaults to <code>null</code> to use the controller.
     * @param mixed parameter Optional map of name/value pairs (or URL query format string) 
     *  to further configure the view; default is <code>null</code>.
     * @return string A <em>best match</em> view definition.
     */
    public function getViewDefinition($page, $viewId=null, $parameter=null) {
        $viewInfo = $this->findMapping($page, $viewId, $parameter);

        if (null === $viewInfo) {
            ZMLogging::instance()->log('no view found for: page='.$page.', viewId='.$viewId.', parameter='.$parameter, ZMLogging::TRACE);
            // set some sensible defaults
            $viewInfo = array('template' => $page, 'viewDefinition' => ZMSettings::get('defaultViewClass'));
        }

        if (is_array($parameter)) {
            $parameter = http_build_query($parameter);
        }
        $definition = $viewInfo['viewDefinition'] . (false === strpos($viewInfo['viewDefinition'], '#') ? '#' : '&') . $parameter . '&template=' . $viewInfo['template'] . '&viewId=' . $viewInfo['viewId'];
        ZMLogging::instance()->log('view definition: '.$definition, ZMLogging::TRACE);
        return $definition;
    }

    /**
     * Clear all mappings.
     */
    public function clear() {
        $this->globalViews_ = array();
        $this->pageViews_ = array();
    }

}

?>
