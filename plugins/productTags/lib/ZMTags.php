<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

define('TABLE_TAGS', DB_PREFIX . 'tags');
define('TABLE_PRODUCT_TAGS', DB_PREFIX . 'product_tags');

/**
 * <em>Tags</em>.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.productTags
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
        return Runtime::getContainer()->get('tagService');
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
        $sql = "SELECT distinct pt.product_tag_id, t.name
                FROM " . TABLE_PRODUCT_TAGS . " pt, " . TABLE_TAGS . " t
                WHERE pt.product_id = :product_id AND t.tag_id = pt.tag_id
                AND t.language_id = :language_id
                ORDER BY name";
        $args = array('product_id' => $productId, 'language_id' => $languageId);
        foreach (ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_PRODUCT_TAGS, TABLE_TAGS)) as $result) {
            $tags[$result['product_tag_id']] = $result['name'];
        }

        return $tags;
    }

    /**
     * Get all product (ids) for the given tag list.
     *
     * @param array tags List of tags.
     * @param int languageId The language id.
     * @return array List of product ids.
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

    /**
     * Get all tags.
     *
     * @param int languageId The language id.
     * @return array List of (unique) <code>string</code> tags.
     */
    public function getAllTags($languageId) {
        $tags = array();
        $sql = "SELECT distinct t.tag_id, t.name
                FROM " . TABLE_TAGS . " t
                WHERE t.language_id = :language_id
                ORDER BY name";
        $args = array('language_id' => $languageId);
        foreach (ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_PRODUCT_TAGS, TABLE_TAGS)) as $result) {
            $tags[$result['tag_id']] = $result['name'];
        }

        return $tags;
    }

    /**
     * Set tags for a product.
     *
     * @param int productId The product id.
     * @param int languageId The language id.
     * @param array tags List of tags.
     */
    public function setTagsForProductId($productId, $languageId, $tags) {
        // add all missing tags for this language
        $allTags = array_flip($this->getAllTags($languageId));
        foreach ($tags as $tag) {
            if (!array_key_exists($tag, $allTags)) {
                // add new tag for language
                $sql = "INSERT INTO " . TABLE_TAGS . " (name, language_id) VALUES (:name, :language_id)";
                ZMRuntime::getDatabase()->update($sql, array('name' => $tag, 'language_id' => $languageId), TABLE_TAGS);
            }
        }

        // delete existing tags for product
        $tagIds = array_keys($this->getTagsForProductId($productId, $languageId));
        if (0 < count($tagIds)) {
            $sql = "DELETE FROM " . TABLE_PRODUCT_TAGS . "
                    WHERE product_tag_id in (:product_tag_id)";
            ZMRuntime::getDatabase()->update($sql, array('product_tag_id' => $tagIds), TABLE_PRODUCT_TAGS);
        }

        // reload to get all current tag_ids
        $allTags = array_flip($this->getAllTags($languageId));

        // (re-)add all tags
        foreach ($tags as $tag) {
            $tagId = $allTags[$tag];
            $sql = "INSERT INTO " . TABLE_PRODUCT_TAGS . " (product_id, tag_id) VALUES (:product_id, :tag_id)";
            ZMRuntime::getDatabase()->update($sql, array('product_id' => $productId, 'tag_id' => $tagId), TABLE_PRODUCT_TAGS);
        }
    }

    /**
     * Purge unused tags.
     */
    public function cleanupTags() {
        $sql = "DELETE FROM " . TABLE_TAGS . "
                WHERE NOT tag_id in (SELECT DISTINCT tag_id from " . TABLE_PRODUCT_TAGS . ")";
        ZMRuntime::getDatabase()->update($sql);
    }

    /**
     * Get tag stats.
     *
     * @param int languageId The language id.
     * @return array A map of tag =&gt; number-ofproducts.
     */
    public function getStats($languageId) {
        $sql = "SELECT COUNT(*) as tag_id, t.name
                FROM " . TABLE_PRODUCT_TAGS . " pt, " . TABLE_TAGS . " t
                WHERE t.tag_id = pt.tag_id AND t.language_id = :language_id
                GROUP BY name";
        $stats = array();
        foreach (ZMRuntime::getDatabase()->query($sql, array('language_id' => $languageId), array(TABLE_PRODUCT_TAGS, TABLE_TAGS)) as $result) {
            //XXX: doh!
            $stats[$result['name']] = $result['tag_id'];
        }

        return $stats;
    }

}
