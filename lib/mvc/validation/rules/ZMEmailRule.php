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
 * Email validation rules.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.validation.rules
 */
class ZMEmailRule extends ZMRule {

    /**
     * Create new email rule.
     *
     * @param string name The field name; default is <code>null</code>.
     * @param string msg Optional message; default is <code>null</code>.
     */
    function __construct($name=null, $msg=null) {
        parent::__construct($name, "%s is not a valid email.", $msg);
    }


    /**
     * Build email regexp.
     * see: http://php.inspire.net.nz/manual/en/function.eregi.php
     *
     * @return string regexp for email matching.
     */
    protected function emailRegexp() {
        $atom = '[-a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~]';
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
     * @param ZMRequest request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    public function validate($request, $data) {
        if (!array_key_exists($this->getName(), $data)) {
            return true;
        }

        $email = $data[$this->getName()];

        return empty($data[$this->getName()]) || 1 == preg_match('/'.$this->emailRegexp().'/i', $email);
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
        $js .= ",".'"'.$this->emailRegexp().'"';
        $js .= ")";
        return $js;
    }

}
