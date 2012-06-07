<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace zenmagick\apps\store\bundles\ZenCartBundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Loader\XmlFileLoader;

use Swift_Transport_SendmailTransport;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\dependencyInjection\loader\YamlLoader;
use zenmagick\apps\store\bundles\ZenCartBundle\utils\EmailEventHandler;
use zenmagick\apps\store\menu\MenuLoader;

/**
 * Zencart support bundle.
 *
 * @author DerManoMann
 */
class ZenCartBundle extends Bundle {
    const ZENCART_ADMIN_FOLDER = 'ZENCART_ADMIN_FOLDER';

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container) {
        parent::build($container);
    }

    /**
     * {@inheritDoc}
     */
    public function boot() {
        $settingsService = Runtime::getSettings();
        if (null == $settingsService->get('apps.store.zencart.path')) { // @todo or default to vendors/zencart?
            $settingsService->set('apps.store.zencart.path', dirname(Runtime::getInstallationPath()));
        }

        $eventDispatcher = Runtime::getEventDispatcher();
        $eventDispatcher->listen($this);
        $eventDispatcher->addListener('generate_email', array(Beans::getBean('zenmagick\apps\store\bundles\ZenCartBundle\utils\EmailEventHandler'), 'onGenerateEmail'));

        // random defines that we might need
        if (!defined('PRODUCTS_OPTIONS_TYPE_SELECT')) { define('PRODUCTS_OPTIONS_TYPE_SELECT', 0); }
        if (!defined('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL')) { define('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL', 0); }
        if (!defined('TEXT_PREFIX')) { define('TEXT_PREFIX', 'txt_'); }
        if (!defined('UPLOAD_PREFIX')) { define('UPLOAD_PREFIX', 'upload_'); }
    }

    public static function buildSearchPaths($base = '') {
        $settingsService = Runtime::getSettings();
        $zcPath = $settingsService->get('apps.store.zencart.path');
        $dirs = array(dirname(__FILE__).'/bridge', $zcPath);
        if (Runtime::isContextMatch('admin')) {
            $adminDir = $settingsService->get('apps.store.zencart.admindir');
            $adminDirs = array(dirname(__FILE__).'/bridge/admin', $zcPath.'/'.$adminDir);
            $dirs = false !== strpos($base, 'classes') ? array_merge($adminDirs, $dirs) : $adminDirs;
        }

        $overrides = (false !== strpos($base, 'auto_loaders') || false !== strpos($base, 'init_includes'));
        $searchPaths = array();
        foreach ($dirs as $dir) {
            if ($overrides) {
                $searchPaths[] = $dir.'/'.$base.'/overrides';
            }
            $searchPaths[] = $dir.'/'.$base;
        }
        return $searchPaths;
    }

    /**
     * Resolve some templated file vars
     *
     *
     * @todo refactor this into a different class
     */
    public static function resolveFileVars($string) {
        $container = Runtime::getContainer();
        $request = $container->get('request');
        $map = array();
        $map['%current_page%'] = $request->getRequestId();
        $map['%language%'] = $request->getSelectedLanguage()->getDirectory();
        $map['%template_dir%'] = $container->get('themeService')->getActiveThemeId();
        return str_replace(array_keys($map), array_values($map), $string);
    }

    /**
     * Find Zen Cart init system files
     *
     * We check our bridge directory before falling back on ZenCart native files
     *
     * @param mixed string|array $paths path or paths to file or files, can be a glob.
     * @returns array array of absolute paths to files indexed by file basename.
     */
    public static function resolveFiles($paths) {
        $files = array();

        foreach ((array)$paths as $path) {
            $path = self::resolveFileVars($path);
            $file = basename($path);
            $relative = dirname($path);
            $checkRoots = self::buildSearchPaths($relative);
            foreach ($checkRoots as $root) {
                foreach (glob($root . '/' .  $file, GLOB_BRACE) as $found) {
                    if (isset($files[basename($found)])) continue;
                    $files[basename($found)] = realpath($found);
                }
            }
        }
        return $files;
    }

    /**
     * Find a ZenCart init file.
     *
     * This just wraps resolveFiles and returns a single result.
     *
     * @see self::resolveFiles
     */
    public static function resolveFile($paths) {
        $file = current(self::resolveFiles($paths));
        return $file;
    }

    /**
     * Prepare db config
     */
    public function onInitConfigDone($event) {
        $yaml = array('services' => array(
            'zenCartThemeStatusMapBuilder' => array('parent' => 'merge:themeStatusMapBuilder', 'class' => 'zenmagick\apps\store\bundles\ZenCartBundle\mock\ZenCartThemeStatusMapBuilder')
        ));
        $yamlLoader = new YamlLoader($this->container, new FileLocator(dirname(__FILE__)));
        $yamlLoader->load($yaml);

        if (!defined('DB_PREFIX')) define('DB_PREFIX', \ZMRuntime::getDatabase()->getPrefix());
        $settingsService = $this->container->get('settingsService');
        if (Runtime::isContextMatch('admin')) {
            $adminDir = $this->container->get('configService')->getConfigValue(self::ZENCART_ADMIN_FOLDER);
            if (null != $adminDir) {
                $settingsService->set('apps.store.zencart.admindir', $adminDir->getValue());
            }

            $urlMappings = __DIR__.'/Resources/config/admin/url_mappings.yaml';
            \ZMUrlManager::instance()->load(file_get_contents($urlMappings), false);

            $routingFile = __DIR__.'/Resources/config/admin/routing.xml';
            if (file_exists($routingFile)) {
                $routeResolver = $this->container->get('routeResolver');
                $routingLoader = new XmlFileLoader(new FileLocator());
                $routeCollection = $routingLoader->load($routingFile);
                $routeResolver->getRouter()->getRouteCollection()->addCollection($routeCollection);
            }
        }

        if (!defined('IS_ADMIN_FLAG')) { define('IS_ADMIN_FLAG', Runtime::isContextMatch('admin')); }

        $zcClassLoader = new ZenCartClassLoader();
        $zcClassLoader->setBaseDirectories($this->buildSearchPaths('includes/classes'));
        $zcClassLoader->register();

        // include some zencart files we need.
        include_once $settingsService->get('apps.store.zencart.path').'/includes/database_tables.php';
    }

    /**
     * Handle things that require a request.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');

        // needed throughout sadly
        $GLOBALS['session_started'] = true;
        $GLOBALS['request_type'] = $request->isSecure() ? 'SSL' : 'NONSSL';
        $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'];

        if (Runtime::isContextMatch('admin')) {

            // @todo shouldn't assume we already have a menu, but we have to since the $adminMenu is never checked for emptiness only null
            $adminMenu = $this->container->get('adminMenu');
            $menuLoader = new MenuLoader();
            $menuLoader->load(__DIR__.'/Resources/config/admin/menu.yaml', $adminMenu);

        } else {
            // init_canonical needs this
            global $current_page;
            $current_page = $request->getRequestId();

            /**
             * only used in the orders class and old email functions
             * @todo move it somewhere else
             */
            $session = $request->getSession();
            if (null == $session->getValue('customers_ip_address')) {
                $session->setValue('customers_ip_address', $_SERVER['REMOTE_ADDR']);
            }
        }
    }

    /**
     * Things to do after the auto loader is finished, but before going back into index.php
     */
    public function onAutoloadDone($event) {
        $request = $event->get('request');

        // skip more zc request handling
        if (!$this->needsZC($request)) {
            global $code_page_directory;
            $code_page_directory = 'zenmagick';
        } else {
            global $code_page_directory, $current_page_base;
            $current_page_base = $request->getRequestId();
            $code_page_directory = 'includes/modules/pages/'.$request->getRequestId();
        }
    }

    /**
     * Simple function to check if we need zen-cart request processing.
     *
     * @param ZMRequest request The current request.
     * @return boolean <code>true</code> if zen-cart should handle the request.
     */
    private function needsZC($request) {
        if ($this->isZencartTheme($request)) {
            return true;
        }

        $requestId = $request->getRequestId();
        if (\ZMLangUtils::inArray($requestId, Runtime::getSettings()->get('apps.store.request.enableZCRequestHandling'))) {
            Runtime::getLogging()->debug('enable zencart request processing for requestId='.$requestId);
            return true;
        }
        if (false === strpos($requestId, 'checkout_') && 'download' != $requestId) {
            // not checkout
            return false;
        }

        // supported by ZenMagick
        $supportedCheckoutPages = array('checkout_shipping_address', 'checkout_payment_address', 'checkout_payment', 'checkout_shipping');

        $needs = !in_array($requestId, $supportedCheckoutPages);
        if ($needs) {
            Runtime::getLogging()->debug('enable zencart request processing for requestId='.$requestId);
        }
        return $needs;
    }

    /**
     * Check for zencart theme.
     */
    protected function isZencartTheme($request) {
        $languageId = $request->getSession()->getLanguageId();
        $themeService = $this->container->get('themeService');
        $themeChain = $themeService->getThemeChain($languageId);
        foreach ($themeChain as $theme) {
            $meta = $theme->getConfig('meta');
            if (array_key_exists('zencart', $meta)) {
                return true;
            }
        }
        return false;
    }

}
