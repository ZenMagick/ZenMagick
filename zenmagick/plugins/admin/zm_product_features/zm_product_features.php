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
        $this->setLoaderSupport('FOLDER');
        $this->setPreferredSortOrder(100);
    }

    /**
     * Create new instance.
     */
    function zm_product_features() {
        $this->__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Init this plugin.
     */
    function init() {
    global $zm_request;
        parent::init();

        // make ZMFeatures available by pre-loading it
        ZMLoader::instance()->resolve("ZMFeatures");

        if (0 < $zm_request->getProductId()) {
            // only available if product selected
            $this->addMenuItem('zm_product_features', zm_l10n_get('Product Features'), 'zm_product_features_admin', ZM_MENU_CATALOG_ADMIN);
        }
    }

}

?>
