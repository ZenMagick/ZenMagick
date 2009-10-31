<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * Min/max length validation rule.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.validation.rules
 * @version $Id: ZMMinRule.php 2158 2009-04-16 01:34:04Z dermanomann $
 */
class ZMMinMaxRule extends ZMRule {
    private $min_;
    private $max_;


    /**
     * Create new min/max length rule.
     *
     * @param string name The field name.
     * @param int min The minimun length; default is <em>1</em>.
     * @param int max The maximum length; default is <em>0</em> for unlimited.
     * @param string msg Optional message.
     */
    function __construct($name, $min=1, $max=0, $msg=null) {
        parent::__construct($name, "%s must be between %s and %s characters long.", $msg);
        $this->min_ = $min;
        $this->max_ = $max;
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
    public function validate($req) {
        return !array_key_exists($this->getName(), $req) || empty($req[$this->getName()])
            || $this->min_ <= strlen(trim($req[$this->getName()]))
            || (0 != $this->max_ && $this->max_ < strlen(trim($req[$this->getName()])));
    }


    /**
     * Return an appropriate error message.
     *
     * @return string Localized error message.
     */
    public function getErrorMsg() {
        return zm_l10n_get((null != $this->getMsg() ? $this->getMsg() : $this->getDefaultMsg()), $this->getName(), $this->min_, $this->max_);
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    public function toJSString() {
        $js = "    new Array('min'";
        $js .= ",'".$this->getJSName()."'";
        $js .= ",'".addslashes($this->getErrorMsg())."'";
        $js .= ",".$this->min_;
        $js .= ")";
        return $js;
    }

}

?>
