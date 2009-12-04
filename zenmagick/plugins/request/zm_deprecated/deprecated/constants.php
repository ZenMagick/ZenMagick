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
 *
 * $Id$
 */
?>
<?php


    //** others **//

    //** deprecated **//
    define('ZM_SESSION_TOKEN_NAME', 'stoken');


    //** menu keys; deprecated **//

    define('ZM_MENU_PLUGINS', 'menu_plugins');
    define('ZM_MENU_CATALOG_ADMIN', 'catalog_manager_tab');


    //** events; deprecated **//

    define('ZM_EVENT_INIT_DONE', 'init_done');
    define('ZM_EVENT_DISPATCH_START', 'dispatch_start');
    define('ZM_EVENT_DISPATCH_DONE', 'dispatch_done');
    define('ZM_EVENT_VIEW_START', 'view_start');
    define('ZM_EVENT_VIEW_DONE', 'view_done');
    define('ZM_EVENT_CONTROLLER_PROCESS_START', 'controller_process_start');
    define('ZM_EVENT_CONTROLLER_PROCESS_END', 'controller_process_end');
    define('ZM_EVENT_THEME_RESOLVED', 'theme_resolved');
    define('ZM_EVENT_ALL_DONE', 'all_done');
    define('ZM_EVENT_CREATE_ACCOUNT', 'create_account');
    define('ZM_EVENT_LOGIN_SUCCESS', 'login_success');
    define('ZM_EVENT_LOGOFF_SUCCESS', 'logoff_success');
    define('ZM_EVENT_GENERATE_EMAIL', 'generate_email');
    define('ZM_EVENT_CREATE_ORDER', 'create_order');


    //** files;deprecated **//

    define ('ZM_FILENAME_RSS', 'rss');
    define ('ZM_FILENAME_CHECKOUT_GUEST', 'checkout_guest');
    define ('ZM_FILENAME_GUEST_HISTORY', 'guest_history');
    define ('ZM_FILENAME_GV_SEND_CONFIRM', 'gv_send_confirm');
    define ('ZM_FILENAME_CATEGORY', 'category');
    define ('ZM_FILENAME_SEARCH', 'search');


    //** admin;deprecated **//

    define('ZM_ADMINFN_INSTALLATION', 'zmInstallation.php');
    define('ZM_ADMINFN_CATALOG_MANAGER', 'zmCatalogManager.php');
    define('ZM_ADMINFN_L10N', 'zmL10n.php');
    define('ZM_ADMINFN_ABOUT', 'zmAbout.php');
    define('ZM_ADMINFN_CONSOLE', 'zmConsole.php');
    define('ZM_ADMINFN_PLUGINS', 'zmPlugins.php');
    define('ZM_ADMINFN_THEME_BUILDER', 'zmThemeBuilder.php');
    define('ZM_ADMINFN_SP_EDITOR', 'zmStaticPageEditor.php');
    define('ZM_ADMINFN_CACHE', 'zmCacheAdmin.php');


    //** plugins/modules; deprecated **//

    define('ZM_PLUGIN_PREFIX', 'PLUGIN_');
    define('ZM_PLUGIN_ENABLED_SUFFIX', 'ENABLED');
    define('ZM_PLUGIN_ORDER_SUFFIX', 'ORDER');

    define('ZM_SCOPE_STORE', 'store');
    define('ZM_SCOPE_ADMIN', 'admin');
    define('ZM_SCOPE_ALL', 'all');


    //** fixed product images sizes; deprecated **//

    define('PRODUCT_IMAGE_SMALL', 'small');
    define('PRODUCT_IMAGE_MEDIUM', 'medium');
    define('PRODUCT_IMAGE_LARGE', 'large');


    //** accounts; deprecated **//

    define('ZM_ACCOUNT_TYPE_REGISTERED', 'registered');
    define('ZM_ACCOUNT_TYPE_GUEST', 'guest');
    define('ZM_ACCOUNT_TYPE_ANONYMOUS', 'anonymous');


    //** coupon types; deprecated **//

    define('ZM_COUPON_TYPPE_GV', 'G');
    define('ZM_COUPON_TYPPE_FIXED', 'F');
    define('ZM_COUPON_TYPPE_PERCENT', 'P');
    define('ZM_COUPON_TYPPE_SHIPPING', 'S');


    //** sale types; deprecated **//

    define('ZM_SALE_TYPE_AMOUNT', 0);
    define('ZM_SALE_TYPE_PERCENT', 1);
    define('ZM_SALE_TYPE_PRICE', 2);


    //** discount types; deprecated **//

    define('ZM_DISCOUNT_TYPE_NONE', 0);
    define('ZM_DISCOUNT_TYPE_PERCENT', 1);
    define('ZM_DISCOUNT_TYPE_PRICE', 2);
    define('ZM_DISCOUNT_TYPE_AMOUNT', 3);
    define('ZM_DISCOUNT_FROM_BASE_PRICE', 0);
    define('ZM_DISCOUNT_FROM_SPECIAL_PRICE', 1);


    //** tax base; deprecated **//

    define('ZM_PRODUCT_TAX_BASE_STORE', 'Store');
    define('ZM_PRODUCT_TAX_BASE_SHIPPING', 'Shipping');
    define('ZM_PRODUCT_TAX_BASE_BILLING', 'Billing');


    //** account authentication; deprecated **//

    define('ZM_ACCOUNT_AUTHORIZATION_ENABLED', 0);
    define('ZM_ACCOUNT_AUTHORIZATION_PENDING', 1);
    define('ZM_ACCOUNT_AUTHORIZATION_BLOCKED', 4);

?>
