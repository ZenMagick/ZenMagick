<?php
/*
 * ZenMagick - Smart e-commerce
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
 * Check for unique email address.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.validation
 */
class ZMUniqueEmailRule extends ZMRule
{
    /**
     * Create new required rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    public function __construct($name, $msg=null)
    {
        parent::__construct($name, "Email already in use.", $msg);
    }

    /**
     * Validate the given request data.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @param array data The data.
     * @return boolean <code>true</code> if the value for <code>$name</code> is valid, <code>false</code> if not.
     */
    public function validate($request, $data)
    {
        return empty($data[$this->getName()]) || !$this->container->get('accountService')->emailExists($data[$this->getName()]);
    }

    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    public function toJSString()
    {
        return '';
    }

}
