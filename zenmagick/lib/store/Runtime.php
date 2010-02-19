<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Store runtime.
 *
 * @author DerManoMann
 * @package org.zenmagick.store
 * @version $Id$
 */
class Runtime extends ZMRuntime {
    private static $themeId_ = null;
    private static $theme_ = null;
    private static $db_ = null;


    /**
     * Get the database dao.
     *
     * @return queryFactory *The* zen-cart <code>queryFactory</code> instance.
     */
    public static function getDB() { if (null == self::$db_) { global $db; self::$db_ = $db; } return self::$db_; }

    /**
     * Get the context for plugins.
     *
     * @return string Either <code>Plugin::CONTEXT_STOREFRONT</code> or <code>Plugin::CONTEXT_ADMIN</code>.
     */
    public static function getPluginContext() {
        return ZMSettings::get('isAdmin') ? Plugin::CONTEXT_ADMIN : Plugin::CONTEXT_STOREFRONT;
    }

    /**
     * Return the directory containing all themes.
     *
     * @return string The base directory for themes.
     */
    public static function getThemesDir() { return ZM_BASE_PATH.'themes'.DIRECTORY_SEPARATOR; }

    /**
     * Return the base path for theme URIs.
     *
     * @return string The URL path prefix for all themes.
     */
    public static function getThemesPathPrefix() { return self::getContext().ZM_ROOT.'themes/'; }

    /**
     * Return the base path for plugin URIs.
     *
     * @return string The URL path prefix for all plugins.
     */
    public static function getPluginPathPrefix() { return self::getContext().ZM_ROOT.'plugins/'; }

    /**
     * The application context.
     *
     * @return string The application context.
     */
    public static function getContext() { return DIR_WS_CATALOG; }

    /**
     * Get the effective theme id.
     *
     * @return string The currently effective theme id.
     */
    public static function getThemeId() {
        if (null != self::$themeId_) {
            return self::$themeId_;
        }

        if (null != self::$theme_) {
            return self::$theme_->getThemeId();
        }

        self::$themeId_ = ZMThemes::instance()->getZCThemeId();
        $path = self::getThemesDir().self::$themeId_;
        if (!@file_exists($path) || !@is_dir($path)) {
            ZMLogging::instance()->log("invalid theme id: '".self::$themeId_.'"');
            self::$themeId_ = null;
            return ZMSettings::get('defaultThemeId');
        }

        return self::$themeId_;
    }

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
    public static function setThemeId($themeId) { 
        self::$themeId_ = $themeId; 
        self::$theme_ = null;
    }

    /**
     * Get the current theme.
     *
     * @return ZMTheme The current theme.
     */
    public static function getTheme() {
        if (null == self::$theme_) {
            self::$theme_ = ZMLoader::make("Theme", self::getThemeId());
        }

        return self::$theme_;
    }

    /**
     * Set the current theme.
     *
     * @param ZMTheme theme The theme.
     */
    public static function setTheme($theme) {
        self::$theme_ = $theme;
    }

    /**
     * Get the language.
     *
     * @return ZMLanguage The current language.
     */
    public static function getLanguage() {
        return ZMObject::singleton('Session')->getLanguage();
    }

    /**
     * Get the default language.
     *
     * @return ZMLanguage The default language.
     */
    public static function getDefaultLanguage() {
        $language = ZMLanguages::instance()->getLanguageForId(ZMSettings::get('storeDefaultLanguageId'));
        if (null == $language) {
            ZMLogging::instance()->log('no default language found - using en as fallback', ZMLogging::WARN);
            $language = ZMLoader::make("Language");
            $language->setId(1);
            $language->setDirectory('english');
            $language->setCode('en');
        }
        return $language;
    }

    /**
     * Get the current currency.
     *
     * @return ZMCurrency The current currency.
     */
    public static function getCurrency() {
        $session = ZMObject::singleton('Session');
        $currency = ZMCurrencies::instance()->getCurrencyForCode($session->getCurrencyCode());
        return $currency;
    }

    /**
     * Finish execution.
     *
     * <p>Calling this function should be the last line in your script.</p>
     */
    public static function finish() {
        session_write_close();
        exit;
    }

    /**
     * Get the store base URL.
     *
     * @param boolean secure If set, return a secure URL (if configured); default is <code>false</code>.
     * @return string The store base url.
     */
    public static function getBaseURL($secure=false) {
        if ($secure && ZMSettings::get('isEnableSSL')) {
            return HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
        } else {
            return HTTP_SERVER . DIR_WS_CATALOG;
        }
    }

}
