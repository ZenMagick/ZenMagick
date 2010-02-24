<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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


define('TABLE_TAGS', ZM_DB_PREFIX . 'tags');
define('TABLE_PRODUCT_TAGS', ZM_DB_PREFIX . 'product_tags');

/**
 * Product association handler for <em>product tags</em>.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_product_tags
 * @version $Id$
 */
class ZMProductTagsProductAssociationHandler implements ZMProductAssociationHandler {

    /**
     * {@inheritDoc}
     */
    public function getType() {
       return "productTags";
    }

    /**
     * {@inheritDoc}
     */
    public function getProductAssociationsForProductId($productId, $args=null, $all=false) {
        $sql = "SELECT distinct t.name
                FROM " . TABLE_PRODUCT_TAGS . " pt, " . TABLE_TAGS . " t
                WHERE pt.product_id = :product_id AND t.tag_id = pt.tag_id";
        $args = array('product_id' => $productId);
        $tags = ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_PROUCT_TAGS, TABLE_TAGS), ZMDatabase::MODEL_RAW);

        $assoc = array();

        if (0 < count($tags)) {
            $sql = "SELECT distinct pt.product_id
                    FROM " . TABLE_PRODUCT_TAGS . " pt, " . TABLE_TAGS . " t
                    WHERE t.name in (:name) AND t.tag_id = pt.tag_id";
            $args = array('name' => $tags);
            foreach (ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_PRODUCT_TAGS, TABLE_TAGS)) as $result) {
                $assoc[] = new ZMProductAssociation($result['productId']);
            }
        }
        
        return $assoc;
    }

}

?>
