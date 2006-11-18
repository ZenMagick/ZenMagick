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
 * @package net.radebatz.zenmagick
 * @abstract
 * @version $Id$
 */
class ZMRequestController {
    var $globals_;
    var $responseView_;


    // create new instance
    function ZMRequestController() {
    global $zm_request;
        $this->globals_ = array();

        // set up default behaviour
        $pageName = $zm_request->getPageName();
        $this->responseView_ = new ZMView($pageName, $pageName);
    }

    // create new instance
    function __construct() {
        $this->ZMRequestController();
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
     */
    function process() { 
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                return $this->processGet();
                break;
            case 'POST':
                return $this->processPost();
                break;
            default:
                die('Unsupported request method: ' . $_SERVER['REQUEST_METHOD']);
                break;
        }
    }

    // process a GET request
    function processGet() {
    global $zm_crumbtrail;

        $zm_crumbtrail->addCrumb(zm_title(false));
        return true;
    }

    // process a POST request
    function processPost() { return $this->processGet(); }

    // returns a name => instance hash of variables that
    // need to be exported into the view space
    function getGlobals() {
        return $this->globals_;
    }

    // utility method for subclasses
    function exportGlobal($name, &$instance) {
        if (null === $instance)
            return;
        $this->globals_[$name] = $instance;
    }

}

?>
