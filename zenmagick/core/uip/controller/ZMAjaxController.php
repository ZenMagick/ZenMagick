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
 * Request controller for ajax requests.
 *
 * @author mano
 * @package net.radebatz.zenmagick.uip
 * @version $Id$
 */
class ZMAjaxController extends ZMController {

    /**
     * Default c'tor.
     */
    function ZMAjaxController() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMAjaxController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
    }


    /**
     * Process a HTTP GET request.
     */
    function processGet() {
        return null;
    }


    /**
     * Set the response content type.
     *
     * @param string type The content type.
     * @param string charset Optional charset; default is utf-8.
     */
    function setContentType($type, $charset="utf-8") {
        header("Content-Type: " . $type . "; charset=" . $charset);
    }

}

?>
