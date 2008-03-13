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
 *
 * $Id$
 */
?>
<?php

    error_reporting(E_ALL^E_NOTICE);
    // hide as to avoid filenames that contain account names, etc.
    @ini_set("display_errors", false);
    @ini_set("log_errors", true); 
    @ini_set("register_globals", false);

    // ZenMagick bootstrap
    if (!IS_ADMIN_FLAG && file_exists(dirname(__FILE__).'/core.php')) {
        require(dirname(__FILE__).'/core.php');
    } else {
        $coreDir = dirname(__FILE__).'/core/';
        require($coreDir."settings/zenmagick.php");
        require($coreDir."settings/settings.php");
        require($coreDir."ZMObject.php");
        require($coreDir."ZMLoader.php");

        // prepare loader
        ZMLoader::instance()->addPath($coreDir);
        ZMLoader::instance()->loadStatic();

        // preload some stuff
        foreach (ZMLoader::instance()->getClassPath() as $name => $file) {
            if ($name == $file) { continue; } // this is static stuff
            // exclude some stuff that gets resolved dynamically
            if ((false === strpos($file, '/controller/')
                  && false === strpos($file, '/model/')
                  && false === strpos($file, '/admin/')
                  && false === strpos($file, '/settings/'))
                || (false !== strpos($file, '/admin/') && zm_setting('isAdmin'))) {
                require_once($file);
            }
        }
    }

    // load global settings
    if (file_exists(dirname(__FILE__).'/local.php')) {
        require(dirname(__FILE__).'/local.php');
    }

    // now we can check for a static homepage
    if (!zm_is_empty(zm_setting('staticHome')) && 'index' == ZMRequest::getPageName() && (0 == count(ZMRequest::getParameterMap()))) {
        require(zm_setting('staticHome'));
        exit;
    }

    if (zm_setting('isLegacyAPI')) {
        // deprecated legacy globals
        $zm_request = new ZMRequest();
        $zm_loader = ZMLoader::instance();
        $zm_runtime = ZMRuntime::instance();
        $zm_layout = ZMLayout::instance();
        $zm_products = ZMProducts::instance();
        $zm_taxes = ZMTaxRates::instance();
        $zm_reviews = ZMReviews::instance();
        $zm_pages = ZMEZPages::instance();
        $zm_coupons = ZMCoupons::instance();
        $zm_banners = ZMBanners::instance();
        $zm_orders = ZMOrders::instance();
        $zm_events = ZMEvents::instance();
        $zm_addresses = ZMAddresses::instance();
        $zm_messages = ZMMessages::instance();
        $zm_validator = ZMValidator::instance();
        $zm_categories = ZMCategories::instance();
        $zm_manufacturers = ZMManufacturers::instance();
        $zm_crumbtrail = ZMCrumbtrail::instance();
        $zm_meta = ZMMetaTags::instance();
        $zm_currencies = ZMCurrencies::instance();
        $zm_languages = ZMLanguages::instance();
        $zm_countries = ZMCountries::instance();
        $zm_accounts = ZMAccounts::instance();
        $zm_account = ZMRequest::getAccount();
        $zm_cart = new ZMShoppingCart();
    }

    // register custom error handler
    if (zm_setting('isZMErrorHandler') && null != zm_setting('zmLogFilename')) {
        error_reporting(E_ALL);
        set_error_handler("zm_error_handler");
    }

    // init and admin plugins
    ZMLoader::make("Plugins");
    ZMPlugins::initPlugins('init', ZMRuntime::getScope());
    ZMPlugins::initPlugins('admin', ZMRuntime::getScope());

    // load default mappings
    zm_set_default_url_mappings();
    zm_set_default_sacs_mappings();

    // make sure to use SSL if required
    ZMSacsMapper::instance()->ensureAccessMethod();

    // upset request plugins :)
    ZMPlugins::initPlugins('request', ZMRuntime::getScope());

    // resolve theme to be used 
    if (zm_setting('isEnableZenMagick') && !zm_setting('isAdmin')) {
        ZMRuntime::setTheme(zm_resolve_theme(zm_setting('isEnableThemeDefaults') ? ZM_DEFAULT_THEME : ZMRuntime::getThemeId()));
    }

    if (zm_setting('isLegacyAPI')) {
        // deprecated legacy globals
        $zm_theme = ZMRuntime::getTheme();
        $zm_themeInfo = $zm_theme->getThemeInfo();
    }

    if (zm_setting('isEnableZenMagick')) {
        require(DIR_FS_CATALOG.ZM_ROOT.'zc_fixes.php');
    }

    // always echo in admin
    if (zm_setting('isAdmin')) { zm_set_setting('isEchoHTML', true); }
    // this is used as default value for the $echo parameter for HTML functions
    define('ZM_ECHO_DEFAULT', zm_setting('isEchoHTML'));

    // start output buffering
    if (zm_setting('isEnableZenMagick') && !zm_setting('isAdmin')) { ob_start(); }

    ZMEvents::instance()->fireEvent(null, ZM_EVENT_INIT_DONE);

?>
