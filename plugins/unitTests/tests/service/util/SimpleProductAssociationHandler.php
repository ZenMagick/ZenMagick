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

use ZenMagick\StoreBundle\Model\Catalog\ProductAssociation;
use ZenMagick\StoreBundle\Services\Catalog\ProductAssociationHandler;
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Simple <code>ZMProductAssociationHandler</code> implementation.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class SimpleProductAssociationHandler extends TestCase implements ProductAssociationHandler {

    /**
     * {@inheritDoc}
     */
    public function getType() {
        return "simple";
    }

    /**
     * Return some hardcoded test data.
     *
     * @param int productId The source product id.
     * @param array args Optional parameter that might be required by the used type; default is <code>null</code> for none.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc; default is <code>false</code>.
     * @return array A list of <code>ZMProductAssociation</code> instances.
     */
    public function getProductAssociationsForProductId($productId, $args=null, $all=false) {
        $assoc = array();
        if (13 == $productId) {
            $assoc[] = new ProductAssociation(13);
        }

        return $assoc;

    }

}

