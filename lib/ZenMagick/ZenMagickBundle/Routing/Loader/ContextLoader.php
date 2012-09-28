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

namespace ZenMagick\ZenMagickBundle\Routing\Loader;

use ZenMagick\Base\Utils\ContextConfigLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ContextLoader implements LoaderInterface
{
    private $loaded = false;
    protected $contextConfigLoader;

    public function __construct(ContextConfigLoader $contextConfigLoader) {
        $this->contextConfigLoader = $contextConfigLoader;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add this loader twice');
        }

        $routes = new RouteCollection();

        $contextConfigLoader = $this->contextConfigLoader;
        foreach ($contextConfigLoader->getRouting() as $routing) {
            foreach ($routing as $id => $info) {
                if (array_key_exists('pattern', $info)) {
                    $route = new Route($info['pattern']);
                    // merge options and defaults
                    if (array_key_exists('defaults', $info)) {
                        $route->addDefaults($info['defaults']);
                    }
                    if (array_key_exists('options', $info)) {
                        $route->addOptions($info['options']);
                    }
                    if (array_key_exists('requirements', $info)) {
                        $route->addRequirements($info['requirements']);
                    }
                }
                $routes->add($id, $route);
            }
        }

        // @todo use prefix in context files and always load them all (not
        // context specific)
        if ($contextConfigLoader->getContext() == 'admin') {
            $routes->addPrefix('/admin');
        }
        return $routes;
    }

    public function supports($resource, $type = null) {
        return 'context' === $type;
    }

    public function getResolver() {
    }

    public function setResolver(LoaderResolverInterface $resolver) {
    }
}
