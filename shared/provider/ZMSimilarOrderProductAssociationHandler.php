<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\apps\store\model\catalog\ProductAssociation;
use zenmagick\apps\store\services\catalog\ProductAssociationHandler;

/**
 * Product association handler for <em>also purchased</em> products.
 *
 * <p>Supports <em>limit</em> parameter in the <code>$args</code> map.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.provider
 */
class ZMSimilarOrderProductAssociationHandler implements ProductAssociationHandler {

    /**
     * {@inheritDoc}
     */
    public function getType() {
       return "similarOrder";
    }

    /**
     * {@inheritDoc}
     */
    public function getProductAssociationsForProductId($productId, $args=array()) {
        $limit = 6;
        if (is_array($args) && array_key_exists('limit', $args)) {
            $limit = (int)$args['limit'];
        }
        $sql = "SELECT p.products_id
                FROM " . TABLE_ORDERS_PRODUCTS . " opa, " . TABLE_ORDERS_PRODUCTS . " opb, " . TABLE_ORDERS . " o, " . TABLE_PRODUCTS ." p
                WHERE opa.products_id = :productId AND opa.orders_id = opb.orders_id
                    AND opb.products_id != :productId and opb.products_id = p.products_id
                    AND opb.orders_id = o.orders_id and p.products_status = 1
                    GROUP BY p.products_id
                    ORDER BY o.date_purchased DESC
                    LIMIT ".$limit;
        $args = array('productId' => $productId);

        $assoc = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, $args, TABLE_PRODUCTS) as $result) {
            $assoc[] = new ProductAssociation($result['productId']);
        }

        return $assoc;
    }

}
