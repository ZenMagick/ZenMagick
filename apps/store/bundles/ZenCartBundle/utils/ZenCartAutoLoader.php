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

namespace ZenMagick\apps\store\bundles\ZenCartBundle\utils;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

/**
 * ZenCart auto loader utility
 *
 * @author Johnny Robeson
 * @todo fix image handler extra_configures
 */
class ZenCartAutoLoader extends ZMObject {
    private $globalKeys = array();
    private $originalErrorLevel;

    public function __construct() {
        $this->originalErrorLevel = error_reporting();
    }

    public function getRequest() {
        return $this->container->get('request');
    }

    public function getThemeService() {
        return $this->container->get('themeService');
    }

    public function setRootDir($rootDir) {
        $this->rootDir = realpath($rootDir);
    }

    public function getRootDir() {
        return $this->rootDir;
    }

    /**
     * Commonly used ZenCart request related globals.
     *
     * May need to be overwritten from time to time.
     */
    public function overrideRequestGlobals() {
        $request = $this->getRequest();

        $requestId = $request->getRequestId();
        // needed throughout sadly
        $globals = array(
            'code_page_directory' => 'includes/modules/pages/'.$requestId,
            'current_page' => $requestId,
            'current_page_base' => $requestId,
            'cPath' => (string)$request->query->get('cPath'),
            'current_category_id' => $request->attributes->get('categoryId'),
            'cPath_array' => $request->attributes->get('categoryIds'),
            'page_directory' => 'includes/modules/pages/'.$requestId,
            'request_type' => $request->isSecure() ? 'SSL' : 'NONSSL',
            'session_started' => true,
            'PHP_SELF' => $request->server->get('PHP_SELF'),
        );
        $this->setGlobalValues($globals);
        if (Runtime::isContextMatch('admin')) {
            $this->setGlobalValue('PHP_SELF', $requestId.'.php');
        } else {
            $_GET['main_page'] = $requestId; // needed (somewhere) to catch routes from the route resolver
        }
    }

    /**
     * Get a list of init files to load.
     *
     */
    public function initFiles() {
        $isAdmin = Runtime::isContextMatch('admin');
        $filePatterns = array();
        if ($isAdmin) {
            $filePatterns[] = '../includes/version.php';
            $filePatterns[] = '../includes/configure.php';
            $filePatterns[] = 'includes/extra_configures/*.php';
            $filePatterns[] = '../includes/database_tables.php';
            $filePatterns[] = '../includes/filenames.php';
            $filePatterns[] = 'includes/extra_datafiles/*.php';
            $filePatterns[] = 'includes/functions/extra_functions/*.php';
            $filePatterns[] = 'includes/functions/{general.php,database.php,functions_customers.php}';
            $filePatterns[] = 'includes/functions/{functions_metatags.php,functions_prices.php,html_output.php}';
            $filePatterns[] = 'includes/functions/{localization.php,password_funcs.php}';
            $filePatterns[] = '../includes/functions/{audience.php,banner.php,featured.php}';
            $filePatterns[] = '../includes/functions/{functions_email.php,salemaker.php,sessions.php,specials.php,zen_mail.php}';
        } else {
            $filePatterns[] = 'includes/version.php';
            $filePatterns[] = 'includes/configure.php';
            $filePatterns[] = 'includes/extra_configures/*.php';
            $filePatterns[] = 'includes/database_tables.php';
            $filePatterns[] = 'includes/filenames.php';
            $filePatterns[] = 'includes/extra_datafiles/*.php';
            $filePatterns[] = 'includes/functions/extra_functions/*.php';
            $filePatterns[] = 'includes/functions/{functions_email.php,functions_general.php,html_output.php}';
            $filePatterns[] = 'includes/functions/{functions_ezpages.php,password_funcs.php,sessions.php,zen_mail.php}';
            $filePatterns[] = 'includes/functions/banner.php';
        }
        return $filePatterns;
    }

    /**
     * Init common stuff across storefront and admin
     *
     * Assume access to $request
     */
    public function initCommon() {
        $this->overrideRequestGlobals();
        $zcClassLoader = new \ZenMagick\apps\store\bundles\ZenCartBundle\ZenCartClassLoader();
        $zcClassLoader->setBaseDirectories($this->buildSearchPaths('includes/classes'));
        $zcClassLoader->register();

        // @todo really make a setting out of these formats?
        $uiFormat = $this->container->get('localeService')->getFormat('date', 'short-ui-format');

        $request = $this->getRequest();
        $data = array(
            'requestContext' => $request->getContext(),
            'httpServer' => $request->getHttpHost(),
            'settings' => $this->container->get('settingsService'),
            'shortUIFormat' => $uiFormat
        );
        foreach ($this->initFiles() as $filePattern) {
            $this->includeFiles($filePattern, $data);
        }

        // Common classes

        $this->setGlobalValue('zco_notifier', new \notifier);
        $this->setGlobalValue('db', new \queryFactory);
        $this->setGlobalValue('messageStack', new \messageStack);
        $this->setGlobalValue('template', new \template_func);
        $this->setGlobalValue('sniffer', new \sniffer);

        $this->container->get('productTypeLayoutService')->defineAll();

    }

    /**
     * Get names of all global variables needed by ZenCart.
     *
     * @return array
     */
    public function getGlobalKeys() {
        return $this->globalKeys;
    }

    /**
     * Get all global variables needed by ZenCart.
     */
    public function getGlobalValues() {
        $globalValues = array();
        foreach ($this->globalKeys as $name) {
            $globalValues[$name] = $GLOBALS[$name];
        }
        return $globalValues;
    }

    /**
     * Set global values used for ZenCart
     *
     * This also saves the names of the values so we know
     * which ones have been set.
     *
     * @param array globalValues
     */
    public function setGlobalValues($globalValues) {
        foreach ($globalValues as $name => $value) {
            $GLOBALS[$name] = $value;
            $this->globalKeys[] = $name;
        }

    }

    /**
     * Set a single global value for ZenCart.
     */
    public function setGlobalValue($name, $value) {
        $GLOBALS[$name] = $value;
        $this->globalKeys[] = $name;
    }

    /**
     * Build list of paths to search for ZenCart init files.
     *
     * Looks in the bundle bridge directory before ZenCart.
     *
     * @param string base Relative path from ZenCart root directory
     */
    public function buildSearchPaths($base = '') {
        $zcPath = $this->getRootDir();
        $dirs = array(dirname(__DIR__).'/bridge', $zcPath);
        if (Runtime::isContextMatch('admin')) {
            $adminDir = $this->container->get('settingsService')->get('zencart.admin_dir');
            $adminDirs = array(dirname(__DIR__).'/bridge/admin', $zcPath.'/'.$adminDir);
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
     */
    public function resolveFileVars($string) {
        if (false === strpos($string, '%')) return $string;
        $map = array();
        $container = Runtime::getContainer();
        $request = $container->get('request');
        $map['%current_page%'] = $request->getRequestId();
        $map['%current_page_base%'] = $request->getRequestId();
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
    public function resolveFiles($paths) {
        $files = array();
        foreach ((array)$paths as $path) {
            $path = $this->resolveFileVars($path);
            $file = basename($path);
            $relative = dirname($path);
            $checkRoots = $this->buildSearchPaths($relative);
            foreach ($checkRoots as $root) {
                foreach (glob($root.'/'.$file, GLOB_BRACE) as $found) {
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
    public function resolveFile($paths) {
        $file = current($this->resolveFiles($paths));
        return $file;
    }

    /**
     * Set up (strict) error reporting level for ZenCart.
     *
     * This is equivalent to STRICT_ERROR_REPORTING
     */
    public function setErrorLevel() {
        $settingsService = $this->container->get('settingsService');

        $exists = $settingsService->exists('apps.store.zencart.strictErrorReporting');
        if (!$exists || !$settingsService->get('apps.store.zencart.strictErrorReporting')) {
            error_reporting(version_compare(PHP_VERSION, 5.4, '>=') ? E_ALL ^ E_DEPRECATED ^ E_NOTICE ^ E_STRICT : E_ALL ^ E_DEPRECATED ^ E_NOTICE);
        }
    }

    /**
     * Restore the original error level.
     */
    public function restoreErrorLevel() {
        if (null != $this->originalErrorLevel) {
            error_reporting($this->originalErrorLevel);
        }
    }

    /**
     * Include a file or files.
     *
     * This method also gives all files access to the
     * required global variables.
     */
    public function includeFiles($path, $data = array(), $once = true) {
        $files = $this->resolveFiles($path);
        // Get some local helpers
        extract($this->getGlobalValues());
        extract($data);

        foreach ($files as $file) {
            if ($once) {
                include_once $file;
            } else {
                include $file;
            }
        }
        // image handler sets these 2 in extra_datafiles !
        // @todo any others we might need ?
        if (isset($ihConf)) $this->setGlobalValue('ihConf', $ihConf);
        if (isset($bmzConf)) $this->setGlobalValue('bmzConf', $bmzConf);

    }
}
