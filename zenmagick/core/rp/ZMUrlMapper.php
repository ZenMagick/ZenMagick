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


define('_ZM_DEFAULT_MAPPING_ID', '_zm_default_mapping_key');

/**
 * Handle URL mappings.
 *
 * <p>URL mappings control the actual view being displayed after the controller
 * is finished with its part of the request processing. The mapping is used
 * by the controller method <code>findView(...)</code>.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp
 * @version $Id$
 */
class ZMUrlMapper extends ZMObject {
    var $mapping_;


    /**
     * Default c'tor.
     */
    function ZMUrlMapper() {
        parent::__construct();

        $this->mapping_ = array();
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
     * Add new mapping.
     *
     * @param string controller The controller; <code>null</code> may be used for shared mappings.
     * @param string view The actual view name; default is <code>null</code> to default to the controller name.
     * @param string id The mapping id (this will be used as parameter for <code>findView(..)</code>); default is
     *  <code>null</code> to default to the controller name.
     * @param bool redirect Optional flag for redirect views; default is <code>false</code>.
     * @param bool secure Optional flag for secure views; default is <code>false</code>.
     */
    function addMapping($controller, $view=null, $id=null, $redirect=false, $secure=false) {
        if (null === $controller) {
            $controller = _ZM_DEFAULT_MAPPING_ID;
        }
        if (null === $view) {
            $view = $controller;
        }
        if (null === $id) {
            $id = _ZM_DEFAULT_MAPPING_ID;
        }
        if (isset($this->mappings_[$controller])) {
            $map = $this->mappings_[$controller];
        } else {
            $map = array();
        }
        $map[$id] = array('view' => $view, 'redirect' => $redirect, 'secure' => $secure);
        $this->mappings_[$controller] = $map;
    }

    /**
     * Find mapping for the given controller and id.
     *
     * @param string controller The controller; <code>null</code> may be used for shared mappings.
     * @param string id The mapping id (this will be used as parameter for <code>findView(..)</code>); default is
     *  <code>null</code> to default to the controller name.
     * @return ZMView The actual view to be used to render the response.
     */
    function &getView($controller, $id=null) {
        if (isset($this->mappings_[$controller])) {
            $map = $this->mappings_[$controller];
        } else {
            // try defaults right away...
            $map = $this->mappings_[_ZM_DEFAULT_MAPPING_ID];
        }
        if (null === $id) {
            $id = _ZM_DEFAULT_MAPPING_ID;
        }

        if (isset($map[$id])) {
            // return mapping
            return $this->_mkView($map[$id]);
        } else {
            if (isset($this->mappings_[$controller])) {
                // try defaults
                $map = $this->mappings_[_ZM_DEFAULT_MAPPING_ID];
                if (isset($map[$id])) {
                    // return default
                    return $this->_mkView($map[$id]);
                }
            }
        }
      
        // make id===controller behave the same as id===nul
        if ($controller == $id && isset($this->mappings_[$controller])) {
            $map = $this->mappings_[$controller];
            if (isset($map[_ZM_DEFAULT_MAPPING_ID])) {
                return $this->_mkView($map[_ZM_DEFAULT_MAPPING_ID]);
            }
        }

        //zm_log('no URL mapping found for: controller: '.$controller.'; id: '.$id, 1);
        return $this->create("PageView", $controller);
    }

    /**
     * Create new view based on the given parameter.
     *
     * @param array mapping A mapping.
     * @return ZMView A view.
     */
    function &_mkView($mapping) {
        if ($mapping['redirect']) {
            return $this->create("RedirectView", $mapping['view'], $mapping['secure']);
        } else {
            return $this->create("PageView", $mapping['view']);
        }
    }

}

?>
