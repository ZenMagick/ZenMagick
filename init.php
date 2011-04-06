<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
use zenmagick\base\events\Event;
use zenmagick\http\sacs\SacsManager;


    if (!defined('ZM_APP_PATH')) {
        // app location relative to zenmagick installation (ZM_BASE_PATH)
        define('ZM_APP_PATH', 'apps'.DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR);
    }

    // additional libraries
    if(!defined('ZM_LIBS')) define('ZM_LIBS', 'lib/http,shared');

    include_once 'bootstrap.php';

    // create the main request instance
    $request = $_zm_request = ZMRequest::instance();

    // tell everyone interested that we have a request
    Runtime::getEventDispatcher()->notify(new Event(null, 'init_request',  array('request' => $_zm_request)));

    // freeze container
    Runtime::getContainer()->compile();

    // allow url rewriters to fiddle with the request
    $_zm_request->urlDecode();

    // make sure we use the appropriate protocol (HTTPS, for example) if required
    SacsManager::instance()->ensureAccessMethod($_zm_request);

    // load stuff that really needs to be global!
    if (Runtime::getSettings()->get('zenmagick.base.plugins.enabled', true)) {
        foreach (ZMPlugins::instance()->initAllPlugins(ZMSettings::get('zenmagick.base.plugins.context')) as $plugin) {
            foreach ($plugin->getGlobal($_zm_request) as $_zm_file) {
                include_once $_zm_file;
            }
        }
    }

    // restore
    $request = $_zm_request;
    Runtime::getEventDispatcher()->notify(new Event(null, 'init_done',  array('request' => $_zm_request)));
