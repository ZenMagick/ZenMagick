<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Category DAO.
 *
 * @author mano
 * @package net.radebatz.zenmagick.service
 * @version $Id$
 */
class ZMCategories extends ZMService {
    var $path_;
    var $type_;
    var $languageId_;

    // flat list
    var $categories_;


    /**
     * Default c'tor.
     *
     * @param array path The current category path.
     * @param string type The category type (not supported yet).
     * @param int languageId The languageId.
     */
    function ZMCategories($path=null, $type=null, $languageId=null) {
    global $zm_runtime;

        parent::__construct();

        $this->type_ = $type;
        $this->languageId_ = null !== $languageId ? $languageId : $zm_runtime->getLanguageId();
        $this->categories_ = null;
        $this->_load();
        $this->_buildTree();
        $this->setPath($path);
    }

    /**
     * Default c'tor.
     *
     * @param array path The current category path.
     * @param string type The category type (not supported yet).
     * @param int languageId The languageId.
     */
    function __construct($path=null, $type=null, $languageId=null) {
        $this->ZMCategories($path, $type, $languageId);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the path.
     *
     * @param array path The current path.
     */
    function setPath($path) { 
        $this->path_ = (null !== $path ? $path : array());
        
        // reset
        foreach ($this->categories_ as $id => $category) {
            $this->categories_[$id]->active_ = false;
        }

        foreach ($this->path_ as $id) {
            if (isset($this->categories_[$id])) {
                $this->categories_[$id]->active_ = true;
            }
        }
    }

    /**
     * Get the default category for the given product id.
     * <p>This will return the first mapped category.</p>
     *
     * @param int productId The product id.
     * @return ZMCategory The default category (or <code>null</code>).
     */
    function getDefaultCategoryForProductId($productId) {
        $db = $this->getDB();
        $sql = "SELECT categories_id
                FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                WHERE products_id = :productId";
        $sql = $db->bindVars($sql, ":productId", $productId, 'integer');
        $results = $db->Execute($sql);

        $category = null;
        if (!$results->EOF) {
            $category = $this->getCategoryForId($results->fields['categories_id']);
        }
        
        return $category;
    }

    /**
     * Get all categories.
     *
     * @param array ids Optional list of category ids.
     * @return array A list of <code>ZMCategory</code> instances.
     */
    function getCategories($ids=null) {
        if (null === $ids) {
            return $this->categories_;
        }

        $categories = array();
        foreach ($ids as $id) {
            $categories[$id] = $this->categories_[$id];
        }

        return $categories;
    }

    /**
     * This returns, in fact, not a real tree, but a list of all top level categories.
     *
     * @return array A list of all top level categories (<code>parentId == 0</code>).
     */
    function getCategoryTree() {
        $tlc = array();
        foreach ($this->categories_ as $id => $category) {
            if (0 == $category->parentId_ && 0 < $id) {
                array_push($tlc, $this->categories_[$id]);
            }
        }

        return $tlc;
    }

    /**
     * Get a category for the given id.
     *
     * @param int categoryId The category id.
     * @return ZMCategory A <code>ZMCategory</code> instance or <code>null</code>.
     */
    function getCategoryForId($categoryId) {
        $category = $this->categories_[$categoryId];
        return $category;
    }


    /**
     * Load all categories.
     */
    function _load() {
    global $zm_runtime;

        $db = $this->getDB();
        // load all straight away - should be faster to sort them later on
        $query = "select c.categories_id, cd.categories_name, c.parent_id, cd.categories_description, c.categories_image, c.sort_order
                  from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd
                  on c.categories_id = cd.categories_id
                  where cd.language_id = :languageId
                  and c.categories_status = '1'
                  order by sort_order, cd.categories_name";
        $query = $db->bindVars($query, ":languageId", $this->languageId_, "integer");
        $results = $db->Execute($query, '', true, 150);

        $this->categories_ = array();
        while (!$results->EOF) {
            $category = $this->_newCategory($results->fields);
            $this->categories_[$category->id_] = $category;
            $results->MoveNext();
		}
    }


    /**
     * Create tree data.
     */
    function _buildTree() {
        foreach ($this->categories_ as $id => $category) {
            if (0 != $category->parentId_) {
                $parent = $this->categories_[$category->parentId_];
                array_push($parent->childrenIds_, $id);
            }
        }
    }

    /**
     * Create new <code>ZMCategory</code> instance.
     */
    function &_newCategory($fields) {
        $category = $this->create("Category", $fields['categories_id'], $fields['parent_id'], $fields['categories_name'], false);
        $category->description_ = $fields['categories_description'];
        $category->sortOrder_ = $fields['sort_order'];
        $category->image_ = $fields['categories_image'];
        return $category;
    }

}

?>
