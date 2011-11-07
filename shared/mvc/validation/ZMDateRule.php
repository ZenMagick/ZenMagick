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
 * Date validation rule.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.validation
 */
class ZMDateRule extends ZMRule {
    private $format_;


    /**
     * Create new date rule.
     *
     * @param string name The field name.
     * @param string format The date format (eg: DD/MM/YYYY); if not set, the date/long format of the current locale will be used.
     * @param string msg Optional message.
     */
    function __construct($name, $format=null, $msg=null) {
        parent::__construct($name, "Please enter a valid date (%s).", $msg);
        $this->format_ = $format;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the format string.
     */
    public function getFormat() {
        return null != $this->format_ ? $this->format_ : $this->container->get('localeService')->getLocale()->getFormat('date', 'short');
    }

    /**
     * Validate the given request data.
     *
     * @param ZMRequest request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    public function validate($request, $data) {
        $value = $data[$this->getName()];
        return empty($value) || null != DateTime::createFromFormat($this->format_, $value);
    }


    /**
     * Return an appropriate error message.
     *
     * @return string Localized error message.
     */
    public function getErrorMsg() {
        return sprintf(_zm(null != $this->getMsg() ? $this->getMsg() : $this->getDefaultMsg()), $this->getName(), $this->getFormat());
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    public function toJSString() {
        //TODO: need to separate between DateTime format and ui format (as used by js validation)
        return '';
        $js = "    new Array('date'";
        $js .= ",'".$this->getJSName()."'";
        $js .= ",'".addslashes($this->getErrorMsg())."'";
        $js .= ",".'"'.$this->getFormat().'"';
        $js .= ")";
        return $js;
    }

}
