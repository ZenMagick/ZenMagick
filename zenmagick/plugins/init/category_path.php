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
 * Init plugin to set up the requests 'cPath' value if missing.
 *
 * @package org.zenmagick.plugins.init
 * @author DerManoMann
 * @version $Id$
 */
class category_path extends ZMPlugin {

    /**
     * Default c'tor.
     */
    function __construct() {
        parent::__construct('Category Path', 'Set the default category path if none exists');
    }

    /**
     * Default c'tor.
     */
    function category_path() {
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

        if (0 != ($productId = $zm_request->getProductId())) {
            if (null == $zm_request->getCategoryPath()) {
                // set default based on product default category
                $product = $zm_products->getProductForId($productId);
                $defaultCategory = $product->getDefaultCategory();
                $zm_request->setCategoryPathArray($defaultCategory->getPathArray());
            }
        }
    }

}

?>
