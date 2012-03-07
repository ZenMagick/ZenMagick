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
?>
<?php


/**
 * List validation rules.
 *
 * <p>Validate against a list of allowed values.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.validation.rules
 */
class ZMListRule extends ZMRule {
    private $values_;


    /**
     * Create new list rule.
     *
     * @param string name The field name; default is <code>null</code>.
     * @param mixed values The list of valid values as either a comma separated string or array; default is <code>null</code>.
     * @param string msg Optional message; default is <code>null</code>.
     */
    function __construct($name=null, $values=null, $msg=null) {
        parent::__construct($name, "%s is not valid.", $msg);
        $this->setValues($values);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set values.
     *
     * @param mixed values The list of valid values as either a comma separated string or array.
     */
    public function setValues($values) {
        $this->values_ = $values;
    }

    /**
     * Get values.
     *
     * @return mixed The list of valid values as either a comma separated string or array.
     */
    public function getValues() {
        return $this->values_;
    }

    /**
     * Validate the given request data.
     *
     * @param ZMRequest request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the regular expression does match.
     */
    public function validate($request, $data) {
        return empty($data[$this->getName()]) || ZMLangUtils::inArray($data[$this->getName()], $this->values_);
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    public function toJSString() {
        $quoted = array();
        foreach ($this->values_ as $value) {
            $quoted[] = "'".addslashes($value)."'";
        }
        $js = "    new Array('list'";
        $js .= ",'".$this->getJSName()."'";
        $js .= ",'".addslashes($this->getErrorMsg())."'";
        $js .= ",new Array(".implode(',', $quoted).")";
        $js .= ")";
        return $js;
    }

}
