<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\base;

use DateTime;
use Exception;
use zenmagick\base\Runtime;
use zenmagick\base\Beans;
use zenmagick\base\classloader\ClassLoader;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMException;
use zenmagick\base\events\Event;
use zenmagick\base\plugins\Plugins;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;
use Symfony\Component\ClassLoader\DebugClassLoader;

use Symfony\Component\HttpFoundation\Response;

/**
 * Base application.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo: document all config options
 */
class Application extends Kernel {
    protected $bootstrap;
    protected $config;
    protected $classLoader;
    protected $profile;

    /**
     * Create new application
     *
     * @param string  environment The environment
     * @param boolean debug Whether to enable debugging or not
     * @param array config Optional config settings.
     */
    public function __construct($environment = 'prod', $debug = false, array $config=array()) {
        $this->environment = $environment;
        $this->debug = (bool)$debug;
        $this->booted = false;
        // @todo same as installationPath?
        $this->rootDir = dirname(dirname(dirname(__DIR__)));
        $this->name = 'zenmagick'; // @todo what?
        $this->startTime = microtime(true);
        $this->classes = array();
        $defaults = array(
            // general stuff
            'installationPath' => $this->rootDir,
            'cli' => php_sapi_name() == 'cli',
            'profile' => $this->debug,
            'enablePlugins' => null,

            // packages
            'packageBase' => basename(dirname(dirname(dirname(__DIR__)))),
            'packages' => array('lib/zenmagick/base', 'config'),
            'classLoader' => 'zenmagick\base\classloader\CachingClassLoader',
            'eventListener' => array('zenmagick\base\EventListener'),

            'bundles' => array(),

            // app stuff
            'context' => null,
            'defaultLocale' => 'en',
            'appConfig' => array(),
            'appContainer' => array(),
            'settings' => array(),

            // ini
            'display_errors'=> false,
            'error_reporting' => -1,
            'log_errors' => true
        );

        $this->config = array_merge($defaults, $config);
        // some derived config
        $this->config['applicationPath'] = $this->config['context'] ? sprintf('%s/apps/%s', $this->config['installationPath'], $this->config['context']) : null;

        if (!$this->getConfig('cli')) {
            $this->config['packages'][] = 'lib/zenmagick/http';
            $this->config['eventListener'][] = 'zenmagick\http\EventListener';
        }

        Toolbox::setEnvironment($this->environment);
        $this->profile = array();
        $this->init();
        $this->initBootstrap();
    }

    /**
     * Register Bundle classes.
     *
     * @return array instantiated bundle objects
     */
    public function registerBundles() {
        $settingsService = Runtime::getSettings();
        $bundleList = array_merge($settingsService->get('zenmagick.bundles', array()), $this->config['bundles']);
        $bundles = array();
        foreach ($bundleList as $name) {
            $bundles[] = new $name();
        }
        return $bundles;
    }

    /**
     * {@inheritDoc}
     * @copyright see symfony.com
     * @see Symfony\Component\HttpKernel\KernelInterface
     */
    public function registerContainerConfiguration(LoaderInterface $loader) {
        die(var_dump(debug_backtrace()));
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    /**
     * {@inheritDoc}
     *
     * Modified to support our settings for 'display_errors'
     * and 'error_reporting'.
     */
    public function init() {
        ini_set('log_errors', $this->config['log_errors']);
        if ($this->debug) {
            ini_set('display_errors', true);
            error_reporting(-1);
            DebugClassLoader::enable();
            ErrorHandler::register($this->errorReportingLevel);
            if ('cli' !== php_sapi_name()) {
                ExceptionHandler::register();
            }
        } else {
            ini_set('display_errors', $this->config['display_errors']);
            error_reporting($this->config['error_reporting']);
        }
    }

    /**
     * Bootstrap application.
     *
     * @param array keys Optional list of bootstrap block keys to run; default is <code>null</code> for all.
     */
    public function boot(array $keys=null) {
        if (true === $this->booted) return;
        try {
            foreach ($this->bootstrap as $ii => $step) {
                if (array_key_exists('done', $step) || (null !== $keys && !in_array($step['key'], $keys))) {
                    continue;
                }

                if (array_key_exists('preEvent', $step)) {
                    $eventName = $step['preEvent'];
                    $this->fireEvent($eventName);
                }

                if (!array_key_exists('methods', $step)) $step['methods'] = array();
                foreach ((array)$step['methods'] as $method) {
                    $this->profile(sprintf('enter bootstrap method: %s', $method));
                    $this->$method();
                    $this->profile(sprintf('exit bootstrap method: %s', $method));
                }
                if (array_key_exists('postEvent', $step)) {
                    $eventName = $step['postEvent'];
                    $this->fireEvent($eventName);
                }
                $this->bootstrap[$ii]['done'] = true;
            }
            $this->booted = true;
        } catch (Exception $e) {
            $msg = sprintf('bootstrap failed: %s', $e->getMessage());
            if (null != ($container = $this->getContainer()) && $container->has('loggingService') && null != ($loggingService = $container->get('loggingService'))) {
                $loggingService->dump($e, $msg);
            }
            echo implode("\n", ZMException::formatStackTrace($e->getTrace()));
            die($msg);
        }
    }

    /**
     * Gets a http kernel from the container
     *
     * @return HttpKernel
     */
    protected function getHttpKernel() {
        return $this->getContainer()->get('http_kernel');
    }

    /**
     * Get the application path.
     *
     * @return string The application path or <code>null</code>.
     */
    public function getApplicationPath() {
        return $this->config['applicationPath'];
    }

    /**
     * Get the installation path.
     *
     * @return string The installation path or <code>null</code>.
     */
    public function getInstallationPath() {
        return $this->config['installationPath'];
    }

    /**
     * {@inheritDoc}
     * @todo don't get it from runtime!
     */
    public function getContainer() {
        return Runtime::getContainer();
    }

    /**
     * {@inheritDoc}
     * @todo adjust
     */
    public function getCharset() {
        return 'UTF-8';
    }

    /**
     * Get the application context.
     *
     * @return string The application context.
     */
    public function getContext() {
        return $this->config['context'];
    }

    /**
     * Init the bootstrap config.
     */
    protected function initBootstrap() {
        $bootstrap = array(
            array(
                'key' => 'init',
                'methods' => array(
                    'initClassLoader',
                    'loadPackages',
                    'initLogging',
                    'initSettings',
                    'initRuntime',
                    'initGlobal',
                    'loadBundles',
                    'loadBootstrapPackages',
                    'initApplicationContainer',
                    'initEventListener'
                ),
            ),
            array(
                'key' => 'bootstrap',
                'preEvent' => 'init_config_done',
                'methods' => array('initLocale', 'initPlugins'),
                'postEvent' => 'bootstrap_done'
            ),
            array(
                'key' => 'container',
                'methods' => array('compileContainer'),
                'postEvent' => 'container_ready'
            )
        );

        if (!$this->getConfig('cli')) {
            $bootstrap[] = array('key' => 'request', 'postEvent' => 'request_ready');
        }
        $this->bootstrap = $bootstrap;
    }

    /**
     * Get application config.
     *
     * @return array Map of application configuration.
     */
    public function getConfig($key=null) {
        if (null == $key) {
            return $this->config;
        }

        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        return null;
    }

    /**
     * Add entry to profile.
     *
     * @param string text The profile text; default is null to just return the current profile data.
     * @return array List of profile entries.
     */
    public function profile($text=null) {
        if ($this->config['profile']) {
            if ($text) {
                $this->profile[] = array('text' => $text, 'timestamp' => microtime());
            }
            return $this->profile;
        }
        return null;
    }

    /**
     * Get the currently elapsed page execution time.
     *
     * @param string time Optional execution timestamp to be used instead of the current time.
     * @return long The execution time in milliseconds.
     */
    public function getElapsedTime($time=null) {
        $endTime = explode(' ', (null != $time ? $time : microtime()));
        // $time might be float
        if (1 == count($endTime)) { $endTime[] = 0; }
        $executionTime = $endTime[1]+$endTime[0]-$this->startTime;
        return round($executionTime, 4);
    }

    /**
     * Init class loader.
     */
    protected function initClassLoader() {
        // set up base class loader
        $installationPath = $this->config['installationPath'] .'/lib/zenmagick/base';
        $basephar = 'phar://'.$installationPath.'/base.phar';

        // NOTE: the base package has a flattened folder structure, so the path doesn't reflect the namespace
        if (file_exists($basephar)) {
            require_once $basephar.'/classloader/ClassLoader.php';
            require_once $basephar.'/classloader/CachingClassLoader.php';
        } else {
            require_once $installationPath.'/classloader/ClassLoader.php';
            require_once $installationPath.'/classloader/CachingClassLoader.php';
        }

        $this->classLoader = new $this->config['classLoader']();
        $this->classLoader->register();

        // @todo hardcoded list until we can use composer class map.
        $classDirs = array('lib/mvc', 'shared');
        if ($applicationName = $this->config['context']) {
            $classDirs[] = 'apps/'.$applicationName.'/lib';
        }
        foreach ($classDirs as $classDir) {
            $classPath = $this->config['installationPath'].'/'.$classDir;
            $this->classLoader->addConfig($classPath);
        }
    }

    /**
     * Fire event.
     *
     * @param string eventName The event name.
     * @param array parameter Optional parameter; default is an empty array.
     */
    public function fireEvent($eventName, array $parameter=array()) {
        $parameter['kernel'] = $this;
        if (!$this->getConfig('cli') && in_array($eventName, array('request_ready', 'container_ready'))) {
            $parameter['request'] = $this->getContainer()->get('request');
        }

        Runtime::getEventDispatcher()->dispatch($eventName, new Event($this, $parameter));
    }

    /**
     * Load all configured packages.
     */
    protected function loadPackages() {
        $packageBase = $this->config['packageBase'];
        $packages = $this->config['packages'];

        // all folders to check
        $includePath = array_merge(array(dirname($this->config['installationPath'])), explode(PATH_SEPARATOR, get_include_path()));
        foreach ($packages as $path) {
            // pick first existing folder
            foreach ($includePath as $dir) {
                $ppath = $dir.'/'.$packageBase.'/'.$path;
                if (file_exists($ppath) && is_dir($ppath)) {
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
    }

    /**
     * Init some basic settings.
     */
    protected function initSettings() {
        $settingsService = Runtime::getSettings();

        $settingsService->set('zenmagick.environment', $this->environment);
        $settingsService->set('zenmagick.installationPath', $this->config['installationPath']);
        $settingsService->set('zenmagick.base.context', $this->config['context']);

        // as default disable plugins for CLI calls
        $settingsService->set('zenmagick.base.plugins.enabled', (!$this->config['cli'] || (null !== $this->config['enablePlugins'] ? $this->config['enablePlugins'] : false)));

        if ($applicationPath = $this->config['applicationPath']) {
            $settingsService->setAll(Toolbox::loadWithEnv($applicationPath.'/config/config.yaml'));
        }

        foreach ($this->config['appConfig'] as $config) {
            if (file_exists($config)) {
                $settingsService->setAll(Toolbox::loadWithEnv($config));
            }
        }
    }

    /**
     * Init runtime.
     */
    protected function initRuntime() {
        // register this as 'kernel'
        $this->getContainer()->set('kernel', $this);
        $this->getContainer()->set('http_kernel', new \zenmagick\http\HttpApplication());
    }

    /**
     * Init global.
     */
    protected function initGlobal() {
        $container = $this->getContainer();
        $settingsService = Runtime::getSettings();
        $globalFilename = realpath($this->config['installationPath'].'/global.yaml');
        if (file_exists($globalFilename) && $container->has('contextConfigLoader')) {
            $contextConfigLoader = $container->get('contextConfigLoader');
            $contextConfigLoader->setConfig(Toolbox::loadWithEnv($globalFilename));
            $contextConfigLoader->process();
        }

        // if settings are defined here, they are the final word
        foreach ($this->config['settings'] as $key => $value) {
            $settingsServivce->set($key, $value);
        }
    }

    /**
     * Load bundles.
     */
    protected function loadBundles() {
        $container = $this->getContainer();
        $settingsService = Runtime::getSettings();

        // TODO?: this might be less than HttpKernel does
        $extensions = array();
        foreach ($this->registerBundles() as $bundle) {
            $bundle->build($container);
            if ($extension = $bundle->getContainerExtension()) {
                $container->registerExtension($extension);
                $extensions[] = $extension->getAlias();
            }
            $bundle->setContainer($container);
            $bundle->boot();
            $this->bundles[] = $bundle;
        }
    }

    /**
     * Load bootstrap packages.
     */
    protected function loadBootstrapPackages() {
        $container = $this->getContainer();
        $settingsService = Runtime::getSettings();
        if ($settingsService->get('zenmagick.base.plugins.enabled', true)) {
            foreach ($container->get('pluginService')->getPluginPackages() as $path) {
                $this->classLoader->addConfig($path);
            }
        }
    }

    /**
     * Init application container.
     */
    protected function initApplicationContainer() {
        if ($applicationPath = $this->config['applicationPath']) {
            $container = $this->getContainer();
            $containerConfig = Toolbox::resolveWithEnv($applicationPath.'/config/container.xml');
            if (file_exists($containerConfig)) {
                $containerLoader = new XmlFileLoader($container, new FileLocator(dirname($containerConfig)));
                $containerLoader->load(basename($containerConfig));
            }
        }
        foreach ($this->config['appContainer'] as $file) {
            $container = $this->getContainer();
            $containerConfig = Toolbox::resolveWithEnv($file);
            if (file_exists($containerConfig)) {
                $containerLoader = new XmlFileLoader($container, new FileLocator(dirname($containerConfig)));
                $containerLoader->load(basename($containerConfig));
            }
        }
    }

    /**
     * Init event listener.
     */
    protected function initEventListener() {
        $eventDispatcher = Runtime::getEventDispatcher();
        if ($applicationPath = $this->config['applicationPath'] && $this->config['context']) {
            // always add an application event listener - if available
            $eventListener = sprintf('zenmagick\apps\%s\EventListener', $this->config['context']);
            if (ClassLoader::classExists($eventListener)) {
                $eventDispatcher->listen(new $eventListener());
            }
        }

        // hook up all configured event listeners
        $settingsService = Runtime::getSettings();
        foreach ($this->config['eventListener'] as $eventListener) {
            if (null != ($eventListener = Beans::getBean(trim($eventListener)))) {
                $eventDispatcher->listen($eventListener);
            }
        }

        foreach ($settingsService->get('zenmagick.base.events.listeners', array()) as $eventListener) {
            if (null != ($eventListener = Beans::getBean(trim($eventListener)))) {
                $eventDispatcher->listen($eventListener);
            }
        }
    }

    /**
     * Init logging.
     */
    protected function initLogging() {
        $settingsService = Runtime::getSettings();
        if ($settingsService->get('zenmagick.base.logging.handleErrors')) {
            $logging = Runtime::getLogging();
            set_error_handler(array($logging, 'errorHandler'));
            set_exception_handler(array($logging, 'exceptionHandler'));
            register_shutdown_function(array($logging, 'shutdownHandler'));
        }
        Runtime::getLogging()->debug(sprintf('environment is: %s', $this->environment));
    }

    /**
     * Init locale.
     */
    protected function initLocale() {
        $container = $this->getContainer();
        $settingsService = Runtime::getSettings();

        $container->get('localeService')->init($settingsService->get('zenmagick.base.locales.locale', $this->config['defaultLocale']));

        // set a default timezone; NOTE: warnings are suppressed for date_default_timezone_get() in case there isn't a default at all
        date_default_timezone_set($settingsService->get('zenmagick.core.date.timezone', @date_default_timezone_get()));
        if (null != ($dateTimeZone = date_timezone_get(new DateTime()))) {
            // set back with the actually used value
            $settingsService->set('zenmagick.core.date.timezone', $dateTimeZone->getName());
        }
    }

    /**
     * Init plugins.
     */
    protected function initPlugins() {
        $container = $this->getContainer();
        $settingsService = Runtime::getSettings();

        if ($settingsService->get('zenmagick.base.plugins.enabled', true)) {
            $container->get('pluginService')->getPluginsForContext($this->config['context']);
        }
    }

    /**
     * Compile container.
     */
    protected function compileContainer() {
        $container = $this->getContainer()->compile();
    }

}
