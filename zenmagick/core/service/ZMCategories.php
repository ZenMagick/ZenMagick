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
 * Category DAO.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMCategories extends ZMService {
    var $path_;
    var $languageId_;

    // flat list
    var $categories_;
    var $treeFlag_;


    /**
     * Default c'tor.
     *
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @param array path The current category path.
     */
    function __construct($languageId=null, $path=null) {
    global $zm_request;

        parent::__construct();

        if (null === $languageId) {
            $session = $zm_request->getSession();
            $languageId = $session->getLanguageId();
        }
        $this->languageId_ = $languageId;
        $this->path_ = null !== $path ? $path : array();

        $this->categories_ = array();
        $this->treeFlag_ = false;
    }

    /**
     * Default c'tor.
     *
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @param array path The current category path.
     */
    function ZMCategories($languageId=null, $path=null) {
        $this->__construct($languageId, $path);
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
        $this->path_ = null !== $path ? $path : $this->path_;
        $this->_applyPath();    
    }

    /**
     * Apply path to categories.
     *
     * @param int languageId Optional language id; default is <code>null</code>.
     */
    function _applyPath($languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        if (!isset($this->categories_[$languageId])) {
            return;
        }

        foreach ($this->categories_[$languageId] as $id => $category) {
            $this->categories_[$languageId][$id]->active_ = false;
        }

        foreach ($this->path_ as $id) {
            if (isset($this->categories_[$languageId][$id])) {
                $this->categories_[$languageId][$id]->active_ = true;
            }
        }
    }

    /**
     * Get the default category for the given product id.
     * <p>This will return the first mapped category.</p>
     *
     * @param int productId The product id.
     * @param int languageId Optional language id; default is <code>null</code>.
     * @return ZMCategory The default category (or <code>null</code>).
     */
    function getDefaultCategoryForProductId($productId, $languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        if (!isset($this->categories_[$languageId])) {
            $this->_load($languageId);
            $this->_applyPath($languageId);
        }

        $db = ZMRuntime::getDB();
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
     * @param int languageId Optional language id; default is <code>null</code>.
     * @return array A list of <code>ZMCategory</code> instances.
     */
    function getCategories($ids=null, $languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        if (!isset($this->categories_[$languageId])) {
            $this->categories_[$languageId] = array();
            $this->_load($languageId);
            $this->_applyPath($languageId);
            if (!$this->treeFlag_) {
                $this->_buildTree($languageId);
                $this->treeFlag_ = true;
            }
        }

        if (null === $ids) {
            return $this->categories_[$languageId];
        }

        $categories = array();
        foreach ($ids as $id) {
            $categories[$id] = $this->categories_[$languageId][$id];
        }

        return $categories;
    }

    /**
     * This returns, in fact, not a real tree, but a list of all top level categories.
     *
     * @param int languageId Optional language id; default is <code>null</code>.
     * @return array A list of all top level categories (<code>parentId == 0</code>).
     */
    function getCategoryTree($languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        if (!isset($this->categories_[$languageId])) {
            $this->_load($languageId);
            $this->_applyPath($languageId);
        }

        if (!$this->treeFlag_) {
            $this->_buildTree($languageId);
            $this->treeFlag_ = true;
        }

        $tlc = array();
        foreach ($this->categories_[$languageId] as $id => $category) {
            if (0 == $category->parentId_ && 0 < $id) {
                $tlc[] = $this->categories_[$languageId][$id];
            }
        }

        return $tlc;
    }

    /**
     * Get a category for the given id.
     *
     * @param int categoryId The category id.
     * @param int languageId Optional language id; default is <code>null</code>.
     * @return ZMCategory A <code>ZMCategory</code> instance or <code>null</code>.
     */
    function getCategoryForId($categoryId, $languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        if (!isset($this->categories_[$languageId])) {
            $this->_load();
            $this->_applyPath();
            if (!$this->treeFlag_) {
                $this->_buildTree();
                $this->treeFlag_ = true;
            }
        }

        $category = $this->categories_[$languageId][$categoryId];
        return $category;
    }


    /**
     * Load all categories.
     *
     * @param int languageId Optional language id; default is <code>null</code>.
     */
    function _load($languageId=null) {
    global $zm_runtime;

        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        $db = ZMRuntime::getDB();
        // load all straight away - should be faster to sort them later on
        $query = "select c.categories_id, cd.categories_name, c.parent_id, cd.categories_description, c.categories_image, c.sort_order
                  from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd
                  on c.categories_id = cd.categories_id
                  where cd.language_id = :languageId
                  and c.categories_status = '1'
                  order by sort_order, cd.categories_name";
        $query = $db->bindVars($query, ":languageId", $languageId, "integer");
        $results = $db->Execute($query, '', true, 150);

        $this->categories_[$languageId] = array();
        while (!$results->EOF) {
            $category = $this->_newCategory($results->fields);
            $this->categories_[$languageId][$category->id_] = $category;
            $results->MoveNext();
    		}
    }


    /**
     * Create tree data.
     *
     * @param int languageId Optional language id; default is <code>null</code>.
     */
    function _buildTree($languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        foreach ($this->categories_[$languageId] as $id => $category) {
            if (0 != $category->parentId_) {
                $parent = $this->categories_[$languageId][$category->parentId_];
                array_push($parent->childrenIds_, $id);
            }
        }
    }

    /**
     * Create new <code>ZMCategory</code> instance.
     */
    function _newCategory($fields) {
        $category = $this->create("Category");
        $category->id_ = $fields['categories_id'];
        $category->parentId_ = $fields['parent_id'];
        $category->name_ = $fields['categories_name'];
        $category->description_ = $fields['categories_description'];
        $category->sortOrder_ = $fields['sort_order'];
        $category->image_ = $fields['categories_image'];
        return $category;
    }

}

?>
