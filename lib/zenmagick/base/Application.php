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
use zenmagick\base\dependencyInjection\ContainerBuilder;
use zenmagick\base\dependencyInjection\parameterBag\SettingsParameterBag;
use zenmagick\base\events\Event;
use zenmagick\base\plugins\Plugins;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;
use Symfony\Component\ClassLoader\DebugClassLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base application.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo: document all config options
 */
class Application extends Kernel {
    protected $config;
    protected $classLoader;
    protected $profile;
    protected $settingsService;

    /**
     * Create new application
     *
     * @param string  environment The environment
     * @param boolean debug Whether to enable debugging or not
     * @param array config Optional config settings.
     */
    public function __construct($environment = 'prod', $debug = false, array $config=array()) {
        $this->config = $config;
        $this->profile = array();
        Toolbox::setEnvironment($environment);
        parent::__construct($environment, $debug);
        $this->startTime = microtime(true);
    }

    /**
     * Register Bundle classes.
     *
     * @return array instantiated bundle objects
     */
    public function registerBundles() {
        $settingsService = $this->settingsService;
        $bundleList = $settingsService->get('zenmagick.bundles', array());
        array_unshift($bundleList, 'zenmagick\base\ZenMagickBundle');
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
        $appContainerFiles = array('lib/zenmagick/base/container.xml');
        if ('cli' !== php_sapi_name()) {
            $appContainerFiles[] = 'lib/zenmagick/http/container.xml';
        }
        if ($applicationPath = $this->getApplicationPath()) {
            $appContainerFiles[] = $applicationPath.'/config/container.xml';
        }
        $appContainerFiles[] = 'config/store-container.xml';
        $appContainerFiles = array_merge($appContainerFiles, $this->getConfig('appContainer', array()));
        $files = array();
        $filesystem = new Filesystem();
        foreach ($appContainerFiles as $file) {
            if (!$filesystem->isAbsolutePath($file)) {
                $file = $this->getRootDir().'/'.$file;
            }
            $containerConfig = Toolbox::resolveWithEnv($file);
            if (file_exists($containerConfig)) {
                $files[] = $containerConfig;
            }
        }

        foreach ($files as $file) {
            $loader->load($file);
        }
    }

    /**
     * {@inheritDoc}
     *
     * Modified to support our settings for 'display_errors'
     * and 'error_reporting'.
     */
    public function init() {
        ini_set('log_errors', $this->getConfig('log_errors', true));
        if ($this->debug) {
            ini_set('display_errors', true);
            error_reporting(-1);
            DebugClassLoader::enable();
            ErrorHandler::register($this->getConfig('error_reporting'));
            if ('cli' !== php_sapi_name()) {
                ExceptionHandler::register();
            }
        } else {
            ini_set('display_errors', $this->getConfig('display_errors', false));
            error_reporting($this->getConfig('error_reporting'));
        }
    }

    /**
     * Bootstrap application.
     *
     * @param array keys Optional list of bootstrap block keys to run; default is <code>null</code> for all.
     */
    public function boot(array $keys=null) {
        if (true === $this->booted) return;
        $bootstrap = $this->initBootstrap();
        foreach ($bootstrap as $ii => $step) {
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
    }

    /**
     * @{inheritDoc}
     */
    public function getName() {
        if (null == $this->name) {
            $this->name = 'zenmagick'; // @todo what?
        }
        return $this->name;
    }

    /**
     * Get the application path.
     *
     * @return string The application path or <code>null</code>.
     */
    public function getApplicationPath() {
        if ($context = $this->getContext()) {
            return sprintf('%s/apps/%s', $this->getRootDir(), $context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRootDir() {
        return dirname(dirname(dirname(__DIR__)));
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheDir() {
        return $this->rootDir.'/cache/'.$this->getContext().'/'.$this->environment;
    }


    /**
     * {@inheritDoc}
     * @todo adjust
     */
    public function getCharset() {
        return $this->getConfig('charset', 'UTF-8');
    }

    /**
     * Get the application context.
     *
     * @return string The application context.
     */
    public function getContext() {
        return $this->getConfig('context');
    }

    /**
     * {@inheritDoc}
     *
     * @todo remove this stub once we're ready
     */
    public function loadClassCache($name = 'classes', $extension = '.php') {
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
                    'initSettings',
                    'initializeBundles',
                    'initializeContainer',
                    'loadBundles',
                    'loadBootstrapPackages',
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

        if ('cli' !== php_sapi_name()) {
            $bootstrap[] = array('key' => 'request', 'postEvent' => 'request_ready');
        }
        return $bootstrap;
    }

    /**
     * Get application config.
     *
     * @return array Map of application configuration.
     */
    public function getConfig($key=null, $default = null) {
        if (null == $key) {
            return $this->config;
        }
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }
        return $default;
    }

    /**
     * Add entry to profile.
     *
     * @param string text The profile text; default is null to just return the current profile data.
     * @return array List of profile entries.
     */
    public function profile($text=null) {
        if ($this->getConfig('profile', $this->debug)) {
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
        $this->classLoader = new ClassLoader();
        $this->classLoader->register();
        if ($this->getContext()) {
            $this->classLoader->addConfig($this->getRootDir().'/apps/'.$this->getContext().'/lib');
        }
        $this->classLoader->addConfig($this->getRootDir().'/shared');
    }

    /**
     * Fire event.
     *
     * @param string eventName The event name.
     * @param array parameter Optional parameter; default is an empty array.
     */
    public function fireEvent($eventName, array $parameter=array()) {
        $parameter['kernel'] = $this;
        if (('cli' !== php_sapi_name()) && in_array($eventName, array('request_ready', 'container_ready'))) {
            $parameter['request'] = $this->container->get('request');
        }

        $this->container->get('eventDispatcher')->dispatch($eventName, new Event($this, $parameter));
    }

    /**
     * {@inheritDoc}
     *
     * @todo object resources
     * @todo caching
     * @todo compile here
     */
    protected function buildContainer() {
        $container = $this->getContainerBuilder();
        $container->set('settingsService', $this->settingsService);
        $extensions = array();
        foreach ($this->bundles as $bundle) {
            if ($extension = $bundle->getContainerExtension()) {
                $container->registerExtension($extension);
                $extensions[] = $extension->getAlias();
            }

            if ($this->debug) {
                //$container->addObjectResource($bundle);
            }
        }
        foreach ($this->bundles as $bundle) {
            $bundle->build($container);
        }
        // @todo registerContainerConfiguration can also return a container!
        $this->registerContainerConfiguration($this->getContainerLoader($container));

        return $container;
    }

    /**
     * Init some basic settings.
     */
    protected function initSettings() {
        $settingsService = new \zenmagick\base\settings\Settings;

        $settingsFiles = array();
        if ($applicationPath = $this->getApplicationPath()) {
            $settingsFiles[] = $applicationPath.'/config/config.yaml';
        }
        $settingsFiles = array_merge($this->getConfig('appConfig', array()), $settingsFiles);
        foreach ($settingsFiles as $config) {
            if (file_exists($config)) {
                $settingsService->setAll(Toolbox::loadWithEnv($config));
            }
        }

        $globalFilename = realpath($this->getRootDir().'/global.yaml');
        if (file_exists($globalFilename)) {
            $contextConfigLoader = new \zenmagick\base\utils\ContextConfigLoader;
            $contextConfigLoader->setConfig(Toolbox::loadWithEnv($globalFilename));
            $config = $contextConfigLoader->resolve($this->getContext());
            if (isset($config['settings'])) {
                $settingsService->setAll($config['settings']);
            }
            unset($config['settings']);
            unset($config['container']); // @todo merge this with the other container configuration if we want to keep it.
            $contextConfigLoader->apply($config);
        }

        // if settings are defined here, they are the final word
        if ($this->getConfig('settings')) {
            $settingsService->setAll((array)$this->getConfig('settings'));
        }
        $listeners = array();
        if ($applicationPath = $this->getApplicationPath()) {
            $listeners[] = sprintf('zenmagick\apps\%s\EventListener', $this->getContext());
        }
        $listeners[] = 'zenmagick\base\EventListener';
        if ('cli' !== php_sapi_name()) {
            $listeners[] = 'zenmagick\http\EventListener';
        }
        $listeners = array_merge($settingsService->get('zenmagick.base.events.listeners', array()), $listeners);
        $settingsService->set('zenmagick.base.events.listeners', $listeners);
        $settingsService->set('zenmagick.base.context', $this->getContext());
        $this->settingsService = $settingsService;
    }

    /**
     * {@inheritDoc}
     */
    public function getContainerBuilder() {
        return new ContainerBuilder(new SettingsParameterBag($this->getKernelParameters()));
        // We'll probably go back to this later?
        //return new ContainerBuilder(new ParameterBag($this->getKernelParameters()));
    }

    /**
     * {@inheritDoc}
     *
     * @todo cached container
     */
    protected function initializeContainer() {
        $container = $this->buildContainer();
        $this->container = $container;
        // register this as 'kernel'
        $this->container->set('kernel', $this);
        Runtime::setContainer($this->container);
    }


    /**
     * Load bundles.
     */
    protected function loadBundles() {
        foreach ($this->getBundles() as $bundle) {
            $bundle->setContainer($this->container);
            $bundle->boot();
        }
    }

    /**
     * Load bootstrap packages.
     */
    protected function loadBootstrapPackages() {
        $container = $this->container;
        $settingsService = $container->get('settingsService');
        if ($settingsService->get('zenmagick.base.plugins.enabled', true)) {
            foreach ($container->get('pluginService')->getPluginPackages() as $path) {
                $this->classLoader->addConfig($path);
            }
        }
    }

    /**
     * Init event listener.
     */
    protected function initEventListener() {
        $eventDispatcher = $this->container->get('eventDispatcher');
        $settingsService = $this->container->get('settingsService');
        foreach ($settingsService->get('zenmagick.base.events.listeners', array()) as $eventListener) {
            if (null != ($eventListener = Beans::getBean(trim($eventListener)))) {
                $eventDispatcher->listen($eventListener);
            }
        }
    }

    /**
     * Init locale.
     */
    protected function initLocale() {
        $container = $this->container;
        $settingsService = $container->get('settingsService');

        $container->get('localeService')->init($settingsService->get('zenmagick.base.locales.locale', $this->getConfig('defaultLocale', 'en')));

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
            $container->get('pluginService')->getPluginsForContext($this->getContext());
        }
    }

    /**
     * Compile container.
     */
    protected function compileContainer() {
        $container = $this->getContainer()->compile();
    }

}
