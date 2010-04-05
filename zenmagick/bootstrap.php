<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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

    /*
     * To use this, 'ZM_APP_PATH' needs to be defined first. 
     * Expected value is the (full) path to an app directory following the
     * ZenMagick MVC layout conventions.
     */


    // start time for stats
    define('ZM_START_TIME', microtime());

    // detect CLI calls
    define('ZM_CLI_CALL', defined('STDIN'));

    // base installation directory
    define('ZM_BASE_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

    error_reporting(E_ALL^E_NOTICE);
    // hide as to avoid filenames that contain account names, etc.
    ini_set("display_errors", false);
    // enable logging
    ini_set("log_errors", true); 
    // no, no
    @ini_set("register_globals", false);

    // load initial code
    if (!IS_ADMIN_FLAG && file_exists(ZM_BASE_PATH.'core.php')) {
        require ZM_BASE_PATH.'core.php';
    } else {
        require_once ZM_BASE_PATH."lib/core/ZMLoader.php";
        spl_autoload_register('ZMLoader::resolve');

        // configure loader
        ZMLoader::instance()->addPath(ZM_BASE_PATH.'lib'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR);
        ZMLoader::instance()->addPath(ZM_BASE_PATH.'lib'.DIRECTORY_SEPARATOR.'mvc'.DIRECTORY_SEPARATOR);
        if (null != ZMRuntime::getApplicationPath()) {
            ZMLoader::instance()->addPath(ZMFileUtils::mkPath(array(ZMRuntime::getApplicationPath(), 'lib')));
        }
        // load static stuff and leave the rest to __autoload()
        ZMLoader::instance()->loadStatic();
    }

    // load defaults and configs...
    ZMSettings::load(file_get_contents(ZMFileUtils::mkPath(array(ZMRuntime::getApplicationPath(), 'config', 'defaults.yaml'))), false);

    // as default disable plugins for CLI calls
    ZMSettings::set('zenmagick.core.plugins.enabled', !ZM_CLI_CALL);

    // create the main request instance
    $request = $_zm_request = ZMRequest::instance();

    // load global settings
    if (file_exists(ZM_BASE_PATH.'local.php')) {
        require_once ZM_BASE_PATH.'local.php';
    }

    // upset plugins if required
    if (ZMSettings::get('zenmagick.core.plugins.enabled')) {
        $plugins = ZMPlugins::instance()->initPluginsForGroups(explode(',', ZMSettings::get('zenmagick.core.plugins.groups')), ZMSettings::get('zenmagick.core.plugins.context', 0));
        foreach ($plugins as $plugin) {
            if ($plugin instanceof ZMRequestHandler) {
                $plugin->initRequest($_zm_request);
            }
        }
    }

    // register custom error handler
    if (ZMSettings::get('zenmagick.core.logging.handleErrors')) {
        set_error_handler(array(ZMLogging::instance(), 'errorHandler'));
        set_exception_handler(array(ZMLogging::instance(), 'exceptionHandler'));
    }

    // core and plugins loaded
    ZMEvents::instance()->fireEvent(null, ZMEvents::BOOTSTRAP_DONE, array('request' => $_zm_request));

?>
