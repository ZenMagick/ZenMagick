<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.validation
 */
class ZMMinRule extends ZMRule {
    private $min_;


    /**
     * Create new min length rule.
     *
     * @param string name The field name.
     * @param int min The minimun length.
     * @param string msg Optional message.
     */
    public function __construct($name, $min, $msg=null) {
        parent::__construct($name, "%s must be at least %s characters long.", $msg);
        $this->min_ = $min;
    }


    /**
     * Validate the given request data.
     *
     * @param ZMRequest request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    public function validate($request, $data) {
        return empty($data[$this->getName()]) || (!array_key_exists($this->getName(), $data) || $this->min_ <= strlen(trim($data[$this->getName()])));
    }


    /**
     * Return an appropriate error message.
     *
     * @return string Localized error message.
     */
    public function getErrorMsg() {
        return sprintf(_zm(null != $this->getMsg() ? $this->getMsg() : $this->getDefaultMsg()), $this->getName(), $this->min_);
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
