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
 * Product association.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_product_associations
 * @version $Id$
 */
class ProductAssociation extends ZMModel {
    var $id_;
    var $type_;
    var $sourceId_;
    var $targetId_;
    var $startDate_;
    var $endDate_;
    var $defaultQty_;
    var $sortOrder_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Create new instance.
     */
    function ProductAssociations() {
        $this->__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the target product.
     *
     * @return ZMProduct The associated product.
     */
    function getTargetProduct() {
    global $zm_products;

        return $zm_products->getProductForId($this->targetId_);
    }

}

?>
