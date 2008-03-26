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
 * Base class for validation rules.
 *
 * @author mano
 * @package org.zenmagick.validation
 * @version $Id$
 */
class ZMRule {
    var $name_;
    var $msg_;
    var $defaultMsg_;


    /**
     * Create new validation rule.
     *
     * @param string name The field name.
     * @param string defaultMsg The default error message.
     * @param string msg Optional custom error message.
     */
    function ZMRule($name, $defaultMsg, $msg=null) {
        $this->name_ = $name;
        $this->defaultMsg_ = $defaultMsg;
        $this->msg_ = $msg;
    }

    /**
     * Create new validation rule.
     *
     * @param string name The field name.
     * @param string defaultMsg The default error message.
     * @param string msg Optional custom error message.
     */
    function __construct($name, $defaultMsg, $msg=null) {
        $this->ZMRule($name, $defaultMsg, $msg);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
    }


    /**
     * Validate the given request data.
     *
     * @param array req The request data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    function validate($req) {
        die("can't use ZMRule directly");
    }


    /**
     * Get the field name this rule is validating.
     *
     * @return string The name of the data element this rule is testing.
     */
    function getName() {
        return $this->name_;
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    function toJSString() {
        die("not implemented");
    }


    /**
     * Return an appropriate error message.
     *
     * @return string Localized error message.
     */
    function getErrorMsg() {
        return null != $this->msg_ ? $this->msg_ : zm_l10n_get($this->defaultMsg_, ucwords(str_replace('_', ' ', $this->name_)));
    }

}

?>
