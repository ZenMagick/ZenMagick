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
namespace ZenMagick\Base;

use ZenMagick\Base\Runtime;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

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
            new \ZenMagick\ZenCartBundle\ZenCartBundle,
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle,
            new \Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle,
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle,
            new \Symfony\Bundle\MonologBundle\MonologBundle,
            new \Symfony\Bundle\TwigBundle\TwigBundle,
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle,
            new \ZenMagick\Base\ZenMagickBundle,
            new \ZenMagick\apps\admin\AdminBundle,
            new \ZenMagick\apps\storefront\StorefrontBundle,
        );
        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
        }
        return $bundles;
    }

    /**
     * {@inheritDoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader) {
        $context = $this->getContext();

        $resources[] = $this->getRootDir().'/app/config/parameters.yml';
        // used to set the basic parameters to fill config_$env.yml files
        $resources[] = $this->getRootDir().'/apps/store/config/configuration.php';
        $resources[] = $this->getRootDir().'/apps/store/config/email.php';
        // extension configuration
        $resources[] = $this->getRootDir().'/app/config/config_'.$this->getEnvironment().'.yml';

        // @todo remove this when we we can prove we don't need $_SESSION
        $resources[] = function($container) use($context) {
            if ('storefront' == $context) {
                $container->setParameter('session.class', 'ZenMagick\apps\storefront\http\Session');
            }
        };

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
        return 'ZenMagick\Base\DependencyInjection\Container';
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
     * Modified to add kernel.context parameter.
     */
    protected function getKernelParameters() {
        $parameters = parent::getKernelParameters();
        $parameters['kernel.context'] = $this->getContext();
        $parameters['kernel.context_dir'] = $this->getApplicationPath();
        return $parameters;
    }
}
