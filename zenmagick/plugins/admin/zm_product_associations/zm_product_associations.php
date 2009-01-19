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
 * A more generic way of doing cross sell, up sell or any other type of product
 * association.
 *
 * @package org.zenmagick.plugins.zm_product_associations
 * @author DerManoMann
 * @version $Id$
 */
class zm_product_associations extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Product Associations', 'ZenMagick product associations');
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
     * Install this plugin.
     */
    function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDir()."sql/associations_install.sql")), $this->messages_);
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDir()."sql/associations_remove.sql")), $this->messages_);
    }


    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        ZMLoader::resolve("ProductAssociations");
        $associations = ZMProductAssociations::instance();
        if ($associations && $associations->isInstalled()) {
            $associations->prepareAssociationTypes();
        }

        if ($associations && 0 < ZMRequest::getProductId()) {
            // only available if product involved
            $this->addMenuItem('zmpa', zm_l10n_get('Product Associations'), 'zm_pa_admin', ZM_MENU_CATALOG_ADMIN);
        }

        ZMObject::attachMethod('getProductAssociationsForProductIdJSON', 'ZMAjaxCatalogController', 
            array(ZMLoader::make('ZMProductAssociationAjaxHandler'), 'getProductAssociationsForProductIdJSON'));
    }


}

?>
