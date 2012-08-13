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

use Exception;
use zenmagick\base\Runtime;
use zenmagick\base\Beans;
use zenmagick\base\settings\Settings;
use zenmagick\base\Toolbox;
use zenmagick\base\ZMException;
use zenmagick\base\dependencyInjection\ContainerBuilder;
use zenmagick\base\dependencyInjection\parameterBag\SettingsParameterBag;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;
use Symfony\Component\HttpKernel\DependencyInjection\AddClassesToCachePass;

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
        Runtime::setContext($this->context);
        parent::__construct($environment, $debug);
        $this->startTime = microtime(true);
    }

    /**
     * Register Bundle classes.
     *
     * @return array instantiated bundle objects
     */
    public function registerBundles() {
        $bundles = array(
            new \zenmagick\base\ZenMagickBundle,
            new \zenmagick\apps\store\bundles\ZenCartBundle\ZenCartBundle,
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle,
            new \Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle,
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle,
        );
        return $bundles;
    }

    /**
     * {@inheritDoc}
     * @see Symfony\Component\HttpKernel\KernelInterface
     * @todo move most this into "a" bundle.
     */
    public function registerContainerConfiguration(LoaderInterface $loader) {
        $settingsService = new Settings;
        $settingsFiles = array();
        $settingsFiles[] = $this->getRootDir().'/apps/base/config/config.yaml';
        $settingsFiles[] = $this->getApplicationPath().'/config/config.yaml';
        // @todo do something better for non store apps
        $settingsFiles[] = $this->getRootDir().'/config/store-config.yaml';
        foreach ($settingsFiles as $config) {
            if (file_exists($config)) {
                $settingsService->load($config);
            }
        }

        $globalFilename = realpath($this->getRootDir().'/global.yaml');
        if (file_exists($globalFilename)) {
            $contextConfigLoader = new \zenmagick\base\utils\ContextConfigLoader;
            $contextConfigLoader->setConfig($globalFilename);
            $contextConfigLoader->setSettingsService($settingsService);
            $config = $contextConfigLoader->resolve($this->getContext());
            unset($config['container']); // @todo merge this with the other container configuration if we want to keep it.
            $contextConfigLoader->apply($config);
        }

        $this->settingsService = $settingsService;
        \ZMRuntime::setDatabase('default', $settingsService->get('apps.store.database.default'));

        if (in_array($this->getContext(), array('admin', 'storefront', 'store'))) {
            $configService = new \zenmagick\apps\store\services\ConfigService;
            foreach ($configService->loadAll() as $key => $value) {
                if (!defined($key)) {
                    define($key, $value);
                }
            }

            $defaults = $this->getRootDir().'/apps/store/config/defaults.php';
            if (file_exists($defaults)) {
                include $defaults;
            }
        }

        $appContainerFiles = array();
        $appContainerFiles[] = $this->getRootDir().'/lib/zenmagick/base/container.xml';
        $appContainerFiles[] = $this->getRootDir().'/lib/zenmagick/http/container.xml';
        $appContainerFiles[] = $this->getRootDir().'/apps/store/config/email.php';
        $appContainerFiles[] = $this->getApplicationPath().'/config/container_'.$this->getEnvironment().'.xml';

        $files = array();
        foreach ($appContainerFiles as $file) {
            if (file_exists($file)) {
                $files[] = $file;
            }
        }
        // @todo move all zencart specific parameteres to bundle
        $zcDir = realpath(dirname($this->getRootDir()));

        $files[] = function($container) use ($zcDir) {
            $container->setParameter('zencart.root_dir', $zcDir);
            $container->setParameter('zencart.admin_dir', defined('ZENCART_ADMIN_FOLDER') ? ZENCART_ADMIN_FOLDER : 'admin');
        };

        foreach ($files as $file) {
            $loader->load($file);
        }
    }


    public function boot() {
        parent::boot();
        $this->initEvents();
    }

    /**
     * Bootstrap application.
     *
     */
    public function initEvents() {
        $settingsService = $this->container->get('settingsService');

        // @todo switch to using tagged services for events.
        $listeners = $settingsService->get('zenmagick.base.events.listeners', array());
        $plugins = $this->container->get('pluginService')->getPluginsForContext($this->getContext());
        $listeners = array_merge($listeners, $plugins);

        if ('storefront' == $this->getContext()) {
            $listeners[] = sprintf('zenmagick\themes\%s\EventListener', $this->container->get('themeService')->getActiveThemeId());
        }

        // @todo switch to using tagged services for events.
        foreach ($listeners as $eventListener) {
            if (is_string($eventListener)) {
                if (!class_exists($eventListener)) continue;
                if (null != ($eventListener = new $eventListener)) {
                    $eventListener->setContainer($this->container);
                }
            }
            if (is_object($eventListener)) {
                $this->container->get('eventDispatcher')->listen($eventListener);
            }
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
     * {@inheritDoc}
     */
    public function getContainerBuilder() {
        return new ContainerBuilder(new ParameterBag($this->getKernelParameters()));
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
        $this->container->get('settingsService')->setAll($this->settingsService);
        Runtime::setContainer($this->container);
        $plugins = $this->container->get('pluginService')->getPluginsForContext($this->getContext());
        if ('storefront' == $this->getContext()) {
            $this->container->get('themeService')->initThemes();
        }

        $this->container->compile();

        foreach($this->container->getParameterBag()->all()  as $param => $value) {
            $this->container->get('settingsService')->set($param, $value);
        }

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
        $parameters['kernel.context_dir'] = $this->getApplicationPath();
        return $parameters;
    }
}
