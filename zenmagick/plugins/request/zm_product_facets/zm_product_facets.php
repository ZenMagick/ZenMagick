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
 * Product facets.
 *
 * @package org.zenmagick.plugins.zm_product_facets
 * @author mano
 * @version $Id$
 */
class zm_product_facets extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Product Facets', 'Facets view on products', '${plugin.version}');
        $this->setLoaderSupport('ALL');
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

        // create as this will also init the underlying cache
        ZMLoader::instance()->resolve("ProductFacets");
ZMLoader::instance()->resolve("Facet");

        // load default facets
        ZMProductFacets::instance()->addFacetBuilder('manufacturers', 'zm_build_manufacturer_facet');
        ZMProductFacets::instance()->addFacetBuilder('categories', 'zm_build_category_facet');
        ZMProductFacets::instance()->addFacetBuilder('prices', 'zm_build_pricerange_facet');
    }

}

?>
