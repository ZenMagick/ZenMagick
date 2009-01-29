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
 * Default catalog manager plugin.
 *
 * @package org.zenmagick.plugins.zm_catalog_default
 * @author DerManoMann
 * @version $Id$
 */
class zm_catalog_default extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Catalog Manager', 'Default Catalog Manager page', '${zenmagick.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setPreferredSortOrder(0);
        $this->setScope(ZMPlugin::SCOPE_ADMIN);
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
        parent::init();

        //if (0 == ZMRequest::getProductId() && 0 == ZMRequest::getCategoryId()) {
            $this->addMenuItem('zm_catalog_default_admin', zm_l10n_get('Catalog Manager'), 'zm_catalog_default_admin', ZMAdminMenu::MENU_CATALOG_MANAGER_TAB);
        //}
    }

}

?>
