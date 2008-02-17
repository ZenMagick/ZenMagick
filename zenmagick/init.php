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
    @ini_set("register_globals", 0);

    // ZenMagick bootstrap
    $_zm_core_file = dirname(__FILE__)."/core.php";
    if (!IS_ADMIN_FLAG && file_exists($_zm_core_file)) {
        require($_zm_core_file);

        // configure core loader
        $zm_loader =& new ZMLoader('coreLoader');
    } else {
        $_zm_core_dir = dirname(__FILE__)."/core/";
        require($_zm_core_dir."settings/zenmagick.php");
        require($_zm_core_dir."settings/settings.php");
        require($_zm_core_dir."utils.php");
        require($_zm_core_dir."ZMObject.php");
        require($_zm_core_dir."ZMLoader.php");
        require($_zm_core_dir."ZMRuntime.php");
        require($_zm_core_dir."ZMService.php");
        require($_zm_core_dir."service/ZMThemes.php");

        // configure core loader
        $zm_loader =& new ZMLoader('coreLoader');
        $zm_loader->addPath($_zm_core_dir);
        // need to do this in global namespace
        foreach ($zm_loader->getStatic() as $static) {
            require_once($static);
        }
    }

    // classes might depend on runtime being available in their c'tor
    $zm_runtime = new ZMRuntime();

    // here the loader should take over...
    if (!defined('ZM_SINGLE_CORE')) {
        $includes = zm_find_includes($_zm_core_dir, true);
        foreach ($includes as $include) {
            // exclude some stuff that gets loaded by the loader
            if ((false === strpos($include, '/controller/')
                && false === strpos($include, '/model/')
                && false === strpos($include, '/admin/')
                && false === strpos($include, '/settings/'))
                || (false !== strpos($include, '/admin/') && zm_setting('isAdmin'))) {
                require_once($include);
            }
        }
    }

    $zm_request = new ZMRequest();

    // set up main class instances (aka the ZenMagick API)
    $zm_layout = new ZMLayout();
    $zm_products = new ZMProducts();
    $zm_taxes = new ZMTaxRates();
    $zm_reviews = new ZMReviews();
    $zm_categories = new ZMCategories();
    $zm_features = new ZMFeatures();
    $zm_manufacturers = new ZMManufacturers();
    $zm_accounts = new ZMAccounts();
    $zm_currencies = new ZMCurrencies();
    $zm_addresses = new ZMAddresses();
    $zm_countries = new ZMCountries();
    $zm_orders = new ZMOrders();
    $zm_cart = new ZMShoppingCart();
    $zm_messages = new ZMMessages();
    $zm_pages = new ZMEZPages();
    $zm_coupons = new ZMCoupons();
    $zm_banners = new ZMBanners();
    $zm_languages = new ZMLanguages();
    $zm_validator = new ZMValidator();
    // share instance
    $zm_account = $zm_request->getAccount();
    // event proxy to simplify event subscription
    $zm_events = new ZMEvents();

    // TODO: get rid of this
    // these can be replaced by themes; will be reinitializes during theme switching
    $zm_crumbtrail = $zm_loader->create('Crumbtrail');
    $zm_meta = $zm_loader->create('MetaTags');

    // load global settings
    $_zm_local = $zm_runtime->getZMRootPath()."local.php";
    if (file_exists($_zm_local)) {
        include($_zm_local);
    }

    // here we can check for a static homepage
    if (!zm_is_empty(zm_setting('staticHome')) && 'index' == $zm_request->getPageName()
          && null == $zm_request->getCategoryPath() && null == $zm_request->getManufacturerId() && null == $zm_request->getParameter('compareId')) {
        require(zm_setting('staticHome'));
        exit;
    }

    if (zm_setting('isZMErrorHandler') && null != zm_setting('zmLogFilename')) {
        // register custom error handler
        //error_reporting(E_ALL);
        set_error_handler("zm_error_handler");
    }

    $zm_plugins = new ZMPlugins();

    $_zm_scope = zm_setting('isAdmin') ? ZM_SCOPE_ADMIN : ZM_SCOPE_STORE;
    if (!zm_setting('isAdmin')) {
        zm_init_plugins('init', $_zm_scope);
    }

    if (zm_setting('isAdmin')) {
        // admin plugins
        zm_init_plugins('admin', $_zm_scope);
    }

    // set up *before* theme is resolved...
    $zm_urlMapper = new ZMUrlMapper();
    zm_set_default_url_mappings();
    $zm_sacsMapper = new ZMSacsMapper();
    zm_set_default_sacs_mappings();

    // make sure to use SSL if required
    $zm_sacsMapper->ensureAccessMethod();

    // upset request plugins :)
    zm_init_plugins('request', $_zm_scope);

    // resolve theme to be used 
    if (zm_setting('isEnableZenMagick')) {
        $zm_theme = zm_resolve_theme(zm_setting('isEnableThemeDefaults') ? ZM_DEFAULT_THEME : $zm_runtime->getThemeId());
    } else {
        $zm_theme = $zm_runtime->getTheme();
    }
    $zm_themeInfo = $zm_theme->getThemeInfo();

    if (zm_setting('isEnableZenMagick')) {
        require(DIR_FS_CATALOG.ZM_ROOT.'zc_fixes.php');
    }

    define('ZM_ECHO_DEFAULT', zm_setting('isEchoHTML'));

    // start output buffering
    if (zm_setting('isEnableZenMagick') && !zm_setting('isAdmin')) { ob_start(); }

    $zm_events->fireEvent($zm_runtime, ZM_EVENT_INIT_DONE);

?>
