<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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

    error_reporting(E_ALL ^ E_NOTICE);
    ini_set("register_globals", 0);

    // ZenMagick bootstrap
    $_zm_bin = dirname(__FILE__)."/core";
    require($_zm_bin."/bootstrap.php");
    $includes = zm_find_includes($_zm_bin, true);
    foreach ($includes as $include) {
        // exclude some stuff that gets loaded by the loader
        if (false === strpos($include, '/controller/') && false === strpos($include, '/model/')) {
            include($include);
        }
    }
    // set up main class instances (aka the ZenMagick API)
    $zm_runtime = new ZMRuntime();
    $zm_loader = new ZMClassLoader();
    $zm_request = new ZMRequest();
    $zm_layout = new ZMLayout();

    $zm_products = new ZMProducts();
    $zm_reviews = new ZMReviews();
    $zm_categories = new ZMCategories($cPath_array);
    $zm_features = new ZMFeatures();
    $zm_manufacturers = new ZMManufacturers();
    $zm_accounts = new ZMAccounts();
    $zm_currencies = new ZMCurrencies();
    $zm_addresses = new ZMAddresses();
    $zm_countries = new ZMCountries();
    $zm_orders = new ZMOrders();
    $zm_cart = new ZMShoppingCart();
    $zm_crumbtrail = new ZMCrumbtrail();
    $zm_messages = new ZMMessages();
    $zm_pages = new ZMEZPages();
    $zm_coupons = new ZMCoupons();
    $zm_banners = new ZMBanners();
    $zm_meta = $zm_loader->create('MetaTags');
    $zm_languages = new ZMLanguages();
    $zm_music = new ZMMusic();
    $zm_mediaManager = new ZMMediaManager();

    // needs to be instantiated after application_top.php
    $zm_messages = new ZMMessages();

    // local settings
    $_zm_local = $zm_runtime->getZMRootPath()."local.php";
    if (file_exists($_zm_local)) {
        include $_zm_local;
    }

    require('zc_fixes.php');

?>
