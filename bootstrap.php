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
use zenmagick\base\Toolbox;
use zenmagick\base\events\Event;

    // TODO: remove
    define('ZM_ROOT', basename(dirname(__FILE__)).'/');

    /*
     * If 'ZM_APP_PATH' is defined, the following conventions are expected:
     *
     *   - ZM_APP_PATH is a full path pointing to a directory
     *   - ZM_APP_PATH contains a lib and a config folder
     *   - the config folder should contain the main application config file: config.yaml
     */

    // start time for stats
    define('ZM_START_TIME', microtime());
    // detect CLI calls
    define('ZM_CLI_CALL', defined('STDIN'));
    // base installation directory
    define('ZM_BASE_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
    // app name
    define('ZM_APP_NAME', defined('ZM_APP_PATH') ? basename(ZM_APP_PATH) : null);

    // set up the environment to run in
    $getenv_func = function_exists('apache_getenv') ? 'apache_getenv' : 'getenv';
    defined('ZM_ENVIRONMENT') || define('ZM_ENVIRONMENT', ($getenv_func('ZM_ENVIRONMENT') ? $getenv_func('ZM_ENVIRONMENT') : 'production'));

    // hide as to avoid filenames that contain account names, etc.
    ini_set('display_errors', true);
    // enable all reporting
    error_reporting(-1);
    // enable logging
    ini_set('log_errors', true);

    // set up base class loader
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

    // set up application class loader
    if (null != Runtime::getApplicationPath()) {
// XXX: legacy loader
ZMLoader::instance()->addPath(Runtime::getApplicationPath().DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR);
        $appLoader = new ClassLoader();
        $appLoader->addConfig(Runtime::getApplicationPath().DIRECTORY_SEPARATOR.'lib');
        $appLoader->register();
        unset($appLoader);
    }

    // set up lib class loader
    if (defined('ZM_LIBS')) {
        $libLoader = new ClassLoader();
        foreach (explode(',', ZM_LIBS) as $name) {
// XXX: legacy loader
ZMLoader::instance()->addPath(ZM_BASE_PATH.trim($name).DIRECTORY_SEPARATOR);
            $libLoader->addConfig(ZM_BASE_PATH.trim($name));
        }
        $libLoader->register();
        unset($libLoader);
    }

    // load application config
    Runtime::getSettings()->setAll(Toolbox::loadWithEnv(Runtime::getApplicationPath().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.yaml'));

    // hook up default event listeners
    foreach (Runtime::getSettings()->get('zenmagick.base.events.listeners', array()) as $_zm_elc) {
        if (null != ($_zm_el = Beans::getBean(trim($_zm_elc)))) {
            Runtime::getEventDispatcher()->listen($_zm_el);
        }
    }

    // load global config to allow overriding app settings
    Runtime::getSettings()->setAll(Toolbox::loadWithEnv(Runtime::getInstallationPath().DIRECTORY_SEPARATOR.'global.yaml'));

    // set up locale
    ZMLocales::instance()->init(Runtime::getSettings()->get('zenmagick.core.locales.locale'));

    // set a default timezone; NOTE: warnings are suppressed for date_default_timezone_get() in case there isn't a default at all
    date_default_timezone_set(Runtime::getSettings()->get('zenmagick.core.date.timezone', @date_default_timezone_get()));
    if (null != ($_dt = date_timezone_get((new DateTime())))) {
        // set back with the actually used value
        Runtime::getSettings()->set('zenmagick.core.date.timezone', $_dt->getName());
    }

    // register custom error handler
    if (Runtime::getSettings()->get('zenmagick.base.logging.handleErrors')) {
        set_error_handler(array(Runtime::getLogging(), 'errorHandler'));
        set_exception_handler(array(Runtime::getLogging(), 'exceptionHandler'));
    }

    Runtime::getLogging()->info('environment is: '.ZM_ENVIRONMENT);
    Runtime::getEventDispatcher()->notify(new Event(null, 'bootstrap_done'));

    // upset plugins if required
    if (Runtime::getSettings()->get('zenmagick.base.plugins.enabled', true)) {
        ZMPlugins::instance()->initAllPlugins(Runtime::getSettings()->get('zenmagick.base.plugins.context'));
        Runtime::getEventDispatcher()->notify(new Event(null, 'init_plugins_done'));
    }
