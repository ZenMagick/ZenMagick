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
 * Min max length validation rules.
 *
 * @author mano
 * @package net.radebatz.zenmagick.validations.rules
 * @version $Id$
 */
class ZMMinRule extends ZMRule {
    var $min_;


    // create new instance; msg is localized message
    function ZMMinRule($name, $min, $msg=null) {
        parent::__construct($name, "%s must be at leat %s characters long.", $msg);
        $this->min_ = $min;
    }

    // create new instance
    function __construct($name, $min, $msg=null) {
        $this->ZMMinRule($name, $min, $msg);
    }

    function __destruct() {
    }


    /**
     * Validate the given request data.
     *
     * @param array req The request data.
     * @return bool <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    function validate($req) {
        return !array_key_exists($this->name_, $req) || $this->min_ <= strlen(trim($req[$this->name_]));
    }


    /**
     * Return an appropriate error message.
     *
     * @return string Localized error message.
     */
    function getErrorMsg() {
        return zm_l10n_get((null != $this->msg_ ? $this->msg_ : $this->defaultMsg_), $this->name_, $this->min_);
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    function toJSString() {
        $js = "    new Array('min'";
        $js .= ",'".$this->name_."'";
        $js .= ",'".addslashes($this->getErrorMsg())."'";
        $js .= ",".$this->min_;
        $js .= ")";
        return $js;
    }

}

?>
