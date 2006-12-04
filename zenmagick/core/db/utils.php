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
 *
 * $Id$
 */
?>
<?php

    /**
     * Prepare/sanitize values for DB operations.
     *
     * @package net.radebatz.zenmagick.db
     * @param string value A value to be sanitized so that i can safely be used for SQL.
     * @return string A save version of the input value.
     */
    function _zm_db_prepare_input($value) { return addslashes(zen_db_prepare_input($value)); }

    /**
     * Var arg list of DB values.
     *
     * <p>Converts a variable number of arguments into a komma separated list (string).
     * All values will be in single quotes and sanitized before usage.</p>
     *
     * @package net.radebatz.zenmagick.db
     * @param mixed A mixed, variable number of parameters.
     * @return string The given parameters as komma separated list (in single quotes "'")
     */
    function zm_db_values($firstValue) {
        $args = func_get_args();

        $sql = '';
        $first = true;
        foreach ($args as $value) {
            if (!$first) $sql .= ", ";
            $sql .= "'" . zm_db_prepare_input($value) . "'";
            $first = false;
        }

        return $sql;
    }

    /**
     * <code>Array</code> version of <code>zm_db_values</code>
     *
     * <p>Converts the values in the given array into a komma separated list (string).
     * All values will be in single quotes and sanitized before usage.</p>
     *
     * @package net.radebatz.zenmagick.db
     * @param array An array of values.
     * @return string The values of the given array as komma separated list (in single quotes "'")
     */
    function zm_db_array($arr) {
        $fragment = '';
        foreach ($arr as $elem) {
            if ('' != $fragment) $fragment .= ', ';
            $fragment .= "'" . $elem . "'";
        }
        return $fragment;
    }

?>
