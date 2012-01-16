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
use zenmagick\base\Beans;
use zenmagick\base\classloader\ClassLoader;
use zenmagick\base\Toolbox;
use zenmagick\base\events\Event;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;


    // XXX: remove once caching is not needed any more
    $CLASSLOADER = 'zenmagick\base\classloader\CachingClassLoader';

    /*
     * If 'ZM_APP_PATH' is defined, the following conventions are expected:
     *
     *   - ZM_APP_PATH is a full path pointing to a directory
     *   - ZM_APP_PATH contains a lib and a config folder
     *   - the config folder should contain the main application config file: config.yaml
     */
    try {
        // start time for stats
        define('ZM_START_TIME', microtime());
        // detect CLI calls
        define('ZM_CLI_CALL', defined('STDIN'));
        // base installation directory
        define('ZM_BASE_PATH', dirname(__FILE__)) .'/';
        // app name
        define('ZM_APP_NAME', defined('ZM_APP_PATH') ? basename(ZM_APP_PATH) : null);
        // set up the environment to run in
        defined('ZM_ENVIRONMENT') || define('ZM_ENVIRONMENT', isset($_SERVER['ZM_ENVIRONMENT']) ? $_SERVER['ZM_ENVIRONMENT'] : 'prod');

        // hide as to avoid filenames that contain account names, etc.
        ini_set('display_errors', false);
        // enable all reporting
        error_reporting(-1);
        // enable logging
        ini_set('log_errors', true);

        // set up base class loader
        $basephar = 'phar://'.ZM_BASE_PATH.'/lib/base/base.phar';
        // NOTE: the base package has a flattened folder structure, so the path doesn't reflect the namespace
        if (file_exists($basephar)) {
            require_once $basephar.'/classloader/ClassLoader.php';
            require_once $basephar.'/classloader/CachingClassLoader.php';
        } else {
            require_once ZM_BASE_PATH.'/lib/base/classloader/ClassLoader.php';
            require_once ZM_BASE_PATH.'/lib/base/classloader/CachingClassLoader.php';
        }

        $vendor = defined('VENDOR_LIB') ? VENDOR_LIB : 'vendor';

        // load main packages
        $packages = array($vendor, $vendor.'/local', 'lib/base', 'lib/core', 'lib/http', 'lib/mvc', 'shared');
        $zmLoader = new $CLASSLOADER();
        $zmLoader->register();
        foreach ($packages as $path) {
            $path = ZM_BASE_PATH.'/'.$path;
            $zmLoader->addConfig($path);
            // packages may have their own *system* services
            $packageConfig = $path.'/container.xml';
            if (file_exists($packageConfig)) {
                $packageLoader = new XmlFileLoader(Runtime::getContainer(), new FileLocator(dirname($packageConfig)));
                $packageLoader->load($packageConfig);
            }
        }

        // some base settings
        Runtime::getSettings()->set('zenmagick.environment', ZM_ENVIRONMENT);
        Runtime::getSettings()->set('zenmagick.installationPath', Runtime::getInstallationPath());
        Runtime::getSettings()->set('zenmagick.base.context', ZM_APP_NAME);

        // load app in separate loader
        if (null != Runtime::getApplicationPath()) {
            $appLoader = new $CLASSLOADER();
            $appLoader->register();
            $appLoader->addConfig(Runtime::getApplicationPath().'/lib');
        }

        // as default disable plugins for CLI calls
        Runtime::getSettings()->set('zenmagick.base.plugins.enabled', !ZM_CLI_CALL);

        // load application config
        Runtime::getSettings()->setAll(Toolbox::loadWithEnv(Runtime::getApplicationPath().'/config/config.yaml'));

        // load global config
        $globalFilename = realpath(Runtime::getInstallationPath().'/global.yaml');
        if (file_exists($globalFilename) && Runtime::getContainer()->has('contextConfigLoader')) {
            $contextConfigLoader = Runtime::getContainer()->get('contextConfigLoader');
            $contextConfigLoader->setConfig(Toolbox::loadWithEnv($globalFilename));
            $contextConfigLoader->process();
        }

        // bundles; DI only for now - might want to use HttpKernel for loading stuff?
        $bundles = array();
        $extensions = array();
        $container = Runtime::getContainer();
        foreach (Runtime::getSettings()->get('zenmagick/bundles', array()) as $key => $class) {
            $bundle = new $class();
            $bundle->build($container);
            if ($extension = $bundle->getContainerExtension()) {
                $container->registerExtension($extension);
                $extensions[] = $extension->getAlias();
            }
            $bundle->setContainer($container);
            $bundle->boot();
            $bundles[] = $bundle;
        }

        // load application container config
        $containerConfig = Toolbox::resolveWithEnv(Runtime::getApplicationPath().'/config/container.xml');
        if (file_exists($containerConfig)) {
            $containerLoader = new XmlFileLoader(Runtime::getContainer(), new FileLocator(dirname($containerConfig)));
            $containerLoader->load(basename($containerConfig));
        }

        if (null != Runtime::getApplicationPath()) {
            // always add an application event listener - if available
            $eventListener = 'zenmagick\\apps\\'.ZM_APP_NAME.'\\EventListener';
            if (ClassLoader::classExists($eventListener)) {
                Runtime::getEventDispatcher()->listen(new $eventListener());
            }
        }

        // hook up default event listeners
        foreach (Runtime::getSettings()->get('zenmagick.base.events.listeners', array()) as $_zm_elc) {
            if (null != ($_zm_el = Beans::getBean(trim($_zm_elc)))) {
                Runtime::getEventDispatcher()->listen($_zm_el);
            }
        }

        Runtime::getEventDispatcher()->dispatch('init_config_done', new Event());

        // set up locale
        Runtime::getContainer()->get('localeService')->init(Runtime::getSettings()->get('zenmagick.base.locales.locale', 'en'));

        // set a default timezone; NOTE: warnings are suppressed for date_default_timezone_get() in case there isn't a default at all
        date_default_timezone_set(Runtime::getSettings()->get('zenmagick.core.date.timezone', @date_default_timezone_get()));
        if (null != ($_dt = date_timezone_get((new DateTime())))) {
            // set back with the actually used value
            Runtime::getSettings()->set('zenmagick.core.date.timezone', $_dt->getName());
        }

        // register custom error handler
        if (Runtime::getSettings()->get('zenmagick.base.logging.handleErrors')) {
            $logging = Runtime::getLogging();
            set_error_handler(array($logging, 'errorHandler'));
            set_exception_handler(array($logging, 'exceptionHandler'));
            register_shutdown_function(array($logging, 'shutdownHandler'));
        }

        Runtime::getLogging()->info('environment is: '.ZM_ENVIRONMENT);
        Runtime::getEventDispatcher()->dispatch('bootstrap_done', new Event());
    } catch (Exception $e) {
        echo '<pre>';
        echo $e->getTraceAsString();
        echo '</pre>';
        die(sprintf('bootstrap failed: %s', $e->getMessage()));
    }

    try {
        // upset plugins if required
        if (Runtime::getSettings()->get('zenmagick.base.plugins.enabled', true)) {
            Runtime::getContainer()->get('pluginService')->initAllPlugins(Runtime::getSettings()->get('zenmagick.base.context'));
            Runtime::getEventDispatcher()->dispatch('init_plugins_done', new Event());
        }
    } catch (Exception $e) {
        echo '<pre>';
        echo $e->getTraceAsString();
        echo '</pre>';
        die(sprintf('init plugins failed: %s', $e->getMessage()));
    }
