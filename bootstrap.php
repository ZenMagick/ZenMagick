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
use zenmagick\base\Beans;
use zenmagick\base\ClassLoader;
use zenmagick\base\events\Event;
use Symfony\Component\Yaml\Yaml;


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

    // hide as to avoid filenames that contain account names, etc.
    ini_set('display_errors', false);

    // all
    error_reporting(-1);

    // enable logging
    ini_set('log_errors', true);

    // ** set up base class loader
    $basephar = 'phar://'.ZM_BASE_PATH.'/lib/base/base.phar';
    if (file_exists($basephar)) {
        require_once $basephar.'/zenmagick/base/ClassLoader.php';
    } else {
        require_once ZM_BASE_PATH.'/lib/base/zenmagick/base/ClassLoader.php';
    }
    unset($basephar);
    $baseLoader = new ClassLoader();
    $baseLoader->addConfig(ZM_BASE_PATH.'lib'.DIRECTORY_SEPARATOR.'base');
    $baseLoader->addConfig(ZM_BASE_PATH.'lib'.DIRECTORY_SEPARATOR.'vendor');
    $baseLoader->register();
    unset($baseLoader);

    // as default disable plugins for CLI calls
    Runtime::getSettings()->set('zenmagick.base.plugins.enabled', !ZM_CLI_CALL);

// XXX: legacy loader
require_once ZM_BASE_PATH."lib/core/ZMLoader.php";
spl_autoload_register('ZMLoader::resolve');
// configure loader
ZMLoader::instance()->addPath(ZM_BASE_PATH.'lib'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR);
ZMLoader::instance()->addPath(ZM_BASE_PATH.'lib'.DIRECTORY_SEPARATOR.'mvc'.DIRECTORY_SEPARATOR);

    // ** set up shared class loader
    if (defined('ZM_SHARED')) {
        $sharedLoader = new ClassLoader();
        foreach (explode(',', ZM_SHARED) as $name) {
// XXX: legacy loader
ZMLoader::instance()->addPath(ZM_BASE_PATH.trim($name).DIRECTORY_SEPARATOR);
            $sharedLoader->addConfig(ZM_BASE_PATH.trim($name));
        }
        $sharedLoader->register();
        unset($sharedLoader);
    }

    // TODO: swap with shared once defaults are loaded as part of the app event listener
    // ** set up application class loader
    if (null != Runtime::getApplicationPath()) {
// XXX: legacy loader
ZMLoader::instance()->addPath(Runtime::getApplicationPath().DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR);
        $appLoader = new ClassLoader();
        $appLoader->addConfig(Runtime::getApplicationPath().DIRECTORY_SEPARATOR.'lib');
        $appLoader->register();
        unset($appLoader);
    }

    // load application config
    Runtime::getSettings()->setAll(Yaml::load(Runtime::getApplicationPath().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.yaml'));

// XXX: legacy: load static stuff and leave the rest to autoload
ZMLoader::instance()->loadStatic();

    // hook up default event listeners
    foreach (Runtime::getSettings()->get('zenmagick.base.events.listeners') as $_zm_elc) {
        if (null != ($_zm_el = Beans::getBean(trim($_zm_elc)))) {
            Runtime::getEventDispatcher()->listen($_zm_el);
        }
    }

    // TODO: move into app event listener
    // mvc mappings
    ZMUrlManager::instance()->load(file_get_contents(ZMFileUtils::mkPath(Runtime::getApplicationPath(), 'config', 'url_mappings.yaml')), false);
    // sacs mappings
    ZMSacsManager::instance()->load(file_get_contents(ZMFileUtils::mkPath(Runtime::getApplicationPath(), 'config', 'sacs_mappings.yaml')), false);
    ZMSacsManager::instance()->loadProviderMappings(ZMSettings::get('zenmagick.mvc.sacs.mappingProviders'));

    // create the main request instance
    $request = $_zm_request = ZMRequest::instance();

    // app config and code loaded; do not log to allow plugins to provider alternative logger
    Runtime::getEventDispatcher()->notify(new Event(null, 'app_init_done',  array('request' => $_zm_request)));

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

    // upset plugins if required
    if (Runtime::getSettings()->get('zenmagick.base.plugins.enabled')) {
        foreach (ZMPlugins::instance()->initAllPlugins(ZMSettings::get('zenmagick.core.plugins.context', 0)) as $plugin) {
            if ($plugin instanceof ZMRequestHandler) {
                $plugin->initRequest($_zm_request);
            }
        }
    }

    // register custom error handler
    if (ZMSettings::get('zenmagick.core.logging.handleErrors')) {
        set_error_handler(array(Runtime::getLogging(), 'errorHandler'));
        set_exception_handler(array(Runtime::getLogging(), 'exceptionHandler'));
    }

    // set up locale
    ZMLocales::instance()->init(ZMSettings::get('zenmagick.core.locales.locale'));

    // core and plugins loaded
    Runtime::getEventDispatcher()->notify(new Event(null, 'bootstrap_done',  array('request' => $_zm_request)));
