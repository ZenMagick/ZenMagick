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
 */
?>
<?php


// compare 
define ('ZM_FILENAME_COMPARE_PRODUCTS', 'product_comparison');

// features
define('ZM_TABLE_FEATURE_TYPES', ZM_DB_PREFIX . 'zm_feature_types');
define('ZM_TABLE_PRODUCT_FEATURES', ZM_DB_PREFIX . 'zm_product_features');
define('ZM_TABLE_FEATURES', ZM_DB_PREFIX . 'zm_features');


/**
 * Plugin adding product features.
 *
 * @package org.zenmagick.plugins.zm_product_features
 * @author DerManoMann
 * @version $Id$
 */
class zm_product_features extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Product Features', 'Comparable product features', '${zenmagick.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setPreferredSortOrder(100);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Install this plugin.
     */
    function install() {
        parent::install();
        ZMDbUtils::executePatch(file($this->getPluginDir()."sql/features.sql"), $this->messages_);
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file($this->getPluginDir()."sql/features_undo.sql"), $this->messages_);
    }


    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        // make ZMFeatures available by pre-loading it
        ZMLoader::resolve("Features");

        if (0 < ZMRequest::getProductId()) {
            // only available if product selected
            $this->addMenuItem('zm_product_features', zm_l10n_get('Product Features'), 'zm_product_features_admin', ZM_MENU_CATALOG_ADMIN);
        }
    }

}

?>
