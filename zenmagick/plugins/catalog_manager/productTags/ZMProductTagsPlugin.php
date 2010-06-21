<?php
/*
 * ZenMagick - Smart e-commerce
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
 * Allow to add tags to products.
 *
 * @package org.zenmagick.plugins.productTags
 * @author DerManoMann
 * @version $Id$
 */
class ZMProductTagsPlugin extends Plugin implements ZMRequestHandler {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Product Tags', 'Tag all your products', '${zenmagick.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setPreferredSortOrder(22);
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
        ZMProductAssociations::instance()->registerHandler('ProductTagsProductAssociationHandler');
        if (0 < $request->getProductId()) {
            // only available if product involved
            $this->addMenuItem('productTagsTab', _zm('Product Tags'), 'ProductTagsTab', ZMAdminMenu::MENU_CATALOG_MANAGER_TAB);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/install.sql")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/uninstall.sql")), $this->messages_);
    }

}
