<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * A quick way to edit certain product properties per category.
 *
 * @package org.zenmagick.plugins.zm_quick_edit
 * @author DerManoMann
 * @version $Id$
 */
class zm_quick_edit extends Plugin implements ZMRequestHandler {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Quick Edit', 'Quickly edit product properties');
        $this->setPreferredSortOrder(35);
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setScope(Plugin::SCOPE_ADMIN);
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
        if (0 < $request->getCategoryId() && 0 == $request->getProductId()) {
            $count = count(ZMProducts::instance()->getProductIdsForCategoryId($request->getCategoryId(), false));
            if (0 < $count) {
                // only available if category involved
                $this->addMenuItem('zm_quick_edit', zm_l10n_get('Quick Edit'), 'zm_quick_edit_admin', ZMAdminMenu::MENU_CATALOG_MANAGER_TAB);
            }
        }
    }

}

?>
