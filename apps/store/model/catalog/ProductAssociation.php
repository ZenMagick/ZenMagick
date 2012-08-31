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
namespace ZenMagick\apps\store\model\catalog;

use ZenMagick\Base\ZMObject;

/**
 * A product association base class.
 *
 * <p><code>ProductAssociationHandler</code> implementations are free to subclass.</p>
 *
 * @author DerManoMann
 */
class ProductAssociation extends ZMObject {
    private $productId;


    /**
     * Create new instance.
     *
     * @param int productId Optional product id; default is <code>null</code>.
     */
    public function __construct($productId=null) {
        parent::__construct();
        $this->productId = $productId;
    }


    /**
     * Get the (source) product id of this association.
     *
     * @return int A product id.
     */
    public function getProductId() {
        return $this->productId;
    }

    /**
     * Set the (source) product id of this association.
     *
     * @param int productId A product id.
     */
    public function setProductId($productId) {
        $this->productId = $productId;
    }

}
