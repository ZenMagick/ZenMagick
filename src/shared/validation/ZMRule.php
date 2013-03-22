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

use ZenMagick\Base\ZMObject;

/**
 * Base class for validation rules.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.validation
 */
abstract class ZMRule extends ZMObject
{
    private $name;
    private $msg;
    private $defaultMsg;

    /**
     * Create new validation rule.
     *
     * @param string name The field name; default is <code>null</code>.
     * @param string defaultMsg The default error message; default is <code>null</code>.
     * @param string msg Optional custom error message; default is <code>null</code>.
     */
    public function __construct($name=null, $defaultMsg=null, $msg=null)
    {
        parent::__construct();
        $this->setName($name);
        $this->setDefaultMsg($defaultMsg);
        $this->setMsg($msg);
    }

    /**
     * Validate the given request data.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    abstract public function validate($request, $data);

    /**
     * Get the parameter name this rule is validating.
     *
     * @return string The name of the request parameter (GET/POST) this rule is testing.
     */
    public function getName()
    {
        return str_replace(array('[', ']'), '', $this->name);
    }

    /**
     * Set the parameter name this rule is validating.
     *
     * @param string name The name of the request parameter (GET/POST) this rule is testing.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the form field name this rule is validating.
     *
     * <p>This might be different in case of checkboxes or other input fields that allow multiple
     * values. In that case PPH requires to to suffix form field names with '<em>[]</em>'. However,
     * the request parameter will not have this suffix (rather being an array instead of a string).</p>
     *
     * @return string The form field name this rule is testing.
     */
    public function getJSName()
    {
        return $this->name;
    }

    /**
     * Get the custom error message.
     *
     * @return string The custom error message.
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * Get the default error message.
     *
     * @return string The default error message.
     */
    public function getDefaultMsg()
    {
        return $this->defaultMsg;
    }

    /**
     * Set a custom error message.
     *
     * @param string msg The custom error message.
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }

    /**
     * Set the default error message.
     *
     * @param string msg The default error message.
     */
    public function setDefaultMsg($msg)
    {
        $this->defaultMsg = $msg;
    }

    /**
     * Create JS validation call.
     *
     * <p>Returns an empty string.<p>
     *
     * @return string Formatted JavaScript .
     */
    public function toJSString()
    {
        return "";
    }

    /**
     * Return an appropriate error message.
     *
     * @return string Localized error message.
     */
    public function getErrorMsg()
    {
        return null != $this->msg ? _zm($this->msg) : sprintf(_zm($this->defaultMsg), ucwords(str_replace('_', ' ', $this->name)));
    }

}
