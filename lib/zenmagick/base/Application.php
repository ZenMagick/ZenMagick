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
            new \zenmagick\apps\store\bundles\ZenCartBundle\ZenCartBundle,
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle,
            new \Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle,
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle,
            new \Symfony\Bundle\MonologBundle\MonologBundle,
            new \Symfony\Bundle\TwigBundle\TwigBundle,
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle,
            new \zenmagick\base\ZenMagickBundle,
        );
        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
        }
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
        $parameters['kernel.context_dir'] = $this->getApplicationPath();

        $resources = array();
        $resources[] = function($container) use($parameters) {
            $container->setParameter('database_driver', $parameters['driver']);
            $container->setParameter('database_host', $parameters['host']);
            $container->setParameter('database_name', $parameters['dbname']);
            $container->setParameter('database_user', $parameters['user']);
            $container->setParameter('database_password', $parameters['password']);
            $container->setParameter('database_prefix', $parameters['prefix']);
            $container->setParameter('locale', isset($parameters['locale']) ? $parameters['locale'] : 'en');
        };

        $session = array();

        if (in_array($this->getContext(), array('admin', 'storefront', 'store'))) {
            $configService = new \zenmagick\apps\store\services\ConfigService;
            foreach ($configService->loadAll() as $key => $value) {
                if (!defined($key)) {
                    define($key, $value);
                }
            }

            $session['handler_id'] = 'session.handler.pdo';
        }

        $resources[] = $this->getRootDir().'/apps/store/config/configuration.php';

        $session['name'] = 'zm-%kernel.context%';
        $session['gc_probability'] = 1;
        $session['gc_divisor'] = 2;
        $session['gc_maxlifetime'] = '%zenmagick.http.session.timeout%';
        $session['cookie_lifetime'] = 0;
        $session['cookie_path'] = '/';
        $session['cookie_httponly'] = true;
        $session['cookie_secure'] = false;

        $parameters['session'] = $session;

        $resources[] = function($container) use($parameters) {
            $container->loadFromExtension('framework', array(
                'default_locale' => '%locale%',
                'secret' => 'notsecret',
                'router' => array(
                    // @todo use a real file :)
                    'resource' => $parameters['kernel.context_dir'].'/config/routing.xml',
                ),
                'session' => $parameters['session'],
                'templating' => array(
                    'engines' => array('php', 'twig')),
            ));

            // Monolog configuration is equivalent to config in symfony-standard.
            if ('prod' == $container->getParameter('kernel.environment')) {
                $monolog = array(
                    'handlers' => array(
                        'main' => array(
                            'type' => 'fingers_crossed',
                            'action_level' => 'error',
                            'handler' => 'nested',
                        ),
                        'nested' => array(
                            'type' => 'stream',
                            'path' => '%kernel.logs_dir%/%kernel.context%-%kernel.environment%.log',
                            'level' => 'debug',
                        ),
                    ),
                );
            } else {
                $monolog = array(
                    'handlers' => array(
                        'main' => array(
                            'type' => 'stream',
                            'path' => '%kernel.logs_dir%/%kernel.context%-%kernel.environment%.log',
                            'level' => 'debug',
                        ),
                        'firephp' => array(
                            'type' => 'firephp',
                            'level' => 'info',
                        )
                    )
                );
            }
            $container->setParameter('monolog.logger.class', 'zenmagick\base\logging\Logging');
            $container->loadFromExtension('monolog', $monolog);
            /*$container->loadFromExtension('web_profiler', array(
                'toolbar' => true,
            ));*/
            $container->loadFromExtension('zenmagick', array(
            ));


        };

        $resources[] = $this->getRootDir().'/apps/store/config/email.php';

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
