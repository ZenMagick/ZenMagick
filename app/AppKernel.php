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

use ZenMagick\Base\Runtime;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Base application.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AppKernel extends Kernel
{
    const APP_VERSION = '0.9.13';
    const EXTERNAL_CACHE_BASE_TOGGLE = 'SYMFONY__ZM_USE_EXTERNAL_CACHE';
    const EXTERNAL_USER_DIR_KEY = 'SYMFONY__ZM_USER';
    const EXTERNAL_HOST_DIR_KEY = 'SYMFONY__ZM_HOST';

    protected $context;

    /**
     * Create new application
     *
     * @param string  environment The environment
     * @param boolean debug Whether to enable debugging or not
     * @param array config Optional config settings.
     */
    public function __construct($environment = 'prod', $debug = false, $context = null)
    {
        $this->context = $context;
        // @todo FIXME: Only save it the first time. this gets called again via ConfigCache
        if (null === Runtime::getContext()) {
            Runtime::setContext($context);
        }

        parent::__construct($environment, $debug);
    }

    /**
     * {@inheritDoc}
     */
    public function getRootDir()
    {
        return __DIR__;
    }

    /**
     * Register Bundle classes.
     *
     * @return array instantiated bundle objects
     */
    public function registerBundles()
    {
        $bundles = array(
            new ZenMagick\ZenCartBundle\ZenCartBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new ZenMagick\ZenMagickBundle\ZenMagickBundle(),
            new ZenMagick\StoreBundle\StoreBundle(),
            new ZenMagick\AdminBundle\AdminBundle(),
            new ZenMagick\StorefrontBundle\StorefrontBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            // @todo default or not? either way make it toggleable
            //new Jmikola\InsecureRoutesBundle\JmikolaInsecureRoutesBundle(),
        );
        if (in_array($this->getEnvironment(), array('dev', 'test', 'install'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle;
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle;
        }

        return $bundles;
    }

    /**
     * {@inheritDoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {

        $resources[] = $this->getRootDir().'/config/parameters.yml';
        $rootDir = dirname($this->getRootDir());

        $resources[] = $rootDir.'/config/parameters.yml';
        // used to set the basic parameters to fill config_$env.yml files
        $resources[] = $this->getRootDir().'/config/configuration.php';
        // extension configuration
        $resources[] = $this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml';

        $resources[] = $rootDir.'/config/container.xml';

        foreach ($resources as $resource) {
            if (is_string($resource) && !file_exists($resource)) {
                continue;
            }
            $loader->load($resource);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getContainerBaseClass()
    {
        return 'ZenMagick\ZenMagickBundle\DependencyInjection\Container';
    }

    /**
     * Get the application context.
     *
     * @return string The application context.
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritDoc}
     *
     * Modified to add kernel.context parameter.
     */
    protected function getKernelParameters()
    {
        $parameters = parent::getKernelParameters();
        $parameters['kernel.context'] = $this->getContext();
        $parameters['zenmagick.root_dir'] = dirname($this->getRootDir()); // @todo remove this
        // @todo temporary helper parameter until context is gone.
        return $parameters;
    }

    /**
     * @inheritDoc
     *
     * contains some experimental code for custom cache directories
     * for multiple sites.
     * @todo find something that works for both console and web
     * @todo don't cache non site specific resources.
     */
    public function getCacheDir()
    {
        if (isset($_SERVER[self::EXTERNAL_CACHE_BASE_TOGGLE])) {
            return sprintf('/var/zenmagick/%s/%s/cache/%s',
                $_SERVER[self::EXTERNAL_USER_DIR_KEY],
                $_SERVER[self::EXTERNAL_HOST_DIR_KEY],
                $this->environment);
        }

        return parent::getCacheDir();
    }

    /**
     * @inheritDoc
     *
     * contains some experimental code for custom log directories
     * for multiple sites.
     *
     * @todo find something that works for both console and web
     */
    public function getLogDir()
    {
        if (isset($_SERVER[self::EXTERNAL_CACHE_BASE_TOGGLE])) {
            return sprintf('/var/zenmagick/%s/%s/logs',
                $_SERVER[self::EXTERNAL_USER_DIR_KEY],
                $_SERVER[self::EXTERNAL_HOST_DIR_KEY]);
        }

        return parent::getLogDir();
    }
}
