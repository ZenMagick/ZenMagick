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
namespace ZenMagick\Http;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\XmlFileLoader;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Routing\Loader\YamlLoader;
use ZenMagick\Http\Utils\ContextConfigLoader as HttpContextConfigLoader;


/**
 * HTTP event listener.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EventListener extends ZMObject {

    /**
     * Init things that need a request.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        $session = $request->getSession();

        $settingsService = $this->container->get('settingsService');

        // load application routing
        $routeResolver = $this->container->get('routeResolver');
        $routeFiles = array(Runtime::getApplicationPath().'/config/routing.xml');
        $routeFiles = array_merge($routeFiles, (array)$settingsService->get('zenmagick.http.routing.addnRouteFiles'));
        $routeLoader = new XmlFileLoader(new FileLocator());

        foreach ($routeFiles as $routeFile) {
            if (file_exists($routeFile)) {
                $routeCollection = $routeLoader->load($routeFile);
                $routeResolver->getRouter()->getRouteCollection()->addCollection($routeCollection);
            }
        }

        // load additional routing
        $contextConfigLoader = $this->container->get('contextConfigLoader');
        if ($contextConfigLoader instanceof HttpContextConfigLoader) {
            $routingLoader = new YamlLoader();
            $routeResolver = $this->container->get('routeResolver');
            foreach ($contextConfigLoader->getRouting() as $routing) {
                foreach ($routing as $id => $info) {
                    if (!array_key_exists('pattern', $info) && null != ($route = $routeResolver->getRouteForId('product_info'))) {
                        // merge options and defaults
                        if (array_key_exists('defaults', $info)) {
                            $route->addOptions($info['defaults']);
                        }
                        if (array_key_exists('options', $info)) {
                            $route->addOptions($info['options']);
                        }
                        unset ($routing[$id]);
                    }
                }

                $routeCollection = $routingLoader->load($routing);
                $routeResolver->getRouter()->getRouteCollection()->addCollection($routeCollection);
            }
        }

        // @todo This competes with admin EventListener!
        if (null != ($userSession = $this->container->get('userSession'))) {
            if (null != ($localeCode = $userSession->getLocaleCode())) {
                // init with user locale
                $request->setLocale($localeCode);
            }
        }
    }

}
