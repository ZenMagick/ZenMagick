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
 * Plugin adding category maintenance.
 *
 * @package org.zenmagick.plugins.zm_category_admin
 * @author DerManoMann
 * @version $Id$
 */
class zm_category_admin extends ZMPlugin {

    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct('Category Maintenance', 'Category Management', '${zenmagick.version}');
        $this->setLoaderSupport('FOLDER');
        $this->setPreferredSortOrder(5);
    }

    /**
     * Default c'tor.
     */
    function zm_category_admin() {
        $this->__construct();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Init this plugin.
     */
    function init() {
    global $zm_request, $zm_products;

        parent::init();

        $categoryId = $zm_request->getCategoryId();
        if (0 < $categoryId) {
            // only available if category involved
            $this->addMenuItem('zm_category_admin', zm_l10n_get('Category'), 'zm_category_admin', ZM_MENU_CATALOG_ADMIN);
            if (!zm_setting('admin.isShowCatalogTreeProducts')) {
                if (0 < count($zm_products->getProductIdsForCategoryId($categoryId, false))) {
                    $this->addMenuItem('zm_category_admin_list', zm_l10n_get('Products'), 'zm_category_admin_list', ZM_MENU_CATALOG_ADMIN);
                }
            }
        }
    }

}

?>
