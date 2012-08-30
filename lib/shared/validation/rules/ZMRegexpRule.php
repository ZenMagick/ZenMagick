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
 * Regexp validation rules.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.validation.rules
 */
class ZMRegexpRule extends ZMRule {
    private $regexp_;


    /**
     * Create new regexp rule.
     *
     * @param string name The field name; default is <code>null</code>.
     * @param string regexp The regular expression; default is <code>null</code>.
     * @param string msg Optional message; default is <code>null</code>.
     */
    function __construct($name=null, $regexp=null, $msg=null) {
        parent::__construct($name, "%s is not valid.", $msg);
        $this->setRegexp($regexp);
    }


    /**
     * Set the regular expression.
     *
     * @param string regexp The regular expression.
     */
    public function setRegexp($regexp) {
        $this->regexp_ = $regexp;
    }

    /**
     * Get the regular expression.
     *
     * @ return string The regular expression.
     */
    public function getRegexp() {
        return $this->regexp_;
    }

    /**
     * Validate the given request data.
     *
     * @param zenmagick\http\Request request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the regular expression does match.
     */
    public function validate($request, $data) {
        return empty($data[$this->getName()]) || preg_match($this->regexp_, $data[$this->getName()]);
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    public function toJSString() {
        $js = "    new Array('regexp'";
        $js .= ",'".$this->getJSName()."'";
        $js .= ",'".addslashes($this->getErrorMsg())."'";
        $js .= ",".'"'.$this->regexp_.'"';
        $js .= ")";
        return $js;
    }

}
