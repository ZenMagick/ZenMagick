<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
class zm_product_features extends Plugin implements ZMRequestHandler {

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
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/features.sql")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/features_undo.sql")), $this->messages_);
    }


    /**
     * {@inheritDoc}
     */
    public function initRequest($request) {
        if (0 < $request->getProductId()) {
            // only available if product selected
            $this->addMenuItem('zm_product_features', _zm('Product Features'), 'zm_product_features_admin', ZMAdminMenu::MENU_CATALOG_MANAGER_TAB);
        }
    }

}
