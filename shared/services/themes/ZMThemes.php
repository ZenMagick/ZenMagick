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

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
        return ZMObject::singleton('Themes');
    }


    /**
     * Get a list of all available themes.
     *
     * @return array A list of <code>ZMTheme</code> instances.
     */ 
    public function getAvailableThemes() {
        $themes = array();
        $basePath = Runtime::getThemesDir();
        $dirs = $this->getThemeDirList();
        // load info classes and get instance
        foreach ($dirs as $dir) {
            if (file_exists($basePath.$dir.DIRECTORY_SEPARATOR.'theme.yaml')) {
                $themes[] =  ZMLoader::make("Theme", $dir);
            }
        }

        return $themes;
    }

    /**
     * Get <code>ZMTheme</code> instance for the given theme Id.
     *
     * @param string themeId The theme id.
     * @return ZMTheme <code>ZMTheme</code> instance or <code>null</code>.
     */
    public function getThemeForId($themeId=null) {
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
            if (ZMLangUtils::startsWith($file, '.') || 'CVS' == $file) {
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
     * @param int languageId Language id.
     * @return string The configured theme id.
     */
    public function getActiveThemeId($languageId) {
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
     * Set the active theme id.
     *
     * @param string themeId The theme id.
     * @param string variationId Optional variation (theme) id.
     * @param int languageId Optional language id; default is <em>0</em> for all.
     */
    public function setActiveThemeId($themeId, $variationId=null, $languageId=0) {
        // update or insert?
        $sql = "SELECT template_id
                FROM " . TABLE_TEMPLATE_SELECT . "
                WHERE template_language = :languageId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('languageId' => $languageId), TABLE_TEMPLATE_SELECT);

        $sql = '';
        if (null !== $result) {
            $sql = "UPDATE " . TABLE_TEMPLATE_SELECT . " 
                    SET template_dir = :themeId, variation_dir = :variationId
                    WHERE template_id = :id
                      AND template_language = :languageId";
        } else {
            $sql = "INSERT INTO " . TABLE_TEMPLATE_SELECT . " 
                    (template_dir, variation_dir, template_language)
                    values (:themeId, :variationId, :languageId)";
        }
        $args = array('id' => $result['id'], 'themeId' => $themeId, 'variationId' => $variationId, 'languageId' => $languageId);
        ZMRuntime::getDatabase()->update($sql, $args, TABLE_TEMPLATE_SELECT);
    }

    /**
     * Resolve theme incl. loader update, theme switching and all theme default
     * handling.
     *
     * <p>This is <strong>the</strong> method in the ZenMagick theme handling. It will:</p>
     * <ol>
     *  <li>Configure the theme loader to add theme specific code (controller) to the classpath</li>
     *  <li>Init l10n/i18n</li>
     *  <li>Load the theme specific <code>extra</code> code</li>
     *  <li>Check for theme switching and repeat the process if needed</li>
     * </ol>
     *
     * <p>Passing default theme id rather than the current theme id is equivalent to
     * enabling default theme fallback. Coincidentally, this is also the default behaviour.</p>
     *
     * @param string themeId The themeId to start with.
     * @param ZMLanguage language The language.
     * @return ZMTheme The final theme.
     */
    public function resolveTheme($themeId, $language) {
        if (null == $themeId) {
            $themeId = ZMSettings::get('apps.store.themes.default');
        }
        // set up theme
        $theme = ZMThemes::instance()->getThemeForId($themeId);

        // configure theme loader
        $themeLoader = ZMLoader::make("Loader");
        $themeLoader->addPath($theme->getExtraDir());

        // add loader to root loader
        ZMLoader::instance()->setParent($themeLoader);

        // init l10n/i18n
        $theme->loadLocale($language);

        // use theme loader to load static stuff
        foreach ($themeLoader->getStatic() as $static) {
            require_once($static);
        }

        // check for theme switching
        if (Runtime::getThemeId() != $theme->getThemeId()) {
            $nextTheme = $this->resolveTheme(Runtime::getThemeId(), $language);
            // merge with parent..
            $nextTheme->setConfig(ZMLangUtils::arrayMergeRecursive($theme->getConfig(), $nextTheme->getConfig()));
            return $nextTheme;
        }

        return $theme;
    }

}
