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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\loader\YamlFileLoader as SymfonyYamlFileLoader;

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;

    try {
        // create the main request instance
        $request = $_zm_request = Runtime::getContainer()->get('request');

        // load application routing
        $appRoutingFile = Runtime::getApplicationPath().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'routing.yaml';
        if (file_exists($appRoutingFile)) {
            $appRoutingLoader = new SymfonyYamlFileLoader(new FileLocator());
            $appRouterCollection = $appRoutingLoader->load($appRoutingFile);
            $request->getRouter()->getRouteCollection()->addCollection($appRouterCollection);
        }

        // tell everyone interested that we have a request
        Runtime::getEventDispatcher()->dispatch('init_request', new Event(null, array('request' => $_zm_request)));

        // freeze container
        Runtime::getContainer()->compile();

        // tell everyone interested that we have a container
        Runtime::getEventDispatcher()->dispatch('container_ready', new Event(null, array('request' => $_zm_request)));

        // allow seo rewriters to fiddle with the request
        $_zm_request->urlDecode();

        // make sure we use the appropriate protocol (HTTPS, for example) if required
        Runtime::getContainer()->get('sacsManager')->ensureAccessMethod($_zm_request);

        // form validation
        ZMValidator::instance()->load(file_get_contents(\ZMFileUtils::mkPath(array(Runtime::getApplicationPath(), 'config', 'validation.yaml'))));

        // load stuff that really needs to be global!
        if (Runtime::getSettings()->get('zenmagick.base.plugins.enabled', true)) {
            foreach (Runtime::getContainer()->get('pluginService')->getAllPlugins(ZMSettings::get('zenmagick.base.context')) as $plugin) {
                foreach ($plugin->getGlobal($_zm_request) as $_zm_file) {
                    include_once $_zm_file;
                }
            }
        }
    } catch (Exception $e) {
        echo '<pre>';
        echo $e->getTraceAsString();
        echo '</pre>';
        die(sprintf('init webapp failed: %s', $e->getMessage()));
    }

    // reset as other global code migth fiddle with it...
    $request = $_zm_request;
    Runtime::getEventDispatcher()->dispatch('init_done', new Event(null, array('request' => $_zm_request)));

    ZMDispatcher::dispatch($_zm_request);
    exit;
