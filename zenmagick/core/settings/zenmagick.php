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
 *
 * $Id$
 */
?>
<?php


    // ** ZenMagick setup **//

    /**
     * If changing ZM_ROOT, make sure to update 
     * ..\zen-cart\includes\init_includes\overrides\init_templates.php
     * and
     * ..\zen-cart\admin\includes\init_includes\overrides\init_templates.php
     *
     * The full order of action is:
     * 1) Uninstall all ZenMagick patches as some use this value
     * 2) Rename directory
     * 3) Update ZM_ROOT
     * 4) Update the files mentioned above
     */
    define('ZM_ROOT', 'zenmagick/');
    define('ZM_DEFAULT_THEME', 'default');
    define('ZM_THEMES_DIR', ZM_ROOT.'themes/');
    define('ZM_PLUGINS_DIR', ZM_ROOT.'plugins/');
    define('ZM_THEME_CONTENT_DIR', 'content/');
    define('ZM_THEME_EXTRA_DIR', 'extra/');
    define('ZM_THEME_BOXES_DIR', 'content/boxes/');
    define('ZM_THEME_LANG_DIR', 'lang/');
    define('ZM_THEME_STATIC_DIR', 'static/');


    //** events **//

    define('ZM_EVENT_INIT_DONE', 'init_done');
    define('ZM_EVENT_DISPATCH_START', 'dispatch_start');
    define('ZM_EVENT_DISPATCH_DONE', 'dispatch_done');
    define('ZM_EVENT_VIEW_START', 'view_start');
    define('ZM_EVENT_VIEW_DONE', 'view_done');
    define('ZM_EVENT_CONTROLLER_PROCESS_START', 'controller_process_start');
    define('ZM_EVENT_CONTROLLER_PROCESS_END', 'controller_process_end');
    define('ZM_EVENT_THEME_RESOLVED', 'theme_resolved');


    //** db **//

    define('ZM_DB_PREFIX', DB_PREFIX);
    // features
    define('ZM_TABLE_FEATURE_TYPES', ZM_DB_PREFIX . 'zm_feature_types');
    define('ZM_TABLE_PRODUCT_FEATURES', ZM_DB_PREFIX . 'zm_product_features');
    define('ZM_TABLE_FEATURES', ZM_DB_PREFIX . 'zm_features');


    //** files **//

    define ('ZM_FILENAME_COMPARE_PRODUCTS', 'product_comparison');
    define ('ZM_FILENAME_SOURCE_VIEW', 'source_view');
    define ('ZM_FILENAME_RSS', 'rss');
    define ('ZM_FILENAME_CHECKOUT_GUEST', 'checkout_guest');
    define ('ZM_FILENAME_GUEST_HISTORY', 'guest_history');
    define ('ZM_FILENAME_GV_SEND_CONFIRM', 'gv_send_confirm');


    //** admin **//

    define('ZM_ADMINFN_INSTALLATION', 'zmInstallation.php');
    define('ZM_ADMINFN_CATALOG_MANAGER', 'zmCatalogManager.php');
    define('ZM_ADMINFN_FEATURES', 'zmFeatures.php');
    define('ZM_ADMINFN_L10N', 'zmL10n.php');
    define('ZM_ADMINFN_CACHE', 'zmCacheManager.php');
    define('ZM_ADMINFN_ABOUT', 'zmAbout.php');
    define('ZM_ADMINFN_CONSOLE', 'zmConsole.php');
    define('ZM_ADMINFN_PLUGINS', 'zmPlugins.php');
    define('ZM_ADMINFN_THEME_BUILDER', 'zmThemeBuilder.php');


    //** plugins/modules **//

    define('ZM_PLUGIN_PREFIX', 'PLUGIN_');
    define('ZM_PLUGIN_ENABLED_SUFFIX', 'ENABLED');
    define('ZM_PLUGIN_ORDER_SUFFIX', 'ORDER');


    //** loglevel **//

    define('ZM_LOG_ERROR', 1);
    define('ZM_LOG_WARN', 2);
    define('ZM_LOG_INFO', 3);
    define('ZM_LOG_DEBUG', 4);
    define('ZM_LOG_TRACE', 5);


    //** fixed product images sizes **//

    define('PRODUCT_IMAGE_SMALL', 'small');
    define('PRODUCT_IMAGE_MEDIUM', 'medium');
    define('PRODUCT_IMAGE_LARGE', 'large');


    //** accounts **//

    define('ZM_ACCOUNT_TYPE_REGISTERED', 'registered');
    define('ZM_ACCOUNT_TYPE_GUEST', 'guest');
    define('ZM_ACCOUNT_TYPE_ANONYMOUS', 'anonymous');


    //** coupons **//

    define('ZM_COUPON_TYPPE_GV', 'G');
    define('ZM_COUPON_TYPPE_FIXED', 'F');
    define('ZM_COUPON_TYPPE_PERCENT', 'P');
    define('ZM_COUPON_TYPPE_SHIPPING', 'S');


    //** sale types **//

    define('ZM_SALE_TYPE_AMOUNT', 0);
    define('ZM_SALE_TYPE_PERCENT', 1);
    define('ZM_SALE_TYPE_PRICE', 2);


    //** others **//

    define('ZM_PRODUCT_TAX_BASE_STORE', 'Store');
    define('ZM_PRODUCT_TAX_BASE_SHIPPING', 'Shipping');
    define('ZM_PRODUCT_TAX_BASE_BILLING', 'Billing');


    //** others **//

    define('PRODUCTS_OPTIONS_TYPE_SELECT', 0);

?>
