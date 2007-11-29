<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
    var $themeId_;
    var $pageCache_;
    var $theme_;
    var $themes_;


    /**
     * Default c'tor.
     */
    function ZMRuntime() {
        parent::__construct();

        // init with defaults
        $this->themeId_ = null;
        $this->pageCache_ = null;
        $this->theme_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMRuntime();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
    }


    /**
     * Get the database dao.
     *
     * @return queryFactory *The* zen-cart <code>queryFactory</code> instance.
     */
    function getDB() { global $db; return $db; }

    /**
     * Get the page cache.
     *
     * @return ZMCache A <code>ZMCache</code> instance or <code>null</code>.
     */
    function getPageCache() {
    global $zm_loader;

        if (null === $this->pageCache_) {
            $this->pageCache_ =& $zm_loader->create('PageCache');
        }
        return $this->pageCache_;
    }

    /**
     * Return the directory containing all themes.
     *
     * @return string The base directory for themes.
     */
    function getThemesDir() { return DIR_FS_CATALOG.ZM_THEMES_DIR; }

    /**
     * Return the directory containing all plugins.
     *
     * @return string The base directory for plugins.
     */
    function getPluginsDir() { return DIR_FS_CATALOG.ZM_PLUGINS_DIR; }

    /**
     * Return the base path for theme URIs.
     *
     * @return string The URL path prefix for all themes.
     */
    function getThemesPathPrefix() { return $this->getContext().ZM_THEMES_DIR; }

    /**
     * Get the current theme.
     *
     * @return ZMTheme The current theme.
     */
    function &getTheme() {
        if (null == $this->theme_) {
            $this->theme_ =& $this->create("Theme", $this->getThemeId());
        }

        return $this->theme_;
    }

    /**
     * Get the single <code>ZMThemes</code> instance.
     *
     * @return ZMThemes A <code>ZMThemes</code> object.
     */
    function &getThemes() {
        if (null == $this->themes_) {
            $this->themes_ =& $this->create("Themes");
        }

        return $this->themes_;
    }

    /**
     * Get <code>ZMTheme</code> instance for the current (or given) theme Id.
     *
     * @param string themeId The theme id or <code>null</code> for the current theme id.
     * @return ZMTheme <code>ZMTheme</code> instance or <code>null</code>.
     */
    function &getThemeForId($themeId=null) {
        if (null == $themeId) {
            return $this->getTheme();
        }
        $theme =& $this->create("Theme", $themeId);
        return $theme;
    }

    /**
     * Get <code>ZMThemeInfo</code> instance for the current (or given) theme Id.
     *
     * @param string themeId The theme id or <code>null</code> for the current theme id.
     * @return ZMThemeInfo The themes <code>ZMThemeInfo</code> implementation or <code>null</code>.
     */
    function getThemeInfoForId($themeId=null) {
        $themes =& $this->getThemes();
        return $themes->getThemeInfoForId($themeId);
    }

    /**
     * Get the full ZenMagick installation path.
     *
     * @return string The ZenMagick installation folder.
     */
    function getZMRootPath() {  return DIR_FS_CATALOG.ZM_ROOT; }

    /**
     * The application context.
     *
     * @return string The application context.
     */
    function getContext() { return DIR_WS_CATALOG; }

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
        $themes =& $this->getThemes();
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

        $themeId = strtolower($this->getZCThemeId($this->getLanguageId()));
        $path = $this->getThemesDir().$themeId;
        if (!@file_exists($path) || !@is_dir($path)) {
            zm_log("invalid theme id: '".$themeId.'"');
            return ZM_DEFAULT_THEME;
        }

        return $themeId;
    }

    /**
     * Get the language.
     *
     * @return ZMLanguage The current language.
     */
    function getLanguage() {
    global $zm_languages;
      
        $language =& $zm_languages->getLanguageForId($this->getLanguageId());
        return $language;
    }

    /**
     * Get the language id.
     *
     * @return int The current language id.
     */
    function getLanguageId() { return (int)$_SESSION['languages_id']; }

    /**
     * Get the current language name.
     *
     * @return string The current language name.
     */
    function getLanguageName() { return $_SESSION['language']; }

    /**
     * Get the current currency.
     *
     * @return ZMCurrency The current currency.
     */
    function &getCurrency() {
    global $zm_currencies;
   
        $currency =& $zm_currencies->getCurrencyForCode($this->getCurrencyCode());
        return $currency;
    }

    /**
     * Get the current currency code.
     *
     * @return string The current currency code.
     */
    function getCurrencyCode() { return $_SESSION['currency']; }

}

?>
