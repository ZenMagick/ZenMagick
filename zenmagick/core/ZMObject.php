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


/**
 * ZenMagick base class.
 *
 * <p>This is the base class for all ZenMagick classes and contains some very basic
 * stuff that might be usefull for most/all classes.</p>
 *
 * @author mano
 * @package org.zenmagick
 */
class ZMObject {
    var $loader_;

    /**
     * Default c'tor.
     */
    function ZMObject() {
    global $zm_loader;

        $this->loader_ =& $zm_loader;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMObject();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
    }


    /**
     * Shortcut to create new class instances.
     *
     * @param string name The class name.
     * @param var args A variable number of arguments that will be used as arguments for
     * @return mixed An instance of the class denoted by <code>$name</code> or <code>null</code>.
     */
    function create($name) {
        $args = func_get_args();
        array_shift($args);
        return $this->loader_->createWithArgs($name, $args);
    }
}

?>
