<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Protions Copyright (c) 2003 The zen-cart developers
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
 * Base sorter.
 *
 * @author mano
 * @package net.radebatz.zenmagick.sort
 * @version $Id$
 */
class ZMSort {


    // create new instance
    function ZMSort() {
    }

    // create new instance
    function __construct() {
        $this->ZMSort();
    }

    function __destruct() {
    }


    /** Sort API */

    // is sort active
    function isActive() { return true; }

    // sort the given list
    function sort($list) { return $list; }

}

?>
