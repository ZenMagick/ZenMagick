<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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


/**
 * Field match rule.
 *
 * <p>This is mostly to match new and confirm password.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.validation.rules
 */
class ZMFieldMatchRule extends ZMRule {
    private $other_;


    /**
     * Create new field match rule.
     *
     * @param string name The field name; default is <code>null</code>.
     * @param string other The other fields name; default is <code>null</code>.
     * @param string msg Optional message; default is <code>null</code>.
     */
    function __construct($name=null, $other=null, $msg=null) {
        parent::__construct($name, "%s and %s must match.", $msg);
        $this->setOther($other);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the other field name.
     *
     * @param string other The other fields name.
     */
    public function setOther($other) {
        $this->other_ = $other;
    }

    /**
     * Get the other field name.
     *
     * @return string The other fields name.
     */
    public function getOther() {
        return $this->other_;
    }

    /**
     * Validate the given request data.
     *
     * @param ZMRequest request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    public function validate($request, $data) {
        return empty($data[$this->getName()]) || empty($data[$this->other_]) || ($data[$this->getName()] == $data[$this->other_]);
    }


    /**
     * Return an appropriate error message.
     *
     * @return string Localized error message.
     */
    public function getErrorMsg() {
        return sprintf(_zm((null != $this->getMsg() ? $this->getMsg() : $this->getDefaultMsg())), $this->getName(), $this->other_);
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    public function toJSString() {
        $js = "    new Array('fieldMatch'";
        $js .= ",'".$this->getJSName()."'";
        $js .= ",'".addslashes($this->getErrorMsg())."'";
        $js .= ",'".$this->other_."'";
        $js .= ")";
        return $js;
    }

}
