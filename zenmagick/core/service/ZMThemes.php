<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
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
     * Get <code>ZMThemeInfo</code> instance for the current (or given) theme Id.
     *
     * @param string themeId The theme id or <code>null</code> for the current theme id.
     * @return ZMThemeInfo The themes <code>ZMThemeInfo</code> implementation or <code>null</code>.
     */
    public function getThemeInfoForId($themeId=null) {
        // theme id
        $themeId = null == $themeId ? ZMRuntime::getThemeId() : $themeId;
        // theme base path
        $basePath = ZMRuntime::getThemesDir();
        $infoName = $themeId. ' ThemeInfo';
        // theme info class name
        $infoClass = ZMLoader::makeClassname($infoName);
        // theme info file name
        $infoFile = $basePath.$themeId."/".$infoClass.".php";

        // load
        if (!class_exists($infoClass)) {
            if (!file_exists($infoFile)) {
                $this->log('skipping "' . $themeId . '" - no theme info class found', ZM_LOG_WARN);
                return null;
            }
            require_once($infoFile);
        }
        // create instance
        $obj = new $infoClass();
        $obj->setThemeId($themeId);
        if ($themeId != ZM_DEFAULT_THEME && ZMSettings::get('isEnableThemeDefaults')) {
            $obj->setParent($this->getThemeInfoForId(ZM_DEFAULT_THEME));
        }

        return $obj;
    }

    /**
     * Get a list of all available themes.
     *
     * @return array A list of <code>ZMThemeInfo</code> instances.
     */ 
    public function getThemeInfoList() {
        $infoList = array();
        $basePath = ZMRuntime::getThemesDir();
        $dirs = $this->getThemeDirList();
        // load info classes and get instance
        foreach ($dirs as $dir) {
            $themeInfo = $this->getThemeInfoForId($dir);
            if (null != $themeInfo) {
                $infoList[] = $themeInfo;
            }
        }

        return $infoList;
    }

    /**
     * Generate a list of all theme directories.
     *
     * @return array List of all directories under <em>themes</em> that contain a theme.
     */
    private function getThemeDirList() {
        $themes = array();
        $handle = @opendir(ZMRuntime::getThemesDir());
        while (false !== ($file = readdir($handle))) { 
            if (ZMTools::startsWith($file, '.') || 'CVS' == $file) {
                continue;
            }
            array_push($themes, $file);
        }
        @closedir($handle);
        return $themes;
    }

    /**
     * Get the configured zen-cart theme id (aka the template directory name).
     *
     * @param int languageId Optional language id.
     * @return string The configured zen-cart theme id.
     */
    public function getZCThemeId($languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "select template_dir
                from " . TABLE_TEMPLATE_SELECT . "
                where template_language = :languageId";
        $sql = $db->bindVars($sql, ":languageId", $languageId, 'integer');
        $results = $db->Execute($sql);
        if (0 < $results->RecordCount()) {
            $themeId = $results->fields['template_dir'];
        } else {
            $sql = "select template_dir
                    from " . TABLE_TEMPLATE_SELECT . "
                    where template_language = 0";
            $results = $db->Execute($sql);
            $themeId = $results->fields['template_dir'];
        }

        $themeId = empty($themeId) ? ZM_DEFAULT_THEME : $themeId;
        return $themeId;
    }

    /**
     * Update the configured zen-cart theme id.
     *
     * @param string themeId The theme id.
     * @param int languageId Optional language id; default is <em>0</em> for all.
     */
    public function updateZCThemeId($themeId, $languageId=0) {
        $db = ZMRuntime::getDB();

        // update or insert?
        $sql = "select *
                from " . TABLE_TEMPLATE_SELECT . "
                where template_language = :languageId";
        $sql = $db->bindVars($sql, ":languageId", $languageId, 'integer');
        $results = $db->Execute($sql);

        $sql = '';
        if (0 < $results->RecordCount()) {
            // update
            $sql = "update " . TABLE_TEMPLATE_SELECT . " set template_dir = :themeId
                    where template_id = :templateId
                    and template_language = :languageId";
            $sql = $db->bindVars($sql, ":themeId", $themeId, 'string');
            $sql = $db->bindVars($sql, ":templateId", $results->fields['template_id'], 'integer');
            $sql = $db->bindVars($sql, ":languageId", $languageId, 'integer');
        } else {
            // insert
            $sql = "insert into " . TABLE_TEMPLATE_SELECT . " (template_dir, template_language)
                    values (:themeId, :languageId)";
            $sql = $db->bindVars($sql, ":themeId", $themeId, 'string');
            $sql = $db->bindVars($sql, ":languageId", $languageId, 'integer');
        }

        $results = $db->Execute($sql);
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
     * @param string themeId The themeId to start with; default is <code>ZM_DEFAULT_THEME</code>.
     * @return ZMTheme The final theme.
     */
    public function resolveTheme($themeId=ZM_DEFAULT_THEME) {
        // set up theme
        $theme = ZMThemes::instance()->getThemeForId($themeId);
        $themeInfo = $theme->getThemeInfo();

        // configure theme loader
        $themeLoader = ZMLoader::make("Loader");
        $themeLoader->addPath($theme->getExtraDir());

        // add loader to root loader
        ZMLoader::instance()->setParent($themeLoader);

        if (ZMSettings::get('isLegacyAPI')) { eval(zm_globals()); }

        // init l10n/i18n
        $session = ZMRequest::getSession();
        $language = $session->getLanguage();
        $theme->loadLocale($language);

        // use theme loader to load static stuff
        foreach ($themeLoader->getStatic() as $static) {
            require_once($static);
        }

        // check for theme switching
        if (ZMRuntime::getThemeId() != $themeInfo->getThemeId()) {
            return $this->resolveTheme(ZMRuntime::getThemeId());
        }

        // finalise i18n
        zm_i18n_finalise();

        ZMEvents::instance()->fireEvent(null, ZM_EVENT_THEME_RESOLVED, array('theme' => $theme));

        return $theme;
    }

}

?>
