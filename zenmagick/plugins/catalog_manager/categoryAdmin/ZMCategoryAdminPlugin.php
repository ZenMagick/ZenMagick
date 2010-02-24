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


/**
 * Plugin adding category maintenance to the catalog manager.
 *
 * @package org.zenmagick.plugins.categoryAdmin
 * @author DerManoMann
 * @version $Id$
 */
class ZMCategoryAdminPlugin extends Plugin implements ZMRequestHandler {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Category Maintenance', 'Category Management', '${zenmagick.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setPreferredSortOrder(5);
        $this->setContext(Plugin::CONTEXT_ADMIN);
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
    public function initRequest($request) {
        $categoryId = $request->getCategoryId();
        if (0 < $categoryId) {
            // only available if category involved
            $this->addMenuItem('categoryAdminTab', zm_l10n_get('Category'), 'CategoryAdminTab', ZMAdminMenu::MENU_CATALOG_MANAGER_TAB);
            if (false && !ZMSettings::get('admin.isShowCatalogTreeProducts')) {
                if (0 < count(ZMProducts::instance()->getProductIdsForCategoryId($categoryId, false))) {
                    $this->addMenuItem('zm_category_admin_list', zm_l10n_get('Products'), 'zm_category_admin_list', ZMAdminMenu::MENU_CATALOG_MANAGER_TAB);
                }
            }
        }
    }

}

?>
