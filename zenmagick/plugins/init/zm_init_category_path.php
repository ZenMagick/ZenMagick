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
 * Init plugin to set up the requests 'cPath' value if missing.
 *
 * @package org.zenmagick.plugins.init
 * @author DerManoMann
 * @version $Id$
 */
class zm_init_category_path extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Category Path', 'Set the default category path if none exists');
        $this->setScope(ZMPlugin::SCOPE_STORE);
        $this->setPreferredSortOrder(40);
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

        if (0 != ($productId = ZMRequest::getProductId())) {
            if (null == ZMRequest::getCategoryPath()) {
                // set default based on product default category
                if (null != ($product = ZMProducts::instance()->getProductForId($productId))) {
                    $defaultCategory = $product->getDefaultCategory();
                    if (null != $defaultCategory) {
                        ZMRequest::setCategoryPathArray($defaultCategory->getPathArray());
                    }
                }
            }
        }
    }

}

?>
