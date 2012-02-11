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

    define('TRACEBS', false);

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
        define('ZM_BASE_PATH', __DIR__);
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

if (TRACEBS) {$precl = microtime();}
        // load main packages
        $packageBase = 'zenmagick/';
        $packages = array('vendor', 'lib/base', 'lib/core', 'lib/http', 'lib/mvc', 'shared', 'vendor/local');
        $zmLoader = new $CLASSLOADER();
        $zmLoader->register();
        // all folders to check
        $includePath = array_merge(array(dirname(ZM_BASE_PATH)), explode(PATH_SEPARATOR, get_include_path()));
        foreach ($packages as $path) {
            // pick first existing folder
            foreach ($includePath as $dir) {
                $ppath = $dir.'/'.$packageBase.$path;
                if (file_exists($ppath) && is_dir($ppath)) {
                    $zmLoader->addConfig($ppath);
                    // packages may have their own *system* services
                    $packageConfig = $ppath.'/container.xml';
                    if (file_exists($packageConfig)) {
                        $packageLoader = new XmlFileLoader(Runtime::getContainer(), new FileLocator(dirname($packageConfig)));
                        $packageLoader->load($packageConfig);
                    }
                    break;
                }
            }
        }

        $container = Runtime::getContainer();
        $settingsService = Runtime::getSettings();
        $applicationPath = Runtime::getApplicationPath();

if (TRACEBS) {echo 'pre CL: '.Runtime::getExecutionTime($precl)."<BR>";}
if (TRACEBS) {echo 'post CL: '.Runtime::getExecutionTime()."<BR>";}

        // some base settings
        Runtime::setEnvironment(isset($_SERVER['ZM_ENVIRONMENT']) ? $_SERVER['ZM_ENVIRONMENT'] : 'prod');
        $settingsService->set('zenmagick.environment', Runtime::getEnvironment());
        $settingsService->set('zenmagick.installationPath', Runtime::getInstallationPath());
        $settingsService->set('zenmagick.base.context', defined('ZM_APP_PATH') ? basename(ZM_APP_PATH) : null);

        // load app in separate loader
        if (null != $applicationPath) {
            $appLoader = new $CLASSLOADER();
            $appLoader->register();
            $appLoader->addConfig($applicationPath.'/lib');
        }

        // as default disable plugins for CLI calls
        $settingsService->set('zenmagick.base.plugins.enabled', !ZM_CLI_CALL);

        // load application config
        $settingsService->setAll(Toolbox::loadWithEnv($applicationPath.'/config/config.yaml'));
if (TRACEBS) {echo 'post config.yaml: '.Runtime::getExecutionTime()."<BR>";}

        // load global config
        $globalFilename = realpath(Runtime::getInstallationPath().'/global.yaml');
        if (file_exists($globalFilename) && $container->has('contextConfigLoader')) {
            $contextConfigLoader = $container->get('contextConfigLoader');
            $contextConfigLoader->setConfig(Toolbox::loadWithEnv($globalFilename));
            $contextConfigLoader->process();
        }
if (TRACEBS) {echo 'post global.yaml: '.Runtime::getExecutionTime()."<BR>";}

        // bundles; DI only for now - might want to use HttpKernel for loading stuff?
        $bundles = array();
        $extensions = array();
        foreach ($settingsService->get('zenmagick.bundles', array()) as $key => $class) {
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
if (TRACEBS) {echo 'post bundles: '.Runtime::getExecutionTime()."<BR>";}

        // load application container config
        $containerConfig = Toolbox::resolveWithEnv($applicationPath.'/config/container.xml');
        if (file_exists($containerConfig)) {
            $containerLoader = new XmlFileLoader($container, new FileLocator(dirname($containerConfig)));
            $containerLoader->load(basename($containerConfig));
        }
if (TRACEBS) {echo 'post container.xml: '.Runtime::getExecutionTime()."<BR>";}

        if (null != $applicationPath) {
            // always add an application event listener - if available
            $eventListener = sprintf('zenmagick\apps\%s\EventListener', Runtime::getContext());
            if (ClassLoader::classExists($eventListener)) {
                Runtime::getEventDispatcher()->listen(new $eventListener());
            }
        }
if (TRACEBS) {echo 'post app event listner: '.Runtime::getExecutionTime()."<BR>";}

        // hook up default event listeners
        foreach ($settingsService->get('zenmagick.base.events.listeners', array()) as $_zm_elc) {
            if (null != ($_zm_el = Beans::getBean(trim($_zm_elc)))) {
                Runtime::getEventDispatcher()->listen($_zm_el);
            }
        }

        Runtime::getEventDispatcher()->dispatch('init_config_done', new Event());
if (TRACEBS) {echo 'post init_config_done: '.Runtime::getExecutionTime()."<BR>";}

        // set up locale
        $container->get('localeService')->init($settingsService->get('zenmagick.base.locales.locale', 'en'));
if (TRACEBS) {echo 'post init locale: '.Runtime::getExecutionTime()."<BR>";}

        // set a default timezone; NOTE: warnings are suppressed for date_default_timezone_get() in case there isn't a default at all
        date_default_timezone_set(Runtime::getSettings()->get('zenmagick.core.date.timezone', @date_default_timezone_get()));
        if (null != ($_dt = date_timezone_get((new DateTime())))) {
            // set back with the actually used value
            $settingsService->set('zenmagick.core.date.timezone', $_dt->getName());
        }
if (TRACEBS) {echo 'post timezone: '.Runtime::getExecutionTime()."<BR>";}

        // register custom error handler
        if ($settingsService->get('zenmagick.base.logging.handleErrors')) {
            $logging = Runtime::getLogging();
            set_error_handler(array($logging, 'errorHandler'));
            set_exception_handler(array($logging, 'exceptionHandler'));
            register_shutdown_function(array($logging, 'shutdownHandler'));
        }
if (TRACEBS) {echo 'post error handlers: '.Runtime::getExecutionTime()."<BR>";}

        Runtime::getLogging()->info('environment is: '.Runtime::getEnvironment());
if (TRACEBS) {echo 'post bootstrap_done: '.Runtime::getExecutionTime()."<BR>";}
    } catch (Exception $e) {
        echo '<pre>';
        echo $e->getTraceAsString();
        echo '</pre>';
        die(sprintf('bootstrap failed: %s', $e->getMessage()));
    }

    try {
        // upset plugins if required
        if ($settingsService->get('zenmagick.base.plugins.enabled', true)) {
            $container->get('pluginService')->initAllPlugins($settingsService->get('zenmagick.base.context'));
        }
if (TRACEBS) {echo 'post plugins: '.Runtime::getExecutionTime()."<BR>";}
    } catch (Exception $e) {
        echo '<pre>';
        echo $e->getTraceAsString();
        echo '</pre>';
        die(sprintf('init plugins failed: %s', $e->getMessage()));
    }

    try {
        Runtime::getEventDispatcher()->dispatch('bootstrap_done', new Event());
if (TRACEBS) {echo 'post bootstrap_done: '.Runtime::getExecutionTime()."<BR>";}
    } catch (Exception $e) {
        echo '<pre>';
        echo $e->getTraceAsString();
        echo '</pre>';
        die(sprintf('init bootstrap_done failed: %s', $e->getMessage()));
    }
