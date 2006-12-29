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
 * Date validation rule.
 *
 * @author mano
 * @package net.radebatz.zenmagick.validations.rules
 * @version $Id$
 */
class ZMDateRule extends ZMRule {
    var $regexp_;
    var $format_;


    /**
     * Create new date rule.
     *
     * @param string name The field name.
     * @param string regexp The date regexp.
     * @param string format The date format (eg: DD/MM/YYY)
     * @param string msg Optional message.
     */
    function ZMDateRule($name, $regexp, $format, $msg=null) {
        parent::__construct($name, "Please enter a valid date (%s).", $msg);

        $this->regexp_ = $regexp;
        $this->format_ = $format_;
    }

    /**
     * Create new date rule.
     *
     * @param string name The field name.
     * @param string regexp The date regexp.
     * @param string format The date format (eg: DD/MM/YYY)
     * @param string msg Optional message.
     */
    function __construct($name, $regexp, $format, $msg=null) {
        $this->ZMDateRule($name, $regexp, $format, $msg);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Validate the given request data.
     *
     * @param array req The request data.
     * @return bool <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    function validate($req) {
        // check regexp first
        if (!(array_key_exists($this->name_, $req) && eregi($this->regexp_, $req[$this->name_]))) {
            return false;
        }

        // day
        $day = "1";
        $dpos = strpos($this->format_, "DD");
        if (!(false === $dpos)) {
            $day = substr($date, $dpos, 2);
        }

        // month
        $month = "1";
        $mpos = strpos($format, "MM");
        if (!(false === $mpos)) {
            $month = substr($date, $mpos, 2);
        }

        // year
        $year = 1;
        $yspos = strpos($format, "Y");
        $yepos = strrpos($format, "Y");
        if (!(false === $yspos) && !(false === $yepos)) {
            $year = substr($date, $yspos, $yepos-$yspos+1);
        }

        return @checkdate($month, $day, $year);
    }


    /**
     * Return an appropriate error message.
     *
     * @return string Localized error message.
     */
    function getErrorMsg() {
        return zm_l10n_get((null != $this->msg_ ? $this->msg_ : $this->defaultMsg_), $this->name_, $this->format_);
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
