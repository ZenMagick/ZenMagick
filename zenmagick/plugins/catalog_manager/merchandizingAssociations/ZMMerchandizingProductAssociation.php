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
 * Product association.
 *
 * <p>Generic properties:</p>
 * <ul>
 *  <li>id</li>
 *  <li>type</li>
 *  <li>sourceId</li>
 *  <li>targetId</li>
 *  <li>startDate</li>
 *  <li>endDate</li>
 *  <li>defaultQty</li>
 *  <li>sortOrder</li>
 * </ul>
 * 
 * @author DerManoMann
 * @package org.zenmagick.plugins.merchandizingAssociations
 * @version $Id$
 */
class ZMMerchandizingProductAssociation extends ZMProductAssociation {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the target product id.
     *
     * <p>This will also set the <code>productId</code> property.</p>
     *
     * @param int productId The target product id.
     */
    public function setTargetId($productId) {
        $this->set('targetId', $productId);
        $this->setProductId($productId);
    }

    /**
     * Get the target product.
     *
     * @return ZMProduct The associated product.
     */
    public function getTargetProduct() {
        return ZMProducts::instance()->getProductForId($this->get('targetId'));
    }

}
