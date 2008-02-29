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
 * Regexp validation rules.
 *
 * @author mano
 * @package org.zenmagick.validations.rules
 * @version $Id$
 */
class ZMRegexpRule extends ZMRule {
    var $regexp_;


    /**
     * Create new regexp rule.
     *
     * @param string name The field name.
     * @param string regexp The regular expression.
     * @param string msg Optional message.
     */
    function ZMRegexpRule($name, $regexp, $msg=null) {
        parent::__construct($name, "%s is not valid.", $msg);
        $this->regexp_ = $regexp;
    }

    /**
     * Create new regexp rule.
     *
     * @param string name The field name.
     * @param string regexp The regular expression.
     * @param string msg Optional message.
     */
    function __construct($name, $regexp, $msg=null) {
        $this->ZMRegexpRule($name, $regexp, $msg);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Validate the given request data.
     *
     * @param array req The request data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    function validate($req) {
        return array_key_exists($this->name_, $req) && eregi($this->regexp_, $req[$this->name_]);
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    function toJSString() {
        $js = "    new Array('regexp'";
        $js .= ",'".$this->name_."'";
        $js .= ",'".addslashes($this->getErrorMsg())."'";
        $js .= ",".'"'.$this->regexp_.'"';
        $js .= ")";
        return $js;
    }

}

?>
