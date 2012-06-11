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
 * Empty validation rules that can be used to wrap custom logic.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.validation.rules
 */
class ZMWrapperRule extends ZMRule {
    private $function_;
    private $javascript_;


    /**
     * Create new rule.
     *
     * @param string name The field name; default is <code>null</code>.
     * @param string msg Optional message; default is <code>null</code>.
     * @param mixed function The function name or array; default is <code>null</code>.
     */
    function __construct($name=null, $msg=null, $function=null) {
        parent::__construct($name, "Please enter a value for %s.", $msg);
        $this->function_ = null;
        $this->setJavaScript('');
        $this->setFunction($function);
    }


    /**
     * Set the validation function.
     *
     * <p>The function must implement the same siganture as <code>ZMRule::validate($request, $data)</code>.</p>
     *
     * @param string function The function name.
     */
    public function setFunction($function) {
        $this->function_ = $function;
    }

    /**
     * Set the JavaScript validation code.
     *
     * @param string javascript The javascript.
     */
    public function setJavaScript($javascript) {
        $this->javascript = $javascript;
    }

    /**
     * Validate the given request data.
     *
     * @param Request request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    public function validate($request, $data) {
        if (is_array($this->function_) && 2 == count($this->function_) && is_object($this->function_[0]) && is_string($this->function_[1])) {
            // expect object, method name
            $obj = $this->function_[0];
            $method = $this->function_[1];
            return $obj->$method($request, $data);
        } else if (function_exists($this->function_)) {
            return call_user_func($this->function_, $request, $data);
        }

        return true;
    }


    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    public function toJSString() {
        return $this->javascript_;
    }

}
