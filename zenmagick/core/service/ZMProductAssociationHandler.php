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
 * Product association handler.
 *
 * @author DerManoMann
 * @package org.zenmagick.service
 * @version $Id$
 */
interface ZMProductAssociationHandler {

    /**
     * Get product associations for the given product, type and parameter.
     *
     * @param int productId The source product id.
     * @param int type The association type.
     * @param array args Optional parameter that might be required by the used type; default is <code>null</code> for none.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc; default is <code>false</code>.
     * @return array A list of <code>ZMProductAssociation</code> instances.
     */
    public function getProductAssociationsForProductId($productId, $type, $args=null, $all=false);

    /**
     * Get associated products for the given category.
     *
     * <p>This is mostly a convenience method to avoid having to iterate over all products in a given category yourself.</p>
     *
     * <p>This method will also take care of duplicates.</p>
     *
     * @param int categoryId The category.
     * @param int type The association type.
     * @param array args Optional parameter that might be required by the used type; default is <code>null</code> for none.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc; default is <code>false</code>.
     * @return array A list of <code>ZMProductAssociation</code> instances.
     */
    public function getProductAssociationsForCategoryId($categoryId, $type, $args=null, $all=false);

    /**
     * Get associated products for the given shopping cart.
     *
     * <p>This is mostly a convenience method to avoid having to iterate over all products in the given cart.</p>
     *
     * <p>This method will also take care of duplicates.</p>
     *
     * @param ZMShoppingCart shoppingCart The shopping cart.
     * @param int type The association type.
     * @param array args Optional parameter that might be required by the used type; default is <code>null</code> for none.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc; default is <code>false</code>.
     * @return array A list of <code>ZMProductAssociation</code> instances.
     */
    public function getProductAssociationsForShoppingCart($shoppingCart, $type, $args=null, $all=false);

}

?>
