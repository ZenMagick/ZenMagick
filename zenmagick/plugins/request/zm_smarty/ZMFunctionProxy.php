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
 * Smarty funciton proxy.
 *
 * @package org.zenmagick.plugins.zm_smarty
 * @author DerManoMann
 * @version $Id$
 */
class ZMFunctionProxy {

    /**
     * Acts as proxy for all ZenMagick functions to be used by templates.
     *
     * @param string method The function to call.
     * @param array args The function arguments.
     */
    public function __call($method, $args) {
        if (function_exists('zm_'.$method)) {
            $method = 'zm_'.$method;
        }
        switch (count($args)) {
            case 0:
                return $method();
                break;
            case 1:
                return $method($args[0]);
                break;
            case 2:
                return $method($args[0], $args[1]);
                break;
            case 3:
                return $method($args[0], $args[1], $args[2]);
                break;
            case 4:
                return $method($args[0], $args[1], $args[2], $args[3]);
                break;
            case 5:
                return $method($args[0], $args[1], $args[2], $args[3], $args[4]);
                break;
            case 6:
                return $method($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
                break;
            default:
                zm_backtrace('unsupported number of arguments');
        }
    }

}

?>
