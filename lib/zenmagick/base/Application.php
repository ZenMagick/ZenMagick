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
use zenmagick\base\settings\Settings;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMException;
use zenmagick\base\dependencyInjection\ContainerBuilder;
use zenmagick\base\dependencyInjection\parameterBag\SettingsParameterBag;
use zenmagick\base\events\Event;
use zenmagick\base\plugins\Plugins;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;
use Symfony\Component\HttpKernel\DependencyInjection\AddClassesToCachePass;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base application.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo: document all config options
 */
class Application extends Kernel {
    protected $context;
    protected $settingsService;

    /**
     * Create new application
     *
     * @param string  environment The environment
     * @param boolean debug Whether to enable debugging or not
     * @param array config Optional config settings.
     */
    public function __construct($environment = 'prod', $debug = false, $context = null) {
        $this->context = $context;
        Toolbox::setEnvironment($environment);
        Runtime::setContext($this->context);
        parent::__construct($environment, $debug);
        $this->startTime = microtime(true);

        $this->initSettings();
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
        // @todo fold this into a store only database loader
        $configService = new \zenmagick\apps\store\services\ConfigService;
        foreach ($configService->loadAll() as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }
        $defaults = $this->getRootDir().'/apps/store/config/defaults.php';
        if (file_exists($defaults)) {
            $settingsService = $this->settingsService;
            include $defaults;
            $this->settingsService = $settingsService;
        }
        $appContainerFiles = array('lib/zenmagick/base/container.xml');
        $appContainerFiles[] = 'lib/zenmagick/http/container.xml';
        if ($applicationPath = $this->getApplicationPath()) {
            $appContainerFiles[] = $applicationPath.'/config/container.xml';
        }
        $appContainerFiles[] = 'config/store-container.xml';

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

    /*
     * {@inheritDoc}
     *
     * @param array keys
     */
    public function boot(array $keys = null) {
        if (true === $this->booted) {
            return;
        }

        $this->initializeBundles();

        $this->initializeContainer();

        foreach ($this->getBundles() as $bundle) {
            $bundle->setContainer($this->container);
            $bundle->boot();
        }
        $this->bootZM($keys);
        $this->booted = true;
    }

    /**
     * Bootstrap application.
     *
     * @param array keys Optional list of bootstrap block keys to run; default is <code>null</code> for all.
     */
    public function bootZM(array $keys=null) {
        $settingsService = $this->container->get('settingsService');
        // @todo switch to using tagged services for events.
        foreach ($settingsService->get('zenmagick.base.events.listeners', array()) as $eventListener) {
            if (!class_exists($eventListener)) continue;
            if (null != ($eventListener = new $eventListener)) {
                $eventListener->setContainer($this->container);
                $this->container->get('eventDispatcher')->listen($eventListener);
            }
        }
        if (empty($keys) || in_array('bootstrap', (array)$keys)) {
            $this->initEmail();
            $this->container->get('localeService')->init($settingsService->get('zenmagick.base.locales.locale', 'en'));

            $this->container->get('pluginService')->getPluginsForContext($this->getContext());
            $this->fireEvent('request_ready');
        }

        if (!($this->container->getParameterBag() instanceof FrozenParameterBag)) {
            $this->container->compile();
        }

        if (empty($keys) ||in_array('bootstrap', (array)$keys)) {
            $this->fireEvent('container_ready');
        }
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
     * Get the application context.
     *
     * @return string The application context.
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * {@inheritDoc}
     *
     * @todo remove this stub once we're ready
     */
    public function loadClassCache($name = 'classes', $extension = '.php') {
    }

    /**
     * Fire event.
     *
     * @param string eventName The event name.
     * @param array parameter Optional parameter; default is an empty array.
     */
    public function fireEvent($eventName, array $parameter=array()) {
        if ('cli' == php_sapi_name()) return;
        $parameter['kernel'] = $this;
        if (in_array($eventName, array('request_ready', 'container_ready'))) {
            $parameter['request'] = $this->container->get('request');
        }

        $this->container->get('eventDispatcher')->dispatch($eventName, new Event($this, $parameter));
    }

    /**
     * {@inheritDoc}
     *
     * Just like parent::buildContainer except the compilation step is not done here.
     *
     * @copyright Fabien Potencier <fabien@symfony.com>
     * @todo compile here
     */
    protected function buildContainer() {
        foreach (array('cache' => $this->getCacheDir(), 'logs' => $this->getLogDir()) as $name => $dir) {
            if (!is_dir($dir)) {
                if (false === @mkdir($dir, 0777, true)) {
                    throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", $name, $dir));
                }
            } elseif (!is_writable($dir)) {
                throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", $name, $dir));
            }
        }
        $container = $this->getContainerBuilder();
        $extensions = array();
        foreach ($this->bundles as $bundle) {
            if ($extension = $bundle->getContainerExtension()) {
                $container->registerExtension($extension);
                $extensions[] = $extension->getAlias();
            }

            if ($this->debug) {
                $container->addObjectResource($bundle);
            }
        }
        foreach ($this->bundles as $bundle) {
            $bundle->build($container);
        }

        $container->addObjectResource($this);

        // ensure these extensions are implicitly loaded
        $container->getCompilerPassConfig()->setMergePass(new MergeExtensionConfigurationPass($extensions));

        if (null !== $cont = $this->registerContainerConfiguration($this->getContainerLoader($container))) {
            $container->merge($cont);
        }

        $container->addCompilerPass(new AddClassesToCachePass($this));
        //$container->compile();
        return $container;
    }

    /**
     * Init some basic settings.
     *
     * @param array array of settings
     *
     * @todo take a Settings instance?
     */
    protected function initSettings() {
        $this->settingsService = new Settings;
        $settingsFiles = array();
        $settingsService = $this->settingsService;
        if ($applicationPath = $this->getApplicationPath()) {
            $settingsFiles[] = $applicationPath.'/config/config.yaml';
        }
        // @todo do something better for command line.
        if (!in_array($this->getContext(), array('admin', 'storefront'))) {
            $settingsFiles[] = $this->getRootDir().'/config/store-config.yaml';
        }
        foreach ($settingsFiles as $config) {
            if (file_exists($config)) {
                $settingsService->setAll(Toolbox::loadWithEnv($config));
            }
        }

        $globalFilename = realpath($this->getRootDir().'/global.yaml');
        if (file_exists($globalFilename)) {
            $contextConfigLoader = new \zenmagick\base\utils\ContextConfigLoader;
            $contextConfigLoader->setConfig(Toolbox::loadWithEnv($globalFilename));
            $contextConfigLoader->setSettingsService($settingsService);
            $config = $contextConfigLoader->resolve($this->getContext());
            unset($config['container']); // @todo merge this with the other container configuration if we want to keep it.
            $contextConfigLoader->apply($config);
        }

        if (null == $settingsService->get('apps.store.zencart.path')) { // @todo or default to vendors/zencart?
            $settingsService->set('apps.store.zencart.path', dirname($this->getRootDir()));
        }

        if (null != ($tz = $settingsService->get('zenmagick.core.date.timezone'))) {
            date_default_timezone_set($tz);
        }
        \ZMRuntime::setDatabase('default', $settingsService->get('apps.store.database.default'));
    }

    /**
     * {@inheritDoc}
     */
    public function getContainerBuilder() {
        $parameterBag = new SettingsParameterBag($this->getKernelParameters());
        $parameterBag->setSettings($this->settingsService);
        return new ContainerBuilder($parameterBag);
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

        $container->get('settingsService')->setAll($this->settingsService);
        Runtime::setContainer($this->container);
    }

    /**
     * {@inheritDoc}
     *
     * Modified to add kernel.context parameter.
     */
    protected function getKernelParameters() {
        $parameters = parent::getKernelParameters();
        if (empty($parameters)) return; // if it's empty leave it empty.
        $parameters['kernel.context'] = $this->getContext();
        return $parameters;
    }

    /**
     * Initialize email.
     *
     * @todo not the final home. move it closer to the container configuration.
     */
    public function initEmail() {
        // load email container config once all settings/config is loaded
        $emailConfig = Runtime::getInstallationPath().'/config/store-email.xml';
        if (file_exists($emailConfig)) {
            $containerlLoader = new XmlFileLoader($this->container, new FileLocator(dirname($emailConfig)));
            $containerlLoader->load($emailConfig);
        }

        $key = 'zenmagick.base.email.host';
        // enable encryption for gmail smtp
        if ($this->container->getParameterBag()->has($key)) {
            if ('smtp.gmail.com' == $this->container->getParameterBag()->get($key)) {
                $this->container->getParameterBag()->set('zenmagick.base.email.encryption', 'tls');
            }
        }

        if ($this->container->has('swiftmailer.transport')) {
            if (null != ($transport = $this->container->get('swiftmailer.transport')) && $transport instanceof Swift_Transport_EsmtpTransport) {
                $transport->setEncryption($this->container->getParameterBag()->get('zenmagick.base.email.encryption'));
            }
        }

        // load email container config unless we do have already some swiftmailer config
        $bundles = array_keys($this->container->get('settingsService')->get('zenmagick.bundles', array()));
        if (0 == count($this->container->getExtensionConfig('swiftmailer')) && in_array('SwiftmailerBundle', $bundles)) {
            $emailConfig = __DIR__.'/email.xml';
            if (file_exists($emailConfig)) {
                $containerLoader = new XmlFileLoader($this->container, new FileLocator(dirname($emailConfig)));
                $containerLoader->load($emailConfig);
            }
        }
    }
}
