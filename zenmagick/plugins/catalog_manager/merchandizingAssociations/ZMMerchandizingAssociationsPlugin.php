<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * A generic way of building associations between products.
 *
 * @package org.zenmagick.plugins.merchandizingAssociations
 * @author DerManoMann
 * @version $Id$
 */
class ZMMerchandizingAssociationsPlugin extends Plugin implements ZMRequestHandler {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Merchandizing Associations', 'Manage associations between products.');
        $this->setPreferredSortOrder(12);
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
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
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDir()."sql/install.sql")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDir()."sql/remove.sql")), $this->messages_);
    }


    /**
     * {@inheritDoc}
     */
    public function initRequest($request) {
        if (0 < $request->getProductId()) {
            // only available if product involved
            $this->addMenuItem('zmpa', zm_l10n_get('Product Associations'), 'zm_pa_admin', ZMAdminMenu::MENU_CATALOG_MANAGER_TAB);
            $this->addMenuItem('merchandizingAssociationsAdminTab', zm_l10n_get('Merchandizing Associations'), 'MerchandizingAssociationsAdminTab', ZMAdminMenu::MENU_CATALOG_MANAGER_TAB);
        }

        // attach JSON method to AJAX catalog controller
        ZMObject::attachMethod('getProductAssociationsForProductIdJSON', 'ZMAjaxCatalogController', 
            array(new ZMProductAssociationAjaxHandler(), 'getProductAssociationsForProductIdJSON'));

        // register association handler
        ZMProductAssociations::instance()->registerHandler('MechandizingProductAssociationHandler', array('xsell', 'upsell'));
    }

}
