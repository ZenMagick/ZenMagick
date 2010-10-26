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
    if (defined('USE_CORE_PHP') && USE_CORE_PHP && file_exists(ZM_BASE_PATH.'core.php')) {
        require ZM_BASE_PATH.'core.php';
        spl_autoload_register('ZMLoader::resolve');
    } else {
        require_once ZM_BASE_PATH."lib/core/ZMLoader.php";
        spl_autoload_register('ZMLoader::resolve');

        // configure loader
        ZMLoader::instance()->addPath(ZM_BASE_PATH.'lib'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR);
        ZMLoader::instance()->addPath(ZM_BASE_PATH.'lib'.DIRECTORY_SEPARATOR.'mvc'.DIRECTORY_SEPARATOR);
        if (defined('ZM_SHARED')) {
            ZMLoader::instance()->addPath(ZM_BASE_PATH.ZM_SHARED.DIRECTORY_SEPARATOR);
        }
        if (null != ZMRuntime::getApplicationPath()) {
            ZMLoader::instance()->addPath(ZMFileUtils::mkPath(array(ZMRuntime::getApplicationPath(), 'lib')));
        }
        // load static stuff and leave the rest to autoload
        ZMLoader::instance()->loadStatic();
    }

    // load defaults and configs...
    ZMSettings::load(file_get_contents(ZMFileUtils::mkPath(ZMRuntime::getApplicationPath(), 'config', 'config.yaml')), false);
    // mvc mappings
    ZMUrlManager::instance()->load(file_get_contents(ZMFileUtils::mkPath(ZMRuntime::getApplicationPath(), 'config', 'url_mappings.yaml')), false);
    // sacs mappings
    ZMSacsManager::instance()->load(file_get_contents(ZMFileUtils::mkPath(ZMRuntime::getApplicationPath(), 'config', 'sacs_mappings.yaml')), false);
    ZMSacsManager::instance()->loadProviderMappings(ZMSettings::get('zenmagick.mvc.sacs.mappingProviders'));

    // as default disable plugins for CLI calls
    ZMSettings::set('zenmagick.core.plugins.enabled', !ZM_CLI_CALL);

    // create the main request instance
    $request = $_zm_request = ZMRequest::instance();

    // app config and code loaded; do not log to allow plugins to provider alternative logger
    ZMEvents::instance()->fireEvent(null, ZMEvents::APP_INIT_DONE, array('request' => $_zm_request), false);

    // load global settings
    if (file_exists(ZM_BASE_PATH.'local.php')) {
        require_once ZM_BASE_PATH.'local.php';
    }

    // set a default timezone; note that warnings are suppressed for date_default_timezone_get() in case there isn't a default at all
    date_default_timezone_set(ZMSettings::get('zenmagick.core.date.timezone', @date_default_timezone_get()));
    if (null != ($_dt = date_timezone_get((new DateTime())))) {
        // set back with the actually used value
        ZMSettings::set('zenmagick.core.date.timezone', $_dt->getName());
    }

    // set up default event listeners
    foreach (explode(',', ZMSettings::get('zenmagick.core.events.listeners')) as $_zm_elc) {
        if (null != ($_zm_el = ZMBeanUtils::getBean(trim($_zm_elc)))) {
            ZMEvents::instance()->attach($_zm_el);
        }
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

    // set up locale
    ZMLocales::instance()->init(ZMSettings::get('zenmagick.core.locales.locale'));

    // core and plugins loaded
    ZMEvents::instance()->fireEvent(null, ZMEvents::BOOTSTRAP_DONE, array('request' => $_zm_request));
