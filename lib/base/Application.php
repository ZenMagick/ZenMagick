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
namespace zenmagick\base;

use DateTime;
use Exception;
use zenmagick\base\Runtime;
use zenmagick\base\Beans;
use zenmagick\base\classloader\ClassLoader;
use zenmagick\base\Toolbox;
use zenmagick\base\events\Event;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Base application.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @todo: document all config options
 */
class Application {
    protected $bootstrap;
    protected $config;
    protected $classLoader;
    protected $profile;
    protected $bundles;


    /**
     * Create new application
     *
     * @param array config Optional config settings.
     */
    public function __construct(array $config=array()) {
        $defaults = array(
            // general stuff
            'timerStart' => microtime(),
            'installationPath' => dirname(dirname(__DIR__)),
            'cli' => defined('STDIN'),
            'profile' => false,

            // packages
            'packageBase' => 'zenmagick',
            'packages' => array('vendor', 'lib/base', 'lib/core', 'shared', 'vendor/local'),

            'classLoader' => 'zenmagick\base\classloader\CachingClassLoader',
            'eventListener' => array('zenmagick\base\EventListener'),

            'bundles' => array(),

            // app stuff
            'appName' => null,
            'environment' => 'prod',
            'context' => null,
            'defaultLocale' => 'en',
            'appConfig' => array(),

            // ini
            'display_errors'=> false,
            'error_reporting' => -1,
            'log_errors' => true
        );
        $this->config = array_merge($defaults, $config);
        // some derived config
        $this->config['context'] = $this->config['context'] ? $this->config['context'] : $this->config['appName'];
        $this->config['applicationPath'] = $this->config['appName'] ? sprintf('%s/apps/%s', $this->config['installationPath'], $this->config['appName']) : null;

        $this->profile = array();
        $this->bundles = array();

        // init
        ini_set('display_errors', $this->config['display_errors']);
        error_reporting($this->config['error_reporting']);
        ini_set('log_errors', $this->config['log_errors']);
        $this->initBootstrap();
    }


    /**
     * Add a bootstrap block after the given key.
     *
     * <p>A bootstrap is a map that needs a unique <em>key</em> field and optionally:</p>
     * <ul>
     *  <li>a method list; eg <code>'methods' => array('foo', 'bar')</code></li>
     *  <li>a preEvent; eg <code>'preEvent' => 'before_foo'</code></li>
     *  <li>a postEvent; eg <code>'postEvent' => 'after_foo'</code></li>
     * </li>
     *
     * <p>If <code>$key</code> is <code>null</code> the block will be appended at the end of the current bootstrap sequence.<p>
     *
     * @param string key The bootstrap order key.
     * @param array block bootstrap block.
     */
    public function addBootstrapAfter($key, array $block) {
        if (null != $key) {
            // find key position
            $pos = -1;
            for ($ii=0; $ii < count($this->bootstrap); ++$ii) {
                if ($key == $this->bootstrap[$ii]['key']) {
                    $pos = $ii+1;
                    break;
                }
            }
        } else {
            $pos = count($this->bootstrap);
        }
        if (-1 != $pos) {
            array_splice($this->bootstrap, $pos, 0, array($block));
        }
    }

    /**
     * Get the application name.
     *
     * @return string The application name or <code>null</code>.
     */
    public function getApplicationName() {
        return $this->config['appName'];
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
     * Get environment.
     *
     * @return string The current environment or prod if not set.
     */
    public function getEnvironment() {
        return $this->config['environment'];
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
        $this->bootstrap = array(
            array(
                'key' => 'init',
                'methods' => array(
                    'initClassLoader',
                    'loadPackages',
                    'initLogging',
                    'initSettings',
                    'initRuntime',
                    'initApplicationConfig',
                    'initGlobal',
                    'initLogging',
                    'loadBundles',
                    'initApplicationContainer',
                    'initEventListener'
                ),
                'postEvent' => 'init_config_done'
            ),
            array(
                'key' => 'bootstrap',
                'methods' => array('initLocale', 'initPlugins'),
                'postEvent' => 'bootstrap_done'
            ),
            array(
                'key' => 'container',
                'methods' => array('compileContainer'),
                'postEvent' => 'container_ready'
            )
        );
    }

    /**
     * Get application config.
     *
     * @return array Map of application configuration.
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Get enabled bundles.
     *
     * @return array List of enabled bundle objects.
     */
    public function getBundles() {
        return $this->bundles;
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
                $time = microtime();
                $this->profile[] = array('text' => $text, 'time' => $time, 'elapsed' => $this->getElapsedTime($time));
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
        $startTime = explode(' ', $this->config['timerStart']);
        $endTime = explode(' ', (null != $time ? $time : microtime()));
        // $time might be float
        if (1 == count($endTime)) { $endTime[] = 0; }
        $executionTime = $endTime[1]+$endTime[0]-$startTime[1]-$startTime[0];
        return round($executionTime, 4);
    }

    /**
     * Init class loader.
     */
    protected function initClassLoader() {
        // set up base class loader
        $installationPath = $this->config['installationPath'];
        $basephar = 'phar://'.$installationPath.'/lib/base/base.phar';

        // NOTE: the base package has a flattened folder structure, so the path doesn't reflect the namespace
        if (file_exists($basephar)) {
            require_once $basephar.'/classloader/ClassLoader.php';
            require_once $basephar.'/classloader/CachingClassLoader.php';
        } else {
            require_once $installationPath.'/lib/base/classloader/ClassLoader.php';
            require_once $installationPath.'/lib/base/classloader/CachingClassLoader.php';
        }

        $this->classLoader = new $this->config['classLoader']();
        $this->classLoader->register();
    }

    /**
     * Fire event.
     *
     * @param string eventName The event name.
     * @param array parameter Optional parameter; default is an empty array.
     */
    protected function fireEvent($eventName, array $parameter=array()) {
        $parameter['application'] = $this;
        Runtime::getEventDispatcher()->dispatch($eventName, new Event($this, $parameter));
    }

    /**
     * Bootstrap application.
     *
     * @param array keys Optional list of bootstrap block keys to run; default is <code>null</code> for all.
     */
    public function bootstrap(array $keys=null) {
        try {
            foreach ($this->bootstrap as $ii => $step) {
                if (array_key_exists('done', $step) || (null !== $keys && !in_array($step['key'], $keys))) {
                    continue;
                }

                if (array_key_exists('preEvent', $step)) {
                    $eventName = $step['preEvent'];
                    $this->fireEvent($eventName);
                    $this->profile(sprintf('finished event: %s', $eventName));
                }
                foreach ((array)$step['methods'] as $method) {
                    $this->$method();
                    $this->profile(sprintf('exit method: %s', $method));
                }
                if (array_key_exists('postEvent', $step)) {
                    $eventName = $step['postEvent'];
                    $this->fireEvent($eventName);
                    $this->profile(sprintf('finished event: %s', $eventName));
                }
                $this->bootstrap[$ii]['done'] = true;
            }
        } catch (Exception $e) {
            echo $e->getTraceAsString();
            die(sprintf('bootstrap failed: %s', $e->getMessage()));
        }
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
                    $this->classLoader->addConfig($ppath);
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

        $settingsService->set('zenmagick.environment', $this->config['environment']);
        $settingsService->set('zenmagick.installationPath', $this->config['installationPath']);
        $settingsService->set('zenmagick.base.context', $this->config['context'] ? $this->config['context'] : $this->config['appName']);

        // as default disable plugins for CLI calls
        $settingsService->set('zenmagick.base.plugins.enabled', !$this->config['cli']);
    }

    /**
     * Init runtime.
     */
    protected function initRuntime() {
        // register this as 'application'
        Runtime::getContainer()->set('application', $this);
    }

    /**
     * Init application config.
     */
    protected function initApplicationConfig() {
        $settingsService = Runtime::getSettings();
        if ($applicationPath = $this->config['applicationPath']) {
            $appLoader = new $this->config['classLoader']();
            $appLoader->register();
            $appLoader->addConfig($applicationPath.'/lib');

            $settingsService->setAll(Toolbox::loadWithEnv($applicationPath.'/config/config.yaml'));
        }

        foreach ($this->config['appConfig'] as $config) {
            if (file_exists($config)) {
                $settingsService->setAll(Toolbox::loadWithEnv($config));
            }
        }
    }

    /**
     * Init global.
     */
    protected function initGlobal() {
        $container = Runtime::getContainer();
        $globalFilename = realpath($this->config['installationPath'].'/global.yaml');
        if (file_exists($globalFilename) && $container->has('contextConfigLoader')) {
            $contextConfigLoader = $container->get('contextConfigLoader');
            $contextConfigLoader->setConfig(Toolbox::loadWithEnv($globalFilename));
            $contextConfigLoader->process();
        }
    }

    /**
     * Load bundles.
     */
    protected function loadBundles() {
        $container = Runtime::getContainer();
        $settingsService = Runtime::getSettings();

        // TODO?: this might be less than HttpKernel does
        $extensions = array();
        $bundles = array_merge($settingsService->get('zenmagick.bundles', array()), $this->config['bundles']);
        foreach ($bundles as $key => $class) {
            $bundle = new $class();
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
     * Init application container.
     */
    protected function initApplicationContainer() {
        if ($applicationPath = $this->config['applicationPath']) {
            $container = Runtime::getContainer();
            $containerConfig = Toolbox::resolveWithEnv($applicationPath.'/config/container.xml');
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
        if ($applicationPath = $this->config['applicationPath'] && $this->config['appName']) {
            // always add an application event listener - if available
            $eventListener = sprintf('zenmagick\apps\%s\EventListener', $this->config['appName']);
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
        Runtime::getLogging()->debug(sprintf('environment is: %s', $this->config['environment']));
    }

    /**
     * Init locale.
     */
    protected function initLocale() {
        $container = Runtime::getContainer();
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
        $container = Runtime::getContainer();
        $settingsService = Runtime::getSettings();

        if ($settingsService->get('zenmagick.base.plugins.enabled', true)) {
            $container->get('pluginService')->initAllPlugins($this->config['context']);
        }
    }

    /**
     * Compile container.
     */
    protected function compileContainer() {
        $container = Runtime::getContainer()->compile();
    }

}
