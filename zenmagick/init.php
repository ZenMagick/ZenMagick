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

    // mark CLI calls
    define('ZM_CLI_CALL', defined('STDIN'));

    define('ZM_START_TIME', microtime());

    error_reporting(E_ALL^E_NOTICE);
    // hide as to avoid filenames that contain account names, etc.
    @ini_set("display_errors", false);
    @ini_set("log_errors", true); 
    @ini_set("register_globals", false);

    // ZenMagick bootstrap
    if (!IS_ADMIN_FLAG && file_exists(dirname(__FILE__).'/core.php')) {
        require(dirname(__FILE__).'/core.php');
    } else {
        $_zm_coreDir = dirname(__FILE__).'/core/';
        require($_zm_coreDir."settings/zenmagick.php");
        require($_zm_coreDir."ZMSettings.php");
        require($_zm_coreDir."settings/settings.php");
        require($_zm_coreDir."ZMObject.php");
        require($_zm_coreDir."ZMLoader.php");

        // prepare loader
        ZMLoader::instance()->addPath($_zm_coreDir);
        ZMLoader::instance()->loadStatic();

        // preload some stuff
        foreach (ZMLoader::instance()->getClassPath() as $_zm_name => $_zm_file) {
            if ($_zm_name == $_zm_file) { continue; } // this is static stuff
            // exclude some stuff that gets resolved dynamically
            if ((false === strpos($_zm_file, '/controller/')
                  && false === strpos($_zm_file, '/admin/')
                  && false === strpos($_zm_file, '/model/')
                  && false === strpos($_zm_file, '/rules/')
                  && false === strpos($_zm_file, '/provider/')
                  && false === strpos($_zm_file, '/settings/'))
                || (false !== strpos($_zm_file, '/admin/') && ZMSettings::get('isAdmin'))) {
                require_once($_zm_file);
            }
        }
    }

    // load global settings
    if (file_exists(dirname(__FILE__).'/local.php')) {
        require(dirname(__FILE__).'/local.php');
    }

    // upset plugins
    ZMLoader::make("Plugins");
    ZMPlugins::initPlugins(array('init', 'admin', 'request'), ZMRuntime::getScope());

    // register custom error handler
    if (ZMSettings::get('isZMErrorHandler') && null != ZMSettings::get('zmLogFilename')) {
        set_error_handler(array(ZMLogging::instance(), 'errorHandler'));
        set_exception_handler(array(ZMLogging::instance(), 'exceptionHandler'));
    }

    // now we can check for a static homepage
    if (!ZMTools::isEmpty(ZMSettings::get('staticHome')) && 'index' == ZMRequest::getPageName() 
        && (0 == ZMRequest::getCategoryId() && 0 == ZMRequest::getManufacturerId())) {
        require ZMSettings::get('staticHome');
        exit;
    }

    if (ZMSettings::get('isLegacyAPI')) {
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
        $zm_urlMapper = ZMUrlMapper::instance();
        $zm_sacsMapper = ZMSacsMapper::instance();
    }

    // load default mappings
    zm_set_default_url_mappings();
    zm_set_default_sacs_mappings();

    // make sure to use SSL if required
    ZMSacsMapper::instance()->ensureAccessMethod();

    // resolve theme to be used 
    if (ZMSettings::get('isEnableZenMagick')) {
        $_zm_theme = ZMThemes::instance()->resolveTheme(ZMSettings::get('isEnableThemeDefaults') ? ZM_DEFAULT_THEME : ZMRuntime::getThemeId());
        ZMRuntime::setTheme($_zm_theme);
    }

    if (ZMSettings::get('isLegacyAPI')) {
        // deprecated legacy globals
        $zm_theme = ZMRuntime::getTheme();
        $zm_themeInfo = $zm_theme->getThemeInfo();
    }

    if (ZMSettings::get('isEnableZenMagick')) {
        require(DIR_FS_CATALOG.ZM_ROOT.'zc_fixes.php');
    }

    // manually set toolbox for admin
    if (ZMSettings::get('isAdmin')) { $_t = ZMToolbox::instance(); }

    // always echo in admin
    if (ZMSettings::get('isAdmin')) { ZMSettings::get('isEchoHTML', true); }
    // this is used as default value for the $echo parameter for HTML functions
    define('ZM_ECHO_DEFAULT', ZMSettings::get('isEchoHTML'));

    // load stuff that really needs to be global!
    foreach (ZMLoader::instance()->getGlobal() as $_zm_global) {
        include $_zm_global;
    }

    // start output buffering
    if (ZMSettings::get('isEnableZenMagick') && !ZMSettings::get('isAdmin')) { ob_start(); }

    ZMEvents::instance()->fireEvent(null, ZM_EVENT_INIT_DONE);

?>
