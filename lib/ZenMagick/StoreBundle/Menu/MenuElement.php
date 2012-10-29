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
namespace ZenMagick\StoreBundle\Menu;

/**
 * Basic menu element.
 *
 * @param author DerManoMann
 */
class MenuElement extends Node
{
    private $requestId;
    private $params;
    private $alias;

    /**
     * Create instance.
     *
     * @param string id Optional id; default is <code>null</code>.
     * @param string name Optional name; default is an empty string <code>''</code>.
     * @param string requestId Optional requestId; default is <code>null</code>.
     */
    public function __construct($id=null, $name='', $requestId=null)
    {
        parent::__construct($id, $name);
        $this->requestId = $requestId;
        $this->params = '';
        $this->alias = array();
    }

    /**
     * Set requestId.
     *
     * @param string requestId The requestId.
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * Get requestId.
     *
     * @return string The requestId.
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * Set params.
     *
     * @param string params The params.
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Get params.
     *
     * @return string The params.
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set alias.
     *
     * @param array alias The alias.
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * Get alias.
     *
     * @return array The alias.
     */
    public function getAlias()
    {
        return $this->alias;
    }

}
