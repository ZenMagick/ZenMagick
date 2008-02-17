<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Base DAO class.
 *
 * @author mano
 * @package org.zenmagick
 * @version $Id$
 */
class ZMDao extends ZMObject {
    var $db_;


    /**
     * Default c'tor.
     */
    function ZMDao() {
    global $zm_runtime;

        parent::__construct();

        $this->db_ =& $zm_runtime->getDB();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMDao();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Bind a list of values to a given SQL query.
     *
     * <p>Converts the values in the given array into a comma separated list of the specified type.</p>
     *
     * @param string sql The sql query to work on.
     * @param string bindName The name to bind the list to.
     * @param array values An array of values.
     * @param string type The value type; default is 'string'
     * @return string The sql with <code>$bindName</code> replaced with a properly formatted value list.
     */
    function bindValueList($sql, $bindName, $values, $type='string') {
        $fragment = '';
        foreach ($values as $value) {
            if ('' != $fragment) $fragment .= ', ';
            $fragment .= $this->db_->bindVars(":value", ":value", $value, $type);
        }

        return $this->db_->bindVars($sql, $bindName, $fragment, 'noquotestring');
    }

    /**
     * Bind object to a given SQL query.
     *
     * <p>This is based on introspection/reflection on the given object and the available
     * <code>getXXX()</code> or <code>isXXX()</code> methods.</p>
     * <p>SQL label must follow the listed convenctions:</p>
     * <ul>
     *  <li>label start with the prefix '<code>:</code>'</li>
     *  <li>label match the objetcs <code>getXXX()</code> method excl the <code>get</code> prefix</li>
     *  <li>label are suffixed with the data type with a semicolon '<code>;</code>' as separator</li>
     * </ul>
     *
     * <p>Examples:</p>
     * <ul>
     *  <li><code>:firstName;string</code> - maps to the <code>getFirstName()</code> method; data type string</li>
     *  <li><code>:dob;date</code> - maps to the <code>getDob()</code> method; data type date</li>
     *  <li><code>:newsletterSubscriber;integer</code> - maps to the <code>isNewsletterSubscriber()</code> method; data type integer</li>
     * </ul>
     *
     * @param string sql The sql to work on.
     * @param mixed obj The data object instance.
     * @return string The updated SQL query.
     */
    function bindObject($sql, $obj) {
        // prepare label
        preg_match_all('/:\w+;\w+/m', $sql, $matches);
        $labels = array();
        foreach ($matches[0] as $name) {
            $label = explode(';', $name);
            $labels[strtolower(str_replace(':', '', $label[0]))] = array($name, $label[1]);
        }

        // prepare methods
        $methods = get_class_methods($obj);

        // strip 'get' prefix and ignore other
        $getter = array();
        foreach ($methods as $method) {
            if (0 === strpos($method, 'get')) {
                $lcproperty = substr(strtolower($method), 3);
                $getter[$lcproperty] = $method;
            } else if (0 === strpos($method, 'is')) {
                $lcproperty = substr(strtolower($method), 2);
                $getter[$lcproperty] = $method;
            }
        }

        foreach ($getter as $lcproperty => $method) {
            if (isset($labels[$lcproperty])) {
                $label = $labels[$lcproperty];

                // execute getXXX()
                $value = call_user_func(array($obj, $method));

                // bind
                $sql = $this->db_->bindVars($sql, $label[0], $value, $label[1]);

                // unset so we end up with unmapped properties and/or labels ...
                unset($labels[$lcproperty]);
                unset($getter[$lcproperty]);
            }
        }

        return $sql;
    }

}

?>
