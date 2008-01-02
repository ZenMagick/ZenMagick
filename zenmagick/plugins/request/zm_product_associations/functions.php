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
 *
 * $Id$
 */
?>
<?php

    /**
     * Product associations admin page.
     *
     * @package org.zenmagick.plugins.zm_product_associations
     * @return ZMPluginPage A plugin page or <code>null</code>.
     */
    function zm_pa_admin() {
    global $zm_categories;

        $tree = $zm_categories->getCategoryTree();
        $catTree = zm_catalog_tree($tree, '&fkt=zm_pa_admin', false);
        $products = zm_product_resultlist();
        $pp = new ZMPluginPage('zm_pa_admin', zm_l10n_get('Product Associations'), $catTree.$products);
        // so what??
        $header = '<link rel="stylesheet" type="text/css" href="includes/jquery/jquery.treeview.css">';
        $pp->setHeader($header);
        return $pp;
    }

?>
