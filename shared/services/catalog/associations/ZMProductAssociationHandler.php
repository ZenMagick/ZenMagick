<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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


/**
 * Product association handler.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.catalog.associations
 */
interface ZMProductAssociationHandler {

    /**
     * Get the type this handler handles.
     *
     * @return string The type identifier.
     */
    public function getType();
	
    /**
     * Get product associations for the given product, type and parameter.
     *
     * @param int productId The source product id.
     * @param array args Optional parameter that might be required by the used type; default is an empty array.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc; default is <code>false</code>.
     * @return array A list of <code>ZMProductAssociation</code> instances.
     */
    public function getProductAssociationsForProductId($productId, $args=array(), $all=false);

}
