<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2007 ZenMagick
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
    ini_set("display_errors",false);
    ini_set("log_errors",true); 
    @ini_set("register_globals", 0);

    // ZenMagick bootstrap
    $_zm_bin_file = dirname(__FILE__)."/core.php";
    if (!IS_ADMIN_FLAG && file_exists($_zm_bin_file)) {
        require($_zm_bin_file);

        // configure core loader
        $zm_loader =& new ZMLoader('coreLoader');
    } else {
        $_zm_bin_dir = dirname(__FILE__)."/core/";
        require($_zm_bin_dir."bootstrap.php");
        require($_zm_bin_dir."settings/settings.php");
        require($_zm_bin_dir."settings/zenmagick.php");
        require($_zm_bin_dir."ZMLoader.php");
        require($_zm_bin_dir."ZMDao.php");
        require($_zm_bin_dir."dao/ZMThemes.php");
        //require($_zm_bin_dir."rp/uip/themes/ZMTheme.php");

        // configure core loader
        $zm_loader =& new ZMLoader('coreLoader');
        $zm_loader->addPath($_zm_bin_dir);
        // need to do this in global namespace
        foreach ($zm_loader->getStatic() as $static) {
            require_once($static);
        }
    }

    // use loader for all class loading from here?
    $zm_runtime =& $zm_loader->create("ZMRuntime");

    // here the loader should take over...
    if (!defined('ZM_SINGLE_CORE')) {
        $includes = zm_find_includes($_zm_bin_dir, true);
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
    $zm_layout = new ZMLayout();

    // set up main class instances (aka the ZenMagick API)
    $zm_products = new ZMProducts();
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
    $zm_music = new ZMMusic();
    $zm_mediaManager = new ZMMediaManager();

    $zm_account = $zm_request->getAccount();

    // these can be replaced by themes; will be reinitializes durin theme switching
    $zm_crumbtrail =& $zm_loader->create('Crumbtrail');
    $zm_meta =& $zm_loader->create('MetaTags');

    // global settings
    $_zm_local = $zm_runtime->getZMRootPath()."local.php";
    if (file_exists($_zm_local)) {
        include($_zm_local);
    }

    // load 
    if (zm_setting('isEnableZenMagick')) {
        $zm_theme =& zm_resolve_theme(zm_setting('isEnableThemeDefaults') ? ZM_DEFAULT_THEME : $zm_runtime->getThemeId());
    } else {
        $zm_theme =& $zm_runtime->getTheme();
    }
    $zm_themeInfo = $zm_theme->getThemeInfo();

    require(DIR_FS_CATALOG.'zenmagick/zc_fixes.php');

    // handle page caching
    if (zm_setting('isEnableZenMagick') && zm_setting('isPageCacheEnabled')) {
        $pageCache = $zm_runtime->getPageCache();
        if ($pageCache->isCacheable() && $contents = $pageCache->get()) {
            echo $contents;
            if (zm_setting('isDisplayTimerStats')) {
                $_zm_db = $zm_runtime->getDB();
                echo '<!-- stats: ' . round($_zm_db->queryTime(), 4) . ' sec. for ' . $_zm_db->queryCount() . ' queries; ';
                echo 'page: ' . zm_get_elapsed_time() . ' sec. -->';
            }
            require('includes/application_bottom.php');
            exit;
        }
    }

    if (zm_setting('isEnableOB') && zm_setting('isEnableZenMagick') && !zm_setting('isAdmin')) { ob_start(); }

?>
