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
 * A view.
 *
 * @author mano
 * @package net.radebatz.uip.zenmagick
 * @version $Id$
 */
class ZMView extends ZMObject {
    var $controller_;

    // create new instance
    function ZMView() {
        parent::__construct();
    }

    // create new instance
    function __construct() {
        $this->ZMView();
    }

    function __destruct() {
    }


    /**
     * Generate view response.
     */
    function generate() { die('not implemented'); }

    /**
     * Set the controller for this view.
     *
     * @param controller ZMController The corresponding controller.
     */
    function setController($controller) { $this->controller_ =& $controller; }

    /**
     * Get the controller for this view.
     *
     * @return ZMController The corresponding controller.
     */
    function getController() { return $this->controller_; }

}

?>
