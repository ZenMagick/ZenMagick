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
 * Handle URL mappings.
 *
 * <p>URL mappings control the actual view being displayed after the controller
 * is finished with its part of the request processing. The mapping is used
 * by the controller method <code>findView(...)</code>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.rp
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
        $this->globalViews_ = array();
        $this->pageViews_ = array();
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
     * Set a mapping.
     *
     * <p>Supported <code>$viewInfo</code> keys:</p>
     * <dl>
     *  <dt>viewId</dt><dd>The view id. This is the key the controller will use to lookup a view; default is the value of <code>$page</code>.</dd>
     *  <dt>view</dt><dd>The corresponding view template; this is the name of the view filename without the file extension.</dd>
     *  <dt>class</dt><dd>The actual <code>ZMView</code> implementation class name to use; default is <em>PageView</em>.</dd>
     *  <dt>controller</dt><dd>The class name for the controller to handle this page; default is <code>null</code> 
     *   to use <code>$page</code> to build a classname (see <code>ZMLoader::makeClassname(string)</code> for details).</dd>
     *  <dt>formId</dt><dd>Optional name of the form for automatic request validation; default is <code>null</code> for none.</dd>
     *  <dt>formClass</dt><dd>Optional form model class; default is <code>null</code> for none.</dd>
     *  <dt>parameter</dt><dd>Optional URL arg style string or array map with additional parameters for the view class; default is <code>null</code>.</dd>
     * </dl>
     *
     * <p>In the case of <code>$page</code> being <code>null</code>, both <em>view</em> and <em>viewId</em> are required.</p>
     *
     * @param string page The page name; <code>null</code> may be used to lookup shared mappings.
     * @param array viewInfo View details.
     */
    public function setMappingInfo($page, $viewInfo) {
        $defaults = array('view' => $page, 'viewId' => $page, 'controller' => null, 'class' => 'PageView', 'formId' => null, 'formClass' => null, 'parameter' => null);
        // merge with defaults
        $viewInfo = array_merge($defaults, $viewInfo);
        // need this to store the data
        $viewId = $viewInfo['viewId'];
        // sanity check
        if (null == $page && (null == $viewInfo['view'] || null == $viewid)) {
            $msg = zm_l10n_get("invalid url mapping; page=%s, view=%s, viewId=%s", $page, $viewInfo['view'], $viewInfo['viewId']);
            throw ZMLoader::make('ZMException', $msg);
        }

        if (null === $page) {
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
     * Set a mapping.
     *
     * @param string page The page name; <code>null</code> may be used to lookup shared mappings.
     * @param string viewId The view id; this is the key the controller is using to lookup the view; default is <code>null</code>.
     * @param string view The mapped view name; default is <code>null</code> to default to the value of the parameter <em>page</em>.
     * @param string viewClass The view class to be used; default is <code>PageView</code>
     * @param mixed parameter Optional map of name/value pairs to further configure the view; default is <code>null</code>.
     * @param string controller Optional controller name; default is the value of the parameter <em>page</em>.
     * @deprecated Use setMappingInfo instead.
     */
    public function setMapping($page, $viewId=null, $view=null, $viewClass='PageView', $parameter=null, $controller=null) {
        if (null == $page && (null == $view || null == $viewId)) {
            throw ZMLoader::make('ZMException', "invalid url mapping");
        }
        $viewId = null != $viewId ? $viewId : $page;

        // first, build the view info
        $viewInfo = array();
        $viewInfo['view'] = (null != $view ? $view : $page);
        $viewInfo['class'] = $viewClass;
        $viewInfo['parameter'] = $parameter;
        $viewInfo['controller'] = $controller;

        if (null === $page) {
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
     * <code>DefaultController</code> will be * returned.</p>
     *
     * @param string page The page name.
     * @return ZMController A controller instance to handle the request.
     */
    public function findController($page) {
        $clazz = null;
        if (array_key_exists($page, $this->pageViews_) 
            && array_key_exists($page, $this->pageViews_[$page]) && null != $this->pageViews_[$page][$page]['controller']) {
            // class configured
            $clazz = $this->pageViews_[$page][$page]['controller'];
        } else {
            $clazz = ZMLoader::makeClassname($page.'Controller');
        }

        if (null == ($controller = ZMLoader::make($clazz))) {
            $controller = ZMLoader::make("DefaultController");
        }

        return $controller;
    }

    /**
     * Find a URL mapping for the given controller and viewId.
     *
     * @param string page The page name.
     * @param string viewId The viewId; defaults to <code>null</code> to use the controller.
     * @param mixed parameter Optional map of name/value pairs (or URL query format string) 
     *  to further configure the view; default is <code>null</code>.
     * @return ZMView The actual view to be used to render the response.
     */
    public function findView($page, $viewId=null, $parameter=null) {
        ZMLogging::instance()->log('find view: page='.$page.', viewId='.$viewId.', parameter='.$parameter, ZMLogging::TRACE);

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

        if (null == $viewInfo) {
            // set some sensible defaults
            $viewInfo = array('view' => $page, 'class' => 'PageView', 'parameter' => null);
        }

        $view = ZMLoader::make($viewInfo['class'], $viewInfo['view']);
        $view->setMappingId($viewId);
        $parameterMap = ZMTools::toArray($viewInfo['parameter']);
        $parameterMap = array_merge($parameterMap, ZMTools::toArray($parameter));
        ZMBeanUtils::setAll($view, $parameterMap);
        return $view;
    }

}

?>
