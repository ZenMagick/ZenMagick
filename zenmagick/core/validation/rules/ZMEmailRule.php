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
 * Email validation rules.
 *
 * @author mano
 * @package org.zenmagick.validation.rules
 * @version $Id$
 */
class ZMEmailRule extends ZMRule {


    /**
     * Create new email rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    function ZMEmailRule($name, $msg=null) {
        parent::__construct($name, "%s is not a valid email.", $msg);
    }

    /**
     * Create new email rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    function __construct($name, $msg=null) {
        $this->ZMEmailRule($name, $msg);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /** 
     * Build email regexp.
     * see: http://php.inspire.net.nz/manual/en/function.eregi.php
     *
     * @return string regexp for email matching.
     */
    function _emailRegexp() {
        $atom = '[-a-zA-Z0-9!#$%&\'*+/=?^_`{|}~]';
        $domain = '([a-zA-Z0-9]([-a-zA-Z0-9]*[a-zA-Z0-9]+)?)';

        $regexp = '^' . $atom . '+' .              // One or more atom characters.
                  '(\.' . $atom . '+)*'.           // Followed by zero or more dot separated sets of one or more atom characters.
                  '@'.                             // Followed by an "at" character.
                  '(' . $domain . '{1,63}\.)+'.    // Followed by one or max 63 domain characters (dot separated).
                  $domain . '{2,63}'.              // Must be followed by one set consisting a period of two
                  '$';                             // or max 63 domain characters.

        return $regexp;
    }

    /**
     * Validate the given request data.
     *
     * @param array req The request data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    function validate($req) {
        if (!array_key_exists($this->name_, $req)) {
            return true;
        }

        $email = $req[$this->name_];

        return empty($req[$this->name_]) || eregi($this->_emailRegexp(), $email) && zen_validate_email($email);
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
        $js .= ",".'"'.$this->_emailRegexp().'"';
        $js .= ")";
        return $js;
    }

}

?>
