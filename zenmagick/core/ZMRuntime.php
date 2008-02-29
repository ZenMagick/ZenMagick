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
 * A central place for all runtime stuff.
 *
 * <p>This is not really neccessary, but I prefer to have a single place
 * where I can look up stuff rather than search tons fo files.</p>
 *
 * @author mano
 * @package org.zenmagick
 * @version $Id$
 */
class ZMRuntime extends ZMObject {
    private $themeId_;
    private $theme_;
    private $themes_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        // init with defaults
        $this->themeId_ = null;
        $this->theme_ = null;
    }


    /**
     * Get instance.
     */
    public static function instance() {
        return parent::instance('Runtime');
    }

    /**
     * Get the application scope.
     *
     * @return string Either <code>ZM_SCOPE_STORE</code> or <code>ZM_SCOPE_ADMIN</code>.
     */
    public static function getScope() {
        return zm_setting('isAdmin') ? ZM_SCOPE_ADMIN : ZM_SCOPE_STORE;
    }

    /**
     * Get the database dao.
     *
     * @return queryFactory *The* zen-cart <code>queryFactory</code> instance.
     */
    public static function getDB() { global $db; return $db; }

    /**
     * Return the directory containing all themes.
     *
     * @return string The base directory for themes.
     */
    static function getThemesDir() { return DIR_FS_CATALOG.ZM_THEMES_DIR; }

    /**
     * Return the directory containing all plugins.
     *
     * @return string The base directory for plugins.
     */
    static function getPluginsDir() { return DIR_FS_CATALOG.ZM_PLUGINS_DIR; }

    /**
     * Return the base path for theme URIs.
     *
     * @return string The URL path prefix for all themes.
     */
    static function getThemesPathPrefix() { return ZMRuntime::getContext().ZM_THEMES_DIR; }

    /**
     * Get the current theme.
     *
     * @return ZMTheme The current theme.
     */
    function getTheme() {
        if (null == $this->theme_) {
            $this->theme_ = $this->create("Theme", $this->getThemeId());
        }

        return $this->theme_;
    }

    /**
     * Get the single <code>ZMThemes</code> instance.
     *
     * @return ZMThemes A <code>ZMThemes</code> object.
     */
    function getThemes() {
        if (null == $this->themes_) {
            $this->themes_ = $this->create("Themes");
        }

        return $this->themes_;
    }

    /**
     * Get <code>ZMTheme</code> instance for the current (or given) theme Id.
     *
     * @param string themeId The theme id or <code>null</code> for the current theme id.
     * @return ZMTheme <code>ZMTheme</code> instance or <code>null</code>.
     */
    function getThemeForId($themeId=null) {
        if (null == $themeId) {
            return $this->getTheme();
        }
        $theme = $this->create("Theme", $themeId);
        return $theme;
    }

    /**
     * Get <code>ZMThemeInfo</code> instance for the current (or given) theme Id.
     *
     * @param string themeId The theme id or <code>null</code> for the current theme id.
     * @return ZMThemeInfo The themes <code>ZMThemeInfo</code> implementation or <code>null</code>.
     */
    function getThemeInfoForId($themeId=null) {
        $themes = $this->getThemes();
        return $themes->getThemeInfoForId($themeId);
    }

    /**
     * Get the full ZenMagick installation path.
     *
     * @return string The ZenMagick installation folder.
     */
    static function getZMRootPath() { return DIR_FS_CATALOG.ZM_ROOT; }

    /**
     * The application context.
     *
     * @return string The application context.
     */
    static function getContext() { return DIR_WS_CATALOG; }

    /**
     * Set the theme id.
     *
     * <p>This will overwrite the configured theme id.</p>
     *
     * <p>Calling this method is quite expensive, as all theme specific stuff needs
     * to be updated - <strong>this is not completely implemented yet</strong>.</p>
     *
     * @param string themeId The new theme id.
     */
    function setThemeId($themeId) { $this->themeId_ = $themeId; $this->theme_ = null; }

    /**
     * Get the configured zen-cart theme id.
     *
     * @return string The configured zen-cart theme id.
     */
    function getZCThemeId() {
        $themes = $this->getThemes();
        $id = $themes->getZCThemeId();
        return zm_is_empty($id) ? ZM_DEFAULT_THEME : $id;
    }

    /**
     * Get the effective theme id.
     *
     * @return string The currently effective theme id.
     */
    function getThemeId() {
        if (null != $this->themeId_) {
            return $this->themeId_;
        }

        //$themeId = strtolower($this->getZCThemeId($this->getLanguageId()));
        $this->themeId_ = $this->getZCThemeId($this->getLanguageId());
        $path = $this->getThemesDir().$this->themeId_;
        if (!@file_exists($path) || !@is_dir($path)) {
            $this->log("invalid theme id: '".$this->themeId_.'"');
            return ZM_DEFAULT_THEME;
        }

        return $this->themeId_;
    }

    /**
     * Get the language.
     *
     * @return ZMLanguage The current language.
     */
    function getLanguage() {
        $session = $this->create("Session");
        return $session->getLanguage();
    }

    /**
     * Get the language id.
     *
     * @return int The current language id.
     * @deprecated Use <code>$zm_request->getSession()->getLanguageId()</code> instead.
     */
    function getLanguageId() { return (int)$_SESSION['languages_id']; }

    /**
     * Get the current language name.
     *
     * @return string The current language name.
     * @deprecated Use <code>$zm_request->getSession()->getLanguage()->getDirectory()</code> instead.
     */
    function getLanguageName() { return $_SESSION['language']; }

    /**
     * Get the current currency.
     *
     * @return ZMCurrency The current currency.
     */
    function getCurrency() {
    global $zm_currencies;
   
        $currency = $zm_currencies->getCurrencyForCode($this->getCurrencyCode());
        return $currency;
    }

    /**
     * Get the current currency code.
     *
     * @return string The current currency code.
     * @deprecated Use <code>$zm_request->getCurrencyCode()</code> instead.
     */
    function getCurrencyCode() { return $_SESSION['currency']; }

}

?>
