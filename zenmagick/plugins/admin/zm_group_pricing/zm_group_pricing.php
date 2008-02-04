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
 */
?>
<?php


/**
 * Plugin adding group based pricing.
 *
 * @package org.zenmagick.plugins.zm_group_pricing
 * @author DerManoMann
 * @version $Id$
 */
class zm_group_pricing extends ZMPlugin {

    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct('Group Pricing', 'Group Pricing', '${zenmagick.version}');
        $this->setLoaderSupport('FOLDER');
        $this->setPreferredSortOrder(15);
    }

    /**
     * Default c'tor.
     */
    function zm_group_pricing() {
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
    global $zm_request;

        parent::init();

        if (0 < $zm_request->getProductId()) {
            // only available if product involved
            $this->addMenuItem('zm_group_pricing_admin', zm_l10n_get('Group Pricing'), 'zm_group_pricing_admin', ZM_MENU_CATALOG_ADMIN);
        }
    }

}

?>
