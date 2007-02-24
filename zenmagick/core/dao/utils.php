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
     * Create a list of values as SQL fragment.
     *
     * <p>Converts the values in the given array into a komma separated list.</p>
     *
     * @package net.radebatz.zenmagick.dao
     * @param string sql The sql to work on.
     * @param string bindName The name to bind the list to.
     * @param string type The value type.
     * @param array values An array of values.
     * @return string The values of the given array as komma separated list (in single quotes "'")
     */
    function zm_db_value_list($sql, $bindName, $values, $type) {
    global $zm_runtime;

        $db = $zm_runtime->getDB();

        $fragment = '';
        foreach ($values as $value) {
            if ('' != $fragment) $fragment .= ', ';
            $fragment .= $db->bindVars(":value", ":value", $value, $type);
        }

        return $db->bindVars($sql, $bindName, $fragment, 'noquotestring');
    }

?>
