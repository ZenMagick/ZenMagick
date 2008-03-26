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
 * Empty validation rules that can be used to wrap custom logic.
 *
 * @author mano
 * @package org.zenmagick.validation.rules
 * @version $Id$
 */
class ZMWrapperRule extends ZMRule {
    var $function_;
    var $javascript_;


    /**
     * Create new rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    function ZMWrapperRule($name, $msg=null) {
        parent::__construct($name, "Please enter a value for %s.", $msg);

        $this->function_ = null;
        $this->javascript_ = '';
    }

    /**
     * Create new rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    function __construct($name, $msg=null) {
        $this->ZMWrapperRule($name, $msg);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the validation function.
     *
     * <p>The function must implement the same siganture as <code>validate($req)</code>.</p>
     *
     * @param string fkt The function name.
     */
    function setFunction($function) {
        $this->function_ = $function;
    }

    /**
     * Set the JavaScript validation code.
     *
     * @param string javascript The javascript.
     */
    function setJavaScript($javascript) {
        $this->javascript = $javascript;
    }

    /**
     * Validate the given request data.
     *
     * @param array req The request data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    function validate($req) {
        if (function_exists($this->function_)) {
            return call_user_func($this->function_, $req);
        }

        return true;
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    function toJSString() {
        return $this->javascript_;
    }

}

?>
