<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 *
 * $Id$
 */
?>
<?php

    // start time for stats
    define('ZM_START_TIME', microtime());

    // detect CLI calls
    define('ZM_CLI_CALL', defined('STDIN'));

    // base installation directory
    define('ZM_BASE_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);

    error_reporting(E_ALL^E_NOTICE);
    // hide as to avoid filenames that contain account names, etc.
    ini_set("display_errors", false);
    // enable logging
    ini_set("log_errors", true); 
    // XXX: no, no
    @ini_set("register_globals", false);

    // load initial code
    if (!IS_ADMIN_FLAG && file_exists(ZM_BASE_DIR.'core.php')) {
        require ZM_BASE_DIR.'core.php';
    } else {
        require_once ZM_BASE_DIR."lib/core/ZMLoader.php";
        // configure loader
        ZMLoader::instance()->addPath(ZM_BASE_DIR.'lib'.DIRECTORY_SEPARATOR);
        // load static stuff and leave the rest to __autoload()
        ZMLoader::instance()->loadStatic();
    }

    // as default disable plugins for CLI calls
    ZMSettings::set('plugins.enabled', !ZM_CLI_CALL);

    // create the main request instance; XXX: fix name
    $request = ZMRequest::instance();

    // load global settings
    if (file_exists(ZM_BASE_DIR.'local.php')) {
        require_once ZM_BASE_DIR.'local.php';
    }

    // upset plugins if required
    if (ZMSettings::get('plugins.enabled')) {
        // XXX: assuming store implementation
        $plugins = ZMPlugins::instance()->initPluginsForGroupsAndScope(explode(',', ZMSettings::get('plugins.types')), Runtime::getScope());
        foreach ($plugins as $plugin) {
            if ($plugin instanceof ZMRequestHandler) {
                $plugin->initRequest($request);
            }
        }
    }

    // XXX: do after plugins to allow plugins to provide alternative implementations, however it would be good to have some before!
    // register custom error handler
    if (ZMSettings::get('zenmagick.core.logging.handleErrors') && null != ZMSettings::get('zenmagick.core.logging.filename')) {
        set_error_handler(array(ZMLogging::instance(), 'errorHandler'));
        set_exception_handler(array(ZMLogging::instance(), 'exceptionHandler'));
    }

    // core and plugins loaded
    ZMEvents::instance()->fireEvent(null, ZMEvents::BOOTSTRAP_DONE, array('request' => $request));

    // make sure we use HTTPS if required
    ZMSacsMapper::instance()->ensureAccessMethod($request->getRequestId());

    // start output buffering
    // XXX: handle admin?
    if (!ZMSettings::get('isAdmin')) { ob_start(); }

    // load stuff that really needs to be global!
    foreach (ZMLoader::instance()->getGlobal() as $_zm_global) {
        include_once $_zm_global;
    }

    ZMEvents::instance()->fireEvent(null, ZMEvents::INIT_DONE, array('request' => $request));

?>
