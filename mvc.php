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

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\events\Event;

    $eventDispatcher = Runtime::getEventDispatcher();
    $container = Runtime::getContainer();

    try {
        // create the main request instance
        $request = $_zm_request = Runtime::getContainer()->get('request');

        // tell everyone interested that we have a request
        $eventDispatcher->dispatch('request_ready', new Event(null, array('request' => $_zm_request)));

        // freeze container
        $container->compile();

        // tell everyone interested that we have a container
        $eventDispatcher->dispatch('container_ready', new Event(null, array('request' => $_zm_request)));
if (TRACEBS) {echo 'post container_ready: '.Runtime::getExecutionTime()."<BR>";}

        // allow seo rewriters to fiddle with the request
        $_zm_request->urlDecode();
if (TRACEBS) {echo 'post url decode: '.Runtime::getExecutionTime()."<BR>";}

        // make sure we use the appropriate protocol (HTTPS, for example) if required
        $container->get('sacsManager')->ensureAccessMethod($_zm_request);

        // form validation
        $validationConfig = Runtime::getApplicationPath().'/config/validation.yaml';
        if ($container->has('validator') && file_exists($validationConfig)) {
            $container->get('validator')->load(file_get_contents(Toolbox::resolveWithEnv($validationConfig)));
        }

        // load stuff that really needs to be global!
        if (Runtime::getSettings()->get('zenmagick.base.plugins.enabled', true)) {
            foreach ($container->get('pluginService')->getAllPlugins(Runtime::getContext()) as $plugin) {
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
    $eventDispatcher->dispatch('init_done', new Event(null, array('request' => $_zm_request)));
if (TRACEBS) {echo 'post init_done: '.Runtime::getExecutionTime()."<BR>";}

    $container->get('dispatcher')->dispatch($_zm_request);
if (TRACEBS) {echo 'post dispatcher: '.Runtime::getExecutionTime()."<BR>";}
    exit;
