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

namespace zenmagick\apps\store\bundles\ZenCartBundle\utils;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * ZenCart auto loader utility
 *
 * @author Johnny Robeson
 * @todo fix image handler extra_configures
 */
class ZenCartAutoLoader extends ZMObject {
    private $globalKeys = array();
    private $originalErrorLevel;

    /**
     * Init common stuff across storefront and admin
     *
     * Assume access to $request
     */
    public function initCommon() {
        $request = $this->container->get('request');

        $isAdmin = Runtime::isContextMatch('admin');
        if ($isAdmin) {
            $this->includeFiles('../includes/configure.php');
            $this->includeFiles('../includes/database_tables.php');
            $this->includeFiles('../includes/filenames.php');
        } else {
            $this->includeFiles('includes/configure.php');
            $this->includeFiles('includes/database_tables.php');
            $this->includeFiles('includes/filenames.php');
        }

        $settingsService = Runtime::getSettings();
        require $settingsService->get('apps.store.zencart.path').'/includes/version.php';

        $requestId = $request->getRequestId();

        // needed throughout sadly
        $globals = array(
            'current_page' => $requestId,
            'current_page_base' => $requestId,
            'cPath' => (string)$request->getCategoryPath(),
            'current_category_id' => $request->getCategoryId(),
            'cPath_array' => $request->getCategoryPathArray(),
            'request_type' => $request->isSecure() ? 'SSL' : 'NONSSL',
            'session_started' => true,
            'PHP_SELF' => $request->server->get('PHP_SELF'),
        );
        $this->setGlobalValues($globals);

        // Common classes
        $zcClassLoader = new \zenmagick\apps\store\bundles\ZenCartBundle\ZenCartClassLoader();
        $zcClassLoader->setBaseDirectories($this->buildSearchPaths('includes/classes'));
        $zcClassLoader->register();

        $this->setGlobalValue('zco_notifier', new \notifier);
        $this->setGlobalValue('db', new \queryFactory);
        $this->setGlobalValue('messageStack', new \messageStack);
        $this->setGlobalValue('template', new \template_func);
        $this->setGlobalValue('sniffer', new \sniffer);
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
        $settingsService = Runtime::getSettings();
        $zcPath = $settingsService->get('apps.store.zencart.path');
        $dirs = array(dirname(__DIR__).'/bridge', $zcPath);
        if (Runtime::isContextMatch('admin')) {
            $adminDir = $settingsService->get('apps.store.zencart.admindir');
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
        if (null != $this->originalErrorLevel) {
            $this->originalErrorLevel = error_reporting();
        }
        if (!Runtime::getSettings()->get('apps.store.zencart.strictErrorReporting', false)) {
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
    public function includeFiles($path, $require = false, $once = true) {
        $files = $this->resolveFiles($path);
        // Get some local helpers
        extract($this->getGlobalValues());

        foreach ($files as $file) {
            if ($require) {
                if ($once) {
                    require_once $file;
                } else {
                    require $file;
                }
            } else {
                if ($once) {
                    include_once $file;
                } else {
                    include $file;
                }
            }
        }
    }
}
