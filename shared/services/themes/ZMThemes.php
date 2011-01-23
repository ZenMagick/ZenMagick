<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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

/**
 * Themes.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.themes
 */
class ZMThemes extends ZMObject {
    private $themeChain_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->themeChain_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMRuntime::singleton('Themes');
    }


    /**
     * Get a list of all available themes.
     *
     * @return array A list of <code>ZMTheme</code> instances.
     */
    public function getAvailableThemes() {
        $themes = array();
        $basePath = Runtime::getThemesDir();
        $themeDirs = $this->getThemeDirList();
        // load info classes and get instance
        foreach ($themeDirs as $dir) {
            if (file_exists($basePath.$dir.DIRECTORY_SEPARATOR.'theme.yaml')) {
                $themes[] =  ZMLoader::make("Theme", $dir);
            }
        }

        //XXX: try for zc themes
        foreach ($this->getZCThemeDirList() as $dir) {
            if (!in_array($dir, $themeDirs)) {
                $themes[] =  ZMLoader::make("Theme", $dir);
            }
        }

        return $themes;
    }

    /**
     * Override the dynamic theme chain.
     *
     * @param int languageId Language id.
     * @param array themeChain The theme chain to use.
     */
    public function setThemeChain($languageId, $themeChain) {
        if (null === $this->themeChain_) {
            $this->themeChain_ = array();
        }
        $this->themeChain_[$languageId] = $themeChain;
    }

    /**
     * Get theme chain.
     *
     * @param int languageId Language id.
     * @return array List of active themes in increasing order of importance.
     */
    public function getThemeChain($languageId) {
        if (null === $this->themeChain_ || !array_key_exists($languageId, $this->themeChain_)) {
            if (null === $this->themeChain_) {
                $this->themeChain_ = array();
            }
            $this->themeChain_[$languageId] = array();
            $sql = "SELECT *
                    FROM " . TABLE_TEMPLATE_SELECT . "
                    WHERE template_language = :languageId";
            $result = ZMRuntime::getDatabase()->querySingle($sql, array('languageId' => $languageId), TABLE_TEMPLATE_SELECT);
            if (null === $result) {
                $sql = "SELECT *
                        FROM " . TABLE_TEMPLATE_SELECT . "
                        WHERE template_language = 0";
                $result = ZMRuntime::getDatabase()->querySingle($sql, array('languageId' => $languageId), TABLE_TEMPLATE_SELECT);
            }

            // fill the chain
            $this->themeChain_[$languageId][] = $this->getThemeForId(ZMSettings::get('apps.store.themes.default'));
            if (!empty($result['themeId']) && null != ($theme  = $this->getThemeForId($result['themeId']))) {
                $this->themeChain_[$languageId][] = $theme;
            }
            if (!empty($result['variationId']) && null != ($variation  = $this->getThemeForId($result['variationId']))) {
                $this->themeChain_[$languageId][] = $variation;
            }
        }

        return $this->themeChain_[$languageId];
    }

    /**
     * Get <code>ZMTheme</code> instance for the given theme Id.
     *
     * @param string themeId The theme id.
     * @return ZMTheme <code>ZMTheme</code> instance or <code>null</code>.
     */
    public function getThemeForId($themeId) {
        $theme = ZMLoader::make("Theme", $themeId);
        return $theme;
    }

    /**
     * Generate a list of all theme directories.
     *
     * @return array List of all directories under <em>themes</em> that contain a theme.
     */
    private function getThemeDirList() {
        $themes = array();
        $handle = @opendir(Runtime::getThemesDir());
        while (false !== ($file = readdir($handle))) {
            if (ZMLangUtils::startsWith($file, '.')) {
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
        $handle = @opendir(ZMFileUtils::mkPath(DIR_FS_CATALOG, 'includes', 'templates'));
        while (false !== ($file = readdir($handle))) {
            if (ZMLangUtils::startsWith($file, '.')) {
                continue;
            }
            array_push($themes, $file);
        }
        @closedir($handle);
        return $themes;
    }

    /**
     * Get the active theme id (aka the template directory name).
     *
     * @param int languageId Language id; default is <em>0</em> to load the language default theme.
     * @return string The configured theme id.
     */
    public function getActiveThemeId($languageId=0) {
        $sql = "SELECT *
                FROM " . TABLE_TEMPLATE_SELECT . "
                WHERE template_language = :languageId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('languageId' => $languageId), TABLE_TEMPLATE_SELECT);
        if (null !== $result) {
            $themeId = $result['themeId'];
        } else {
            $sql = "SELECT *
                    FROM " . TABLE_TEMPLATE_SELECT . "
                    WHERE template_language = 0";
            $result = ZMRuntime::getDatabase()->querySingle($sql, array('languageId' => $languageId), TABLE_TEMPLATE_SELECT);
            $themeId = $result['themeId'];
        }

        $themeId = empty($themeId) ? ZMSettings::get('apps.store.themes.default') : $themeId;
        return $themeId;
    }

    /**
     * Get a list of configured themes.
     *
     * @return string The configured theme id.
     */
    public function getThemeConfigList() {
        $sql = "SELECT *
                FROM " . TABLE_TEMPLATE_SELECT;
        return ZMRuntime::getDatabase()->query($sql, array(), TABLE_TEMPLATE_SELECT, 'ZMObject');
    }

    /**
     * Update theme config.
     *
     * @param mixed config The theme config to update.
     * @return boolean <code>true</code> on success.
     */
    public function updateThemeConfig($config) {
        return ZMRuntime::getDatabase()->updateModel(TABLE_TEMPLATE_SELECT, $config);
    }

    /**
     * Create theme config.
     *
     * @param mixed config The theme config to create.
     * @return boolean <code>true</code> on success.
     */
    public function createThemeConfig($config) {
        return ZMRuntime::getDatabase()->createModel(TABLE_TEMPLATE_SELECT, $config);
    }

    /**
     * Delete theme config.
     *
     * @param mixed config The theme config to delete.
     * @return boolean <code>true</code> on success.
     */
    public function deleteThemeConfig($config) {
        return ZMRuntime::getDatabase()->removeModel(TABLE_TEMPLATE_SELECT, $config);
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
     * @param ZMLanguage language The language.
     * @return ZMTheme The final theme.
     */
    public function initThemes($language) {
        if (null == $language) {
            // default language
            $language = ZMLanguages::instance()->getLanguageForCode(ZMSettings::get('defaultLanguageCode'));
        }

        foreach ($this->getThemeChain($language->getId()) as $theme) {
            // configure theme loader
            $themeLoader = new ZMLoader($theme->getName());
            $themeLoader->addPath($theme->getExtraDir());

            // add loader to root loader
            ZMLoader::instance()->setParent($themeLoader);

            // init l10n/i18n
            $theme->loadLocale($language);
            // custom theme.yaml settings
            $theme->loadSettings();

            // use theme loader to load static stuff
            foreach ($themeLoader->getStatic() as $static) {
                require_once($static);
            }
        }

        return $theme;
    }

}
