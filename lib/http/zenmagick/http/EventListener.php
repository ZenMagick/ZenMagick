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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\http\sacs\SacsManager;
use zenmagick\http\routing\loader\YamlLoader;
use zenmagick\http\utils\ContextConfigLoader as HttpContextConfigLoader;


/**
 * HTTP event listener.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.http
 */
class EventListener extends ZMObject {

    /**
     * Listen to bootstrap.
     */
    public function onBootstrapDone($event) {
        // mvc mappings
        \ZMUrlManager::instance()->load(file_get_contents(\ZMFileUtils::mkPath(Runtime::getApplicationPath(), 'config', 'url_mappings.yaml')), false);
        // sacs mappings
        $this->container->get('sacsManager')->load(\ZMFileUtils::mkPath(Runtime::getApplicationPath(), 'config', 'sacs_mappings.yaml'), false);
        $this->container->get('sacsManager')->loadProviderMappings(Runtime::getSettings()->get('zenmagick.http.sacs.mappingProviders', array()));
    }

    /**
     * Listen to init_request.
     */
    public function onInitRequest($event) {
        $request = $event->get('request');
        $session = $request->getSession();

        // adjust front controller parameter
        if ($request->getFrontController() != Runtime::getSettings()->get('zenmagick.http.request.handler')) {
             Runtime::getSettings()->set('zenmagick.http.request.handler', $request->getFrontController());
        }

        // load additional routing
        $contextConfigLoader = $this->container->get('contextConfigLoader');
        if ($contextConfigLoader instanceof HttpContextConfigLoader) {
            $router = $request->getRouter();
            foreach ($contextConfigLoader->getRouting() as $routing) {
                $routingLoader = new YamlLoader();
                $routerCollection = $routingLoader->load($routing);
                $router->getRouteCollection()->addCollection($routerCollection);
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
