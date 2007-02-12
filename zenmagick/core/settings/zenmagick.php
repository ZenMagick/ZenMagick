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

    // ZenMagick setup
    define('ZM_ROOT', 'zenmagick/');
    define('ZM_CONTROLLER_PATH', 'zenmagick/core/controller/');
    define('ZM_THEME_BASE_PATH', 'zenmagick/themes/');
    define('ZM_THEME_CONTENT', 'content/');
    define('ZM_THEME_EXTRA', 'extra/');
    define('ZM_THEME_BOXES', 'content/boxes/');

    // db
    define('ZM_DB_PREFIX', DB_PREFIX);
    define('ZM_TABLE_FEATURE_TYPES', ZM_DB_PREFIX . 'zm_feature_types');
    define('ZM_TABLE_PRODUCT_FEATURES', ZM_DB_PREFIX . 'zm_product_features');
    define('ZM_TABLE_FEATURES', ZM_DB_PREFIX . 'zm_features');

    // files
    define ('ZM_FILENAME_COMPARE_PRODUCTS', 'product_comparison');
    define ('ZM_FILENAME_SOURCE_VIEW', 'source_view');

    // admin
    define('ZM_ADMINFN_INSTALLATION', 'zmInstallation.php');
    define('ZM_ADMINFN_CATALOG_MANAGER', 'zmCatalogManager.php');
    define('ZM_ADMINFN_FEATURES', 'zmFeatures.php');
    define('ZM_ADMINFN_L10N', 'zmL10n.php');
    define('ZM_ADMINFN_CACHE', 'zmCacheManager.php');

?>
