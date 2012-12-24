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
    private $route;
    private $routeParameters;
    private $alias;

    /**
     * Create instance.
     *
     * @param string name Optional name; default is <code>null</code>.
     * @param string name Optional label; default is an empty string <code>''</code>.
     * @param string route Optional route id; default is <code>null</code>.
     */
    public function __construct($name=null, $label='', $route=null)
    {
        parent::__construct($name, $label);
        $this->route = $route;
        $this->routeParameters = array();
        $this->alias = array();
    }

    /**
     * Set Route Id.
     *
     * @param string route The route id.
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * Get route id.
     *
     * @return string The route id.
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set route parameters.
     *
     * @param string routeparameters The route params.
     */
    public function setRouteParameters($routeParameters)
    {
        $this->routeParameters = $routeParameters;
    }

    /**
     * Get route parameters.
     *
     * @return string The params.
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
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
