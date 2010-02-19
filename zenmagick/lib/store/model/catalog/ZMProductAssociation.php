<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * A product association base class.
 *
 * <p><code>ZMProductAssociationHandler</code> implementations are free to subclass.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.model.catalog
 * @version $Id$
 */
class ZMProductAssociation extends ZMObject {
    private $productId_;


    /**
     * Create new instance.
     *
     * @param int productId Optional product id; default is <code>null</code>.
     */
    function __construct($productId=null) {
        parent::__construct();
        $this->productId_ = $productId;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the (source) product id of this association.
     *
     * @return int A product id.
     */
    public function getProductId() {
        return $this->productId_;
    }

    /**
     * Set the (source) product id of this association.
     *
     * @param int productId A product id.
     */
    public function setProductId($productId) {
        $this->productId_ = $productId;
    }

}
