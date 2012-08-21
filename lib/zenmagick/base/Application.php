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

use zenmagick\base\Runtime;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;
use Symfony\Component\HttpKernel\DependencyInjection\AddClassesToCachePass;

/**
 * Base application.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Application extends Kernel {
    protected $context;

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
            new \zenmagick\apps\store\bundles\ZenCartBundle\ZenCartBundle,
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle,
            new \Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle,
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle,
            new \Symfony\Bundle\MonologBundle\MonologBundle,
            new \zenmagick\base\ZenMagickBundle,
        );
        return $bundles;
    }

    /**
     * {@inheritDoc}
     * @see Symfony\Component\HttpKernel\KernelInterface
     * @todo move most this into "a" bundle.
     */
    public function registerContainerConfiguration(LoaderInterface $loader) {
        $config = $this->getRootDir().'/config/store-config.yaml';
        $yaml = \Symfony\Component\Yaml\Yaml::parse($config);

        $parameters = $yaml['apps']['store']['database']['default'];
        \ZMRuntime::setDatabase('default', $parameters);
        $parameters['kernel.context'] = $this->getContext();

        $resources = array();
        $resources[] = function($container) use($parameters) {
            $container->setParameter('database_driver', $parameters['driver']);
            $container->setParameter('database_host', $parameters['host']);
            $container->setParameter('database_name', $parameters['dbname']);
            $container->setParameter('database_user', $parameters['user']);
            $container->setParameter('database_password', $parameters['password']);
            $container->setParameter('database_prefix', $parameters['prefix']);
        };

        if (in_array($this->getContext(), array('admin', 'storefront', 'store'))) {
            $configService = new \zenmagick\apps\store\services\ConfigService;
            foreach ($configService->loadAll() as $key => $value) {
                if (!defined($key)) {
                    define($key, $value);
                }
            }
        }

        $resources[] = $this->getRootDir().'/lib/zenmagick/base/container.xml';
        $resources[] = $this->getRootDir().'/lib/zenmagick/http/container.xml';
        $resources[] = function($container) use($parameters) {
            if ('admin' == $parameters['kernel.context']) {
                $container->setParameter('zenmagick.http.sacs.mappingProviders', array('zenmagick\apps\admin\services\DBSacsPermissionProvider'));
            }
        };

        $resources[] = $this->getRootDir().'/apps/store/config/email.php';
        $resources[] = $this->getRootDir().'/apps/store/config/configuration.php';
        $resources[] = $this->getApplicationPath().'/config/container_'.$this->getEnvironment().'.xml';

        foreach ($resources as $resource) {
            if (is_string($resources) && !file_exists($resource)) {
                continue;
            }
            $loader->load($resource);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getContainerBaseClass() {
        return 'zenmagick\base\dependencyInjection\Container';
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
     * @todo remove local modifications
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
        $plugins = $container->get('pluginService')->getPluginsForContext($this->getContext());
        if ('storefront' == $this->getContext()) {
            $container->get('themeService')->initThemes();
        }
        $container->compile();
        return $container;
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
