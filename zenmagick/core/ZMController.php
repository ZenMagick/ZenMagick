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
 * @package net.radebatz.zenmagick.controller
 * @abstract
 * @version $Id$
 */
class ZMController {
    var $globals_;
    var $responseView_;


    // create new instance
    function ZMController() {
    global $zm_request;
        $this->globals_ = array();

        // set up default behaviour
        $pageName = $zm_request->getPageName();
        $this->responseView_ = new ZMView($pageName, $pageName);
    }

    // create new instance
    function __construct() {
        $this->ZMController();
    }

    function __destruct() {
    }


    /****************** controller API */

    /**
     * Return the view name for the result view; 
     *
     * @return ZMView The <code>ZMView</code> for rendering the response.
     */
    function getResponseView() { return $this->responseView_; }
    /**
     * Set the response view.
     *
     * @param ZMView responseView The new response view.
     */
    function setResponseView($responseView) { $this->responseView_ = $responseView; }


    /**
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return <code>true</code> if the request was processed ok, <code>false</code> if not.
     */
    function process() { 
        foreach ($GLOBALS as $name => $instance) {
            if (zm_starts_with($name, "zm_")) {
                if (is_object($instance)) {
                    $this->exportGlobal($name, $instance);
                }
            }
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                return $this->processGet();
            case 'POST':
                return $this->processPost();
            default:
                die('Unsupported request method: ' . $_SERVER['REQUEST_METHOD']);
        }
    }

    /**
     * Process a HTTP GET request.
     */
    function processGet() {
    global $zm_crumbtrail;

        $zm_crumbtrail->addCrumb(zm_title(false));
        return true;
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
        $this->globals_[$name] = $instance;
    }

}

?>
