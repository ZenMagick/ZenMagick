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
 * Max field length validation rule based on the database column length.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.validation
 */
class ZMMaxFieldLengthRule extends ZMRule
{
    private $table;
    private $column;
    private $max;

    /**
     * Create new min length rule.
     *
     * @param string name The field name.
     * @param string table The database table.
     * @param string column The table column.
     * @param string msg Optional message.
     */
    public function __construct($name, $table, $column, $msg=null)
    {
        parent::__construct($name, "%s must not be longer than %s characters.", $msg);
        $this->table = $table;
        $this->column = $column;
        $this->max = -1;
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
        return (!isset($data[$this->getName()]) || empty($data[$this->getName()]) || $this->getMaxFieldLength() >= strlen(trim($data[$this->getName()])));
    }

    /**
     * Get the field length.
     *
     * @return int The max field length.
     */
    protected function getMaxFieldLength()
    {
        if (0 > $this->max) {
            $this->max = $this->container->get('templateManager')->getFieldLength($this->table, $this->column);
        }

        return $this->max;
    }

    /**
     * Return an appropriate error message.
     *
     * @return string Localized error message.
     */
    public function getErrorMsg()
    {
        return sprintf((null != $this->getMsg() ? $this->getMsg() : $this->getDefaultMsg()), $this->getName(), $this->getMaxFieldLength());
    }

    /**
     * Create JS validation call.
     *
     * @return string Formatted JavaScript .
     */
    public function toJSString()
    {
        $js = "    new Array('max'";
        $js .= ",'".$this->getJSName()."'";
        $js .= ",'".addslashes($this->getErrorMsg())."'";
        $js .= ",".$this->getMaxFieldLength();
        $js .= ")";

        return $js;
    }

}
