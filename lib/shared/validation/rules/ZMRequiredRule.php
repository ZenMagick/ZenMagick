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
 * Required validation rules.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.validation.rules
 */
class ZMRequiredRule extends ZMRule {

    /**
     * Create new required rule.
     *
     * <p>If a list of names is specified, validation is considered teh existence of at least one.
     *
     * @param mixed name The field name or a list (either an array or comma separated string) of names; default is <code>null</code>.
     * @param string msg Optional message.
     */
    function __construct($name=null, $msg=null) {
        parent::__construct($name, "Please enter a value for %s.", $msg);
    }


    /**
     * {@inheritDoc}
     */
    public function setName($name) {
        if (is_array($name)) {
            $name = implode(',', $name);
        }
        parent::setName($name);
    }

    /**
     * Validate the given request data.
     *
     * @param zenmagick\http\Request request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    public function validate($request, $data) {
        foreach (explode(',', $this->getName()) as $name) {
            if (array_key_exists($name, $data) && !empty($data[$name])) {
                return true;
            }
        }
        return false;
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    public function toJSString() {
        $js = "    new Array('required'";
        $js .= ",'".$this->getJSName()."'";
        $js .= ",'".addslashes($this->getErrorMsg())."'";
        $js .= ")";
        return $js;
    }

}
