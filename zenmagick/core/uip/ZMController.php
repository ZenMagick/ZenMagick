<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * Request controller base class.
 *
 * @author mano
 * @package net.radebatz.zenmagick.uip
 * @abstract
 * @version $Id$
 */
class ZMController extends ZMObject {
    var $page_;
    var $globals_;


    // create new instance
    function ZMController() {
    global $zm_request;

        parent::__construct();

        $this->globals_ = array();
        $this->page_ = $zm_request->getPageName();
    }

    // create new instance
    function __construct() {
        $this->ZMController();
    }

    function __destruct() {
    }


    /**
     * Checks if the current request can be handled by the controller or not.
     *
     * @return bool <code>true</code> if the controller can handle the request, <code>false</code> if not.
     */
    function validateRequest() {
    global $zm_theme;

        //TODO
        return is_subclass_of($this, "ZMController") || $zm_theme->isValidRequest();
    }


    /**
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMView A <code>ZMView</code> instance or <code>null</code>.
     */
    function process() { 
        foreach ($GLOBALS as $name => $instance) {
            if (zm_starts_with($name, "zm_")) {
                if (is_object($instance)) {
                    $this->exportGlobal($name, $GLOBALS[$name]);
                }
            }
        }

        $view = null;
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $view =& $this->processGet();
                break;
            case 'POST':
                $view =& $this->processPost();
                break;
            default:
                die('Unsupported request method: ' . $_SERVER['REQUEST_METHOD']);
        }

        if (null != $view) {
            $view->setController($this);
        }
        return $view;
    }


    /**
     * Process a HTTP GET request.
     */
    function processGet() {
    global $zm_crumbtrail;

        $zm_crumbtrail->addCrumb(zm_title(false));

        return new ZMThemeView($this->page_);
    }


    /**
     * Process a HTTP POST request.
     */
    function processPost() { return $this->processGet(); }


    /**
     * Returns a <code>name => object</code> hash of variables that need to be exported
     * into the theme space.
     *
     * @return array An associative array of <code>name => object</code> for all variables
     *  that need to be exported into the theme space.
     */
    function getGlobals() {
        return $this->globals_;
    }


    /**
     * Export the given object under the given name.
     * <p>Controller may use this method to make objects available to the response template/view.</p>
     *
     * @param string name The name under which the object should be visible.
     * @param mixed instance An object.
     */
    function exportGlobal($name, &$instance) {
        if (null === $instance)
            return;
        $this->globals_[$name] =& $instance;
    }

    /**
     * Returns the named global.
     *
     * @param string name The object name.
     * @param mixed instance An object instance or <code>null</code>.
     */
    function getGlobal($name) {
        return array_key_exists($name, $this->globals_) ? $this->globals_[$name] : null;
    }

}

?>
