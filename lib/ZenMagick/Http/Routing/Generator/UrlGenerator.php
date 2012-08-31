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
namespace ZenMagick\http\routing\generator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Url generator that allows to override requirements.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class UrlGenerator extends \Symfony\Component\Routing\Generator\UrlGenerator implements ContainerAwareInterface {
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container=null) {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function generate($name, $parameters=array(), $absolute=false, $requirements=array()) {
        // try alias first
        $alias = (array) $this->container->get('settingsService')->get('zenmagick.http.routing.alias');
        if (array_key_exists($name, $alias)) {
            $name = $alias[$name];
        }

        if (null === $route = $this->routes->get($name)) {
            throw new RouteNotFoundException(sprintf('Route "%s" does not exist.', $name));
        }

        if (!isset($this->cache[$name])) {
            $this->cache[$name] = $route->compile();
        }

        $requirements = array_merge($route->getRequirements(), $requirements);
        return $this->doGenerate($this->cache[$name]->getVariables(), $route->getDefaults(), $requirements, $this->cache[$name]->getTokens(), $parameters, $name, $absolute);
    }

}
