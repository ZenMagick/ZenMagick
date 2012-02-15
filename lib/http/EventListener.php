<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace zenmagick\http;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\XmlFileLoader;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\http\sacs\SacsManager;
use zenmagick\http\routing\loader\YamlLoader;
use zenmagick\http\utils\ContextConfigLoader as HttpContextConfigLoader;


/**
 * HTTP event listener.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EventListener extends ZMObject {

    /**
     * Additional config loading.
     */
    public function onInitConfigDone($event) {
        $urlMappings = Runtime::getApplicationPath().'/config/url_mappings.yaml';
        if ($this->container->has('urlManager') && file_exists($urlMappings)) {
            // mvc mappings
            $this->container->get('urlManager')->load(file_get_contents(Runtime::getApplicationPath().'/config/url_mappings.yaml'), false);
        }
        // sacs mappings
        $this->container->get('sacsManager')->load(Runtime::getApplicationPath().'/config/sacs_mappings.yaml', false);
        //TODO: use tag
        $this->container->get('sacsManager')->loadProviderMappings(Runtime::getSettings()->get('zenmagick.http.sacs.mappingProviders', array()));
    }

    /**
     * Init things that need a request.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        $session = $request->getSession();

        $routeResolver = $this->container->get('routeResolver');

        // load application routing
        $appRoutingFile = Runtime::getApplicationPath().'/config/routing.xml';
        if (file_exists($appRoutingFile)) {
            $appRoutingLoader = new XmlFileLoader(new FileLocator());
            $appRouterCollection = $appRoutingLoader->load($appRoutingFile);
            $routeResolver->getRouter()->getRouteCollection()->addCollection($appRouterCollection);
        }

        // adjust front controller parameter
        if ($request->getFrontController() != Runtime::getSettings()->get('zenmagick.http.request.handler')) {
             Runtime::getSettings()->set('zenmagick.http.request.handler', $request->getFrontController());
        }

        // load additional routing
        $contextConfigLoader = $this->container->get('contextConfigLoader');
        if ($contextConfigLoader instanceof HttpContextConfigLoader) {
            foreach ($contextConfigLoader->getRouting() as $routing) {
                $routingLoader = new YamlLoader();
                $routerCollection = $routingLoader->load($routing);
                $routeResolver->getRouter()->getRouteCollection()->addCollection($routerCollection);
            }
        }

        if (null != ($userSession = $session->getUserSession())) {
            if (null != ($locale = $userSession->getLocaleCode())) {
                // init with user locale
                $localeService = $this->container->get('localeService');
                $localeService->init($locale, null, true);
            }
        }
    }

}
