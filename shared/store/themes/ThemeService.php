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
namespace zenmagick\apps\store\themes;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\base\classloader\ClassLoader;
use zenmagick\base\events\Event;
use zenmagick\base\dependencyInjection\loader\YamlFileLoader;

use Symfony\Component\Config\FileLocator;

/**
 * Theme service.
 *
 * <p>Language defaults to 0 (global theme config) in preparation to deprecating language theme config.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ThemeService extends ZMObject {
    const STATUS_MAP_KEY = 'zenmagick.apps.store.themes.status_map';
    protected $themes;
    protected $cache;
    protected $statusMap;
    // theme chain override
    protected $themeChain;
    protected $classLoader;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->themes = array();
        $this->cache = null;
        $this->statusMap = null;
        $this->themeChain = array();
        $this->classLoader = new ClassLoader();
        $this->classLoader->register();
    }


    /**
     * Set the cache.
     *
     * @param zenmagick\base\cache\Cache cache The cache.
     */
    public function setCache($cache) {
        $this->cache = $cache;
    }

    /**
     * Get the cache.
     *
     * @return zenmagick\base\cache\Cache The cache.
     */
    public function getCache() {
        return $this->cache;
    }

    /**
     * Refresh plugin status map.
     */
    public function refreshStatusMap() {
        $this->getStatusMap(true);
    }

    /**
     * Get plugin status map.
     *
     * @param boolean refresh Optional flag to force a refresh; default is <code>false</code>.
     * @return array Plugin status map.
     */
    protected function getStatusMap($refresh=false) {
        if (null === $this->statusMap || $refresh) {
            if (null != $this->cache) {
                $this->statusMap = $this->cache->lookup(self::STATUS_MAP_KEY);
            }

            if (!$this->statusMap || $refresh) {
                $this->container->get('loggingService')->debug('Loading theme status map...');
                $statusMapBuilder = $this->container->get('themeStatusMapBuilder');
                $this->statusMap = $statusMapBuilder->buildStatusMap();
                if ($this->cache) {
                    $this->cache->save($this->statusMap, self::STATUS_MAP_KEY);
                }
            }
        }

        return $this->statusMap;
    }

    /**
     * Get a list of all available themes.
     *
     * @return array A list of <code>Theme</code> instances.
     */
    public function getAvailableThemes() {
        $themes = array();
        $statusMap = $this->getStatusMap();
        foreach ($statusMap['themeList'] as $id => $status) {
            $themes[] = $this->getThemeForId($id);
        }
        return $themes;
    }

    /**
     * Get the active theme.
     *
     * @param int languageId Optional language id; default is <code>0</code>.
     * @return Theme The active theme.
     */
    public function getActiveTheme($languageId=0) {
        $themeChain = $this->getThemeChain($languageId);
        $length = count($themeChain);
        return $themeChain[$length-1];
    }

    /**
     * Override the dynamic theme chain.
     *
     * @param array themeChain The theme chain to use.
     * @param int languageId Optional language id; default is <code>0</code>.
     */
    public function setThemeChain($themeChain, $languageId=0) {
        $this->themeChain[$languageId] = $themeChain;
    }

    /**
     * Get the theme instance for the given id.
     *
     * @param string id The theme id.
     * @return Theme A theme instance.
     */
    public function getThemeForId($id) {
        if (!array_key_exists($id, $this->themes)) {
            $theme = $this->container->get('theme');
            $theme->setId($id);
            $this->themes[$id] = $theme;
            $statusMap = $this->getStatusMap();
            $themeList = $statusMap['themeList'];
            if (array_key_exists($id, $themeList)) {
                $theme->setConfig($themeList[$id]['config']);
                $theme->setBasePath($themeList[$id]['path']);
                $theme->setLocales($themeList[$id]['locales']);
            }
        }
        return $this->themes[$id];
    }

    /**
     * Get theme chain.
     *
     * @param int languageId Optional language id; default is <code>0</code>.
     * @return array List of active themes in increasing order of importance.
     */
    public function getThemeChain($languageId=0) {
        if (array_key_exists($languageId, $this->themeChain)) {
            return $this->themeChain[$languageId];
        }

        $statusMap = $this->getStatusMap();
        $themeChains = $statusMap['themeChains'];
        if (!array_key_exists($languageId, $themeChains)) {
            // default
            $languageId = 0;
        }

        $themeChain = array();
        foreach ($themeChains[$languageId] as $themeId) {
            $themeChain[] = $this->getThemeForId($themeId);
        }
        return $themeChain;
    }

    /**
     * Init themes.
     *
     * <p>This is <strong>the</strong> method in the ZenMagick theme handling. It will:</p>
     * <ol>
     *  <li>Configure the theme loader to add theme specific code (controller) to the classpath</li>
     *  <li>Init l10n/i18n</li>
     *  <li>Load the theme specific <code>extra</code> code</li>
     *  <li>Check for theme switching and repeat the process if needed</li>
     *  <li>Load custome theme settings from <em>theme.yaml</em></li>
     * </ol>
     *
     * @param Language language The language.
     * @return Theme The final active theme.
     */
    public function initThemes($language) {
        $languageId = $language->getId();
        $themeChain = $this->getThemeChain($languageId);

        $statusMap = $this->getStatusMap();
        $themeList = $statusMap['themeList'];

        $eventDispatcher = $this->container->get('eventDispatcher');
        foreach ($themeChain as $theme) {
            $themeInfo = $themeList[$theme->getId()];
            if (array_key_exists('lib', $themeInfo)) {
                $this->classLoader->addNamespace($themeInfo['namespace'], $themeInfo['lib']);
                // allow custom class loading config
                $this->classLoader->addConfig($themeInfo['lib']);
            }
            // init l10n/i18n
            $theme->loadLocale($language);
            // custom theme.yaml settings
            $theme->loadSettings();

            if (array_key_exists('namespace', $themeInfo)) {
                // always add an event listener in the theme's base namespace
                $eventListener = sprintf('%s\EventListener', $themeInfo['namespace'], $theme->getId());
                if (ClassLoader::classExists($eventListener)) {
                    $listener = $this->container->get($eventListener);
                    $eventDispatcher->listen($listener);
                }
            }
            $args = array('language' => $language, 'theme' => $theme, 'themeId' => $theme->getId(), 'languageId' => $languageId);
            $eventDispatcher->dispatch('theme_loaded', new Event($this, $args));
        }

        return $themeChain[count($themeChain)-1];
    }

    /**
     * Get the active theme id (aka the template directory name).
     *
     * @return string The configured theme id.
     */
    public function getActiveThemeId() {
        $theme = $this->getActiveTheme();
        return null != $theme ? $theme->getId() : null;
    }

    /**
     * Get a list of configured themes.
     *
     * @return array A list of themes.
     */
    public function getThemeConfigList() {
        $sql = "SELECT *
                FROM " . TABLE_TEMPLATE_SELECT;
        return \ZMRuntime::getDatabase()->fetchAll($sql, array(), 'template_select', 'zenmagick\apps\store\model\templating\TemplateSelect');
    }

    /**
     * Update theme config.
     *
     * @param mixed config The theme config to update.
     * @return boolean <code>true</code> on success.
     */
    public function updateThemeConfig($config) {
        return \ZMRuntime::getDatabase()->updateModel('template_select', $config);
    }

    /**
     * Create theme config.
     *
     * @param mixed config The theme config to create.
     * @return boolean <code>true</code> on success.
     */
    public function createThemeConfig($config) {
        return \ZMRuntime::getDatabase()->createModel('template_select', $config);
    }

    /**
     * Delete theme config.
     *
     * @param mixed config The theme config to delete.
     * @return boolean <code>true</code> on success.
     */
    public function deleteThemeConfig($config) {
        return \ZMRuntime::getDatabase()->removeModel('template_select', $config);
    }

}
