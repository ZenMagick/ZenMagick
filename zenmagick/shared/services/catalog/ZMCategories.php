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


/**
 * Category DAO.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services.catalog
 * @version $Id$
 */
class ZMCategories extends ZMObject {
    /** 
     * Flat list of <code>ZMCategory</code> instances.
     *
     * <p>This gets loaded on demand, so subclasses have to ensure this is populated before
     * using it.</p>
     */
    protected $categories_;
    private $rootCategories_;
    private $productTypeIdMap_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->categories_ = array();
        $this->rootCategories_ = array();
        $this->productTypeIdMap_ = null;
    }

    /**
     * Destruct instance.
     */
    public function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Categories');
    }


    /**
     * Get the default category for the given product id.
     * <p>This will return the first mapped category.</p>
     *
     * @param int productId The product id.
     * @param int languageId Language id.
     * @return ZMCategory The default category (or <code>null</code>).
     */
    public function getDefaultCategoryForProductId($productId, $languageId) {
        $sql = "SELECT categories_id, products_id
                FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                WHERE products_id = :productId";
        $args = array('productId' => $productId);
        $category = null;
        $result = ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_PRODUCTS_TO_CATEGORIES);
        if (null !== $result) {
            $category = $this->getCategoryForId($result['categoryId'], $languageId);
        }

        return $category;
    }

    /**
     * Get all root categories.
     *
     * @param int languageId Language id.
     * @return array A list of <code>ZMCategory</code> instances.
     */
    public function getRootCategories($languageId) {
        if (!isset($this->rootCategories_[$languageId])) {
            $this->rootCategories_[$languageId] = array();
            $sql = "SELECT c.*, cd.*
                    FROM " . TABLE_CATEGORIES . " c
                      LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON c.categories_id = cd.categories_id
                      WHERE cd.language_id = :languageId
                        AND c.parent_id = 0
                      ORDER BY c.parent_id, sort_order, cd.categories_name";
            $args = array('languageId' => $languageId);
            foreach (Runtime::getDatabase()->query($sql, $args, array(TABLE_CATEGORIES, TABLE_CATEGORIES_DESCRIPTION), 'Category') as $category) {
                $this->rootCategories_[$languageId][$category->getId()] = $category;
            }
            
        }

        return $this->rootCategories_[$languageId];
    }

    /**
     * Get all categories.
     *
     * @param array ids Optional list of category ids.
     * @param int languageId Language id.
     * @return array A list of <code>ZMCategory</code> instances.
     */
    public function getCategories($ids=null, $languageId) {
        if (!isset($this->categories_[$languageId])) {
            $this->load($languageId);
            $this->buildTree($languageId);
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
     * @param int languageId Language id.
     * @return array A list of all top level categories (<code>parentId == 0</code>).
     */
    public function getCategoryTree($languageId) {
        if (!isset($this->categories_[$languageId])) {
            $this->load($languageId);
            $this->buildTree($languageId);
        }

        $tlc = array();
        if (array_key_exists($languageId, $this->categories_)) {
            foreach ($this->categories_[$languageId] as $id => $category) {
                if (0 == $category->getParentId() && 0 < $id) {
                    $tlc[] = $this->categories_[$languageId][$id];
                }
            }
        }

        return $tlc;
    }

    /**
     * Get a category for the given id.
     *
     * @param int categoryId The category id.
     * @param int languageId Language id.
     * @return ZMCategory A <code>ZMCategory</code> instance or <code>null</code>.
     */
    public function getCategoryForId($categoryId, $languageId) {
        if (!isset($this->categories_[$languageId])) {
            $this->load($languageId);
            $this->buildTree($languageId);
        }

        $category = null;
        if (isset($this->categories_[$languageId]) && isset($this->categories_[$languageId][$categoryId])) {
            $category = $this->categories_[$languageId][$categoryId];
        }
        return $category;
    }

    /**
     * Update an existing category.
     *
     * @param ZMCategory category The category.
     * @return Category The updated category.
     */
    public function updateCategory($category) {
        ZMRuntime::getDatabase()->updateModel(TABLE_CATEGORIES, $category);
        ZMRuntime::getDatabase()->updateModel(TABLE_CATEGORIES_DESCRIPTION, $category);
    }

    /**
     * Create a new category.
     *
     * @param ZMCategory category The category.
     * @return Category The updated category.
     */
    public function createCategory($category) {
        $category = ZMRuntime::getDatabase()->createModel(TABLE_CATEGORIES, $category);
        $category = ZMRuntime::getDatabase()->createModel(TABLE_CATEGORIES_DESCRIPTION, $category);
        return $category;
    }

    /**
     * Get the allowed product types (ids) for the given category id.
     *
     * @return array List of allowed product type ids; an empty list means no restrictions.
     */
    public function getProductTypeIds($categoryId) {
        if (null === $this->productTypeIdMap_) {
            $this->productTypeIdMap_ = array();
            $sql = "SELECT * FROM " . TABLE_PRODUCT_TYPES_TO_CATEGORY ."
                    ORDER BY category_id";
            foreach (Runtime::getDatabase()->query($sql, array(), TABLE_PRODUCT_TYPES_TO_CATEGORY) as $result) {
                if (!array_key_exists($result['categoryId'],  $this->productTypeIdMap_)) {
                    $this->productTypeIdMap_[$result['categoryId']] = array();
                }
                $this->productTypeIdMap_[$result['categoryId']][] = $result['productTypeId'];
            }
        }

        return array_key_exists($categoryId, $this->productTypeIdMap_) ? $this->productTypeIdMap_[$categoryId] : array();
    }

    /**
     * Load all categories.
     *
     * @param int languageId Language id; default is <code>null</code>.
     */
    protected function load($languageId) {
        if (!isset($this->categories_[$languageId])) {
            $this->categories_[$languageId] = array();
        }

        // load all straight away - should be faster to sort them later on
        $args = array('languageId' => $languageId);
        $sql = "SELECT c.*, cd.*
                FROM " . TABLE_CATEGORIES . " c
                  LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON c.categories_id = cd.categories_id
                  WHERE cd.language_id = :languageId";
        $sql .= " ORDER BY c.parent_id, sort_order, cd.categories_name";
        foreach (Runtime::getDatabase()->query($sql, $args, array(TABLE_CATEGORIES, TABLE_CATEGORIES_DESCRIPTION), 'Category') as $category) {
            $this->categories_[$languageId][$category->getId()] = $category;
        }
    }

    /**
     * Create tree data.
     *
     * @param int languageId Language id; default is <code>null</code>.
     */
    protected function buildTree($languageId) {
        if (array_key_exists($languageId, $this->categories_)) {
            foreach ($this->categories_[$languageId] as $id => $category) {
                if (0 != $category->getParentId()) {
                    $parent = $this->categories_[$languageId][$category->getParentId()];
                    $parent->addChild($id);
                }
            }
        }
    }

    /**
     * Get meta tag details for the given id and language.
     *
     * @param int categoryId The category id.
     * @param int languageId Language id.
     * @return ZMMetaTagDetails The details or <code>null</code>.
     */
    public function getMetaTagDetailsForId($categoryId, $languageId) {
        $sql = "SELECT * from " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . "
                WHERE categories_id = :categoryId
                  AND language_id = :languageId";
        $args = array('categoryId' => $categoryId, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_METATAGS_CATEGORIES_DESCRIPTION, 'MetaTagDetails');
    }

}
