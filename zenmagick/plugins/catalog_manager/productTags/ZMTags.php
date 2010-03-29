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


define('TABLE_TAGS', ZM_DB_PREFIX . 'tags');
define('TABLE_PRODUCT_TAGS', ZM_DB_PREFIX . 'product_tags');

/**
 * <em>Tags</em>.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.productTags
 * @version $Id$
 */
class ZMTags extends ZMObject {

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
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Tags');
    }


    /**
     * Get all tags for the given product id.
     *
     * @param int productId The product id.
     * @param int languageId The language id.
     * @return array List of (unique) <code>string</code> tags.
     */
    public function getTagsForProductId($productId, $languageId) {
        $tags = array();
        $sql = "SELECT distinct t.name
                FROM " . TABLE_PRODUCT_TAGS . " pt, " . TABLE_TAGS . " t
                WHERE pt.product_id = :product_id AND t.tag_id = pt.tag_id
                AND t.language_id = :language_id";
        $args = array('product_id' => $productId, 'language_id' => $languageId);
        foreach (ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_PRODUCT_TAGS, TABLE_TAGS)) as $result) {
            $tags[] = $result['name'];
        }

        return $tags;
    }

    /**
     * Get all product (ids) for the given tag list.
     *
     * @param array tags List of tags.
     * @param int languageId The language id.
     */
    public function getProductIdsForTags($tags, $languageId) {
        $ids = array();
        $sql = "SELECT distinct pt.product_id
                FROM " . TABLE_PRODUCT_TAGS . " pt, " . TABLE_TAGS . " t
                WHERE t.name in (:name) AND t.tag_id = pt.tag_id
                AND t.language_id = :language_id";
        $args = array('name' => $tags, 'language_id' => $languageId);
        foreach (ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_PRODUCT_TAGS, TABLE_TAGS)) as $result) {
            $ids[] = $result['product_id'];
        }
        
        return $ids;
    }

}
