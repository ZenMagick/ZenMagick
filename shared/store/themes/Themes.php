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

use zenmagick\base\classloader\ClassLoader;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\base\events\Event;
use zenmagick\base\dependencyInjection\loader\YamlFileLoader;

use Symfony\Component\Config\FileLocator;

/**
 * Themes.
 *
 * @author DerManoMann
 */
class Themes extends ZMObject {
    protected $themeChain_;
    protected $initLanguage_;
    protected $cache_;
    protected $basePath;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->themeChain_ = array();
        $this->initLanguage_ = null;
        $this->basePath = '/themes';
    }


    /**
     * Set the themes base path.
     *
     * <p>This is taken as relative to the ZenMagick installation path.</p>
     *
     * @param string path The base path.
     */
    public function setBasePath($path) {
        $this->basePath = $path;
    }

    /**
     * Get the base path.
     *
     * <p>The themes base path, relative to the ZenMagick installation directory.</p>
     *
     * @return string The path.
     */
    public function getBasePath() {
        return $this->basePath;
    }

    /**
     * Set the cache.
     *
     * @param zenmagick\base\cache\Cache cache The cache.
     */
    public function setCache($cache) {
        $this->cache_ = $cache;
    }

    /**
     * Get the cache.
     *
     * @return zenmagick\base\cache\Cache The cache.
     */
    public function getCache() {
        return $this->cache_;
    }

    /**
     * Return the directory containing all themes.
     *
     * @return string The base directory for themes.
     */
    public function getThemesDir() {
        return Runtime::getInstallationPath().$this->basePath;
    }

    /**
     * Get a list of all available themes.
     *
     * @return array A list of <code>Theme</code> instances.
     */
    public function getAvailableThemes() {
        $themes = array();
        $basePath = $this->getThemesDir();
        $themeDirs = $this->getThemeDirList();
        // load info classes and get instance
        foreach ($themeDirs as $dir) {
            if (file_exists($basePath.'/'.$dir.'/theme.yaml')) {
                $theme = $this->container->get('theme');
                $theme->setThemeId($dir);
                $themes[] = $theme;
            }
        }

        //XXX: try for zc themes
        foreach ($this->getZCThemeDirList() as $dir) {
            if (!in_array($dir, $themeDirs)) {
                $theme = $this->container->get('theme');
                $theme->setThemeId($dir);
                $themes[] = $theme;
            }
        }

        return $themes;
    }

    /**
     * Get the active theme.
     *
     * @return Theme The active theme.
     */
    public function getActiveTheme() {
        $languageId = $this->initLanguage_->getId();
        $length = count($this->themeChain_[$languageId]);
        return $this->themeChain_[$languageId][$length-1];
    }

    /**
     * Override the dynamic theme chain.
     *
     * @param int languageId Language id.
     * @param array themeChain The theme chain to use.
     */
    public function setThemeChain($languageId, $themeChain) {
        $this->themeChain_[$languageId] = $themeChain;
    }

    /**
     * Get theme chain.
     *
     * @param int languageId Language id.
     * @return array List of active themes in increasing order of importance.
     */
    public function getThemeChain($languageId) {
        if (!array_key_exists($languageId, $this->themeChain_)) {

            // fill the chain
            $this->themeChain_[$languageId] = array();
            $this->themeChain_[$languageId][] = $this->getThemeForId($this->container->get('settingsService')->get('apps.store.themes.default'), $languageId);

            // Get themeSelects and filter by languageId
            $themeSelelects = array();
            foreach ($this->getThemeConfigList() as $themeSelect) {
                $themeSelectLanguageId = $themeSelect->getLanguageId();
                if (0 == $themeSelectLanguageId || $themeSelectLanguageId == $languageId) {
                    $themeSelelects[] = $themeSelect;
                }
            }

            if (empty($themeSelelects)) return $this->themeChain_[$languageId];

            $themeSelect = array_pop($themeSelelects);
            $themeId = $themeSelect->getThemeId();
            $variationId = $themeSelect->getVariationId();

            if (null != ($theme = $this->getThemeForId($themeId, $languageId))) {
                $this->themeChain_[$languageId][] = $theme;
            }
            if (!empty($variationId) && null != ($variation  = $this->getThemeForId($variationId, $languageId))) {
                $this->themeChain_[$languageId][] = $variation;
            }
        }

        return $this->themeChain_[$languageId];
    }

    /**
     * Get <code>Theme</code> instance for the given theme Id.
     *
     * @param string themeId The theme id.
     * @param init languageId Optional language id to init/load the theme; default is <code>null</code>.
     * @return Theme <code>Theme</code> instance or <code>null</code>.
     */
    public function getThemeForId($themeId, $languageId=null) {
        if (empty($themeId)) {
            return null;
        }
        $cacheKey = \ZMLangUtils::mkUnique('themes', $themeId, $languageId);
        if (false !== ($theme = $this->cache_->lookup($cacheKey))) {
            return $theme;
        }

        $theme = $this->container->get('theme');
        $theme->setThemeId($themeId);

        if (null !== $languageId) {
            $language = $this->container->get('languageService')->getLanguageForId($languageId);

            $libPath = $theme->getBaseDir().'/lib';
            $classLoader = $this->container->get('classLoader');
            $classLoader->addNamespace(sprintf('zenmagick\themes\%s', $themeId), $libPath);
            // allow custom class loading config
            $classLoader->addConfig($libPath);
            $classLoader->register();

            // init l10n/i18n
            $theme->loadLocale($language);
            // custom theme.yaml settings
            $theme->loadSettings();

            // always add an event listener in the theme's base namespace
            $eventListener = sprintf('zenmagick\apps\store\themes\%s\EventListener', $themeId);
            if (ClassLoader::classExists($eventListener)) {
                $listener = new $eventListener();
                $listener->setContainer($this->container);
                Runtime::getEventDispatcher()->listen($listener);
            }

            $args = array('language' => $language, 'theme' => $theme, 'themeId' => $themeId, 'languageId' => $languageId);
            Runtime::getEventDispatcher()->dispatch('theme_loaded', new Event($this, $args));
        }

        // cache to avoid to init a theme more than once
        $this->cache_->save($theme, $cacheKey);

        return $theme;
    }

    /**
     * Generate a list of all theme directories.
     *
     * @return array List of all directories under <em>themes</em> that contain a theme.
     */
    private function getThemeDirList() {
        $themes = array();
        $handle = @opendir($this->getThemesDir());
        while (false !== ($file = readdir($handle))) {
            if (\ZMLangUtils::startsWith($file, '.')) {
                continue;
            }
            array_push($themes, $file);
        }
        @closedir($handle);
        return $themes;
    }

    /**
     * Generate a list of all zencart directories.
     *
     * @return array List of all directories.
     */
    private function getZCThemeDirList() {
        $themes = array();
        $zcPath = $this->container->get('settingsService')->get('apps.store.zencart.path');
        if (false !== ($handle = @opendir($zcPath.'/includes/templates'))) {
            while (false !== ($file = readdir($handle))) {
                if (\ZMLangUtils::startsWith($file, '.')) {
                    continue;
                }
                array_push($themes, $file);
            }
            @closedir($handle);
        }
        return $themes;
    }

    /**
     * Get the active theme id (aka the template directory name).
     *
     * @return string The configured theme id.
     */
    public function getActiveThemeId() {
        $theme = $this->getActiveTheme();
        return null != $theme ? $theme->getThemeId() : null;
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
        return \ZMRuntime::getDatabase()->updateModel(TABLE_TEMPLATE_SELECT, $config);
    }

    /**
     * Create theme config.
     *
     * @param mixed config The theme config to create.
     * @return boolean <code>true</code> on success.
     */
    public function createThemeConfig($config) {
        return \ZMRuntime::getDatabase()->createModel(TABLE_TEMPLATE_SELECT, $config);
    }

    /**
     * Delete theme config.
     *
     * @param mixed config The theme config to delete.
     * @return boolean <code>true</code> on success.
     */
    public function deleteThemeConfig($config) {
        return \ZMRuntime::getDatabase()->removeModel(TABLE_TEMPLATE_SELECT, $config);
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
     * @return Theme The final theme.
     */
    public function initThemes($language = null) {
        if (null == $language) {
            // default language
            $language = $this->container->get('languageService')->getLanguageForCode($this->container->get('settingsService')->get('defaultLanguageCode'));
        }
        $this->initLanguage_ = $language;

        // load if not set
        $themeChain = $this->getThemeChain($language->getId());
        return $themeChain[count($themeChain)-1];
    }

}
