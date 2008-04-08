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
class ZMCategories extends ZMObject {
    public static $CATEGORY_MAPPING = null;
    public static $CATEGORY_DESCRIPTION_MAPPING = null;
    public static $PRODUCTS_TO_CATEGORIES_MAPPING = null;

    private $languageId_;
    // flat list
    private $categories_;
    private $treeFlag_;


    /**
     * Create new instance.
     *
     * @param int languageId The languageId; default is <code>null</code> for session language.
     */
    public function __construct($languageId=null) {
        parent::__construct();

        if (null == ZMCategories::$CATEGORY_MAPPING) {
            ZMCategories::$CATEGORY_MAPPING = array(
              'id' => 'column=categories_id;type=integer;primary=true',
              'languageId' => 'column=language_id;type=integer;readonly=true',
              'active' => 'column=categories_status;type=boolean',
              'parentId' => 'column=parent_id;type=integer',
              'image' => 'column=categories_image;type=string',
              'sortOrder' => 'column=sort_order;type=integer',
              'name' => 'column=categories_name;type=string;readonly=true',
              'description' => 'column=categories_description;type=string;readonly=true'
            );
            ZMCategories::$CATEGORY_MAPPING = ZMDbUtils::addCustomFields(ZMCategories::$CATEGORY_MAPPING, TABLE_CATEGORIES);
        }

        if (null == ZMCategories::$CATEGORY_DESCRIPTION_MAPPING) {
            ZMCategories::$CATEGORY_DESCRIPTION_MAPPING = array(
              // id follows categories id, so no autoincrement, or such
              'id' => 'column=categories_id;type=integer;key=true',
              'languageId' => 'column=language_id;type=integer;key=true',
              'name' => 'column=categories_name;type=string',
              'description' => 'column=categories_description;type=string'
            );
            ZMCategories::$CATEGORY_DESCRIPTION_MAPPING = ZMDbUtils::addCustomFields(ZMCategories::$CATEGORY_DESCRIPTION_MAPPING, TABLE_CATEGORIES_DESCRIPTION);
        }

        if (null == ZMCategories::$PRODUCTS_TO_CATEGORIES_MAPPING) {
            ZMCategories::$PRODUCTS_TO_CATEGORIES_MAPPING = array(
              'productId' => 'column=products_id;type=integer;key=true',
              'categoryId' => 'column=categories_id;type=integer;key=true;readonly=true'
            );
            ZMCategories::$PRODUCTS_TO_CATEGORIES_MAPPING = ZMDbUtils::addCustomFields(ZMCategories::$PRODUCTS_TO_CATEGORIES_MAPPING, TABLE_PRODUCTS_TO_CATEGORIES);
        }

        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }
        $this->languageId_ = $languageId;
        $this->categories_ = array();
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
     * @param int languageId Optional language id; default is <code>null</code>.
     * @return ZMCategory The default category (or <code>null</code>).
     */
    public function getDefaultCategoryForProductId($productId, $languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        if (!isset($this->categories_[$languageId])) {
            $this->load($languageId);
        }

        $sql = "SELECT categories_id, products_id
                FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                WHERE products_id = :productId";
        $args = array('productId' => $productId);

        $category = null;
        $result = ZMRuntime::getDatabase()->querySingle($sql, $args, ZMCategories::$PRODUCTS_TO_CATEGORIES_MAPPING);
        if (null !== $result) {
            $category = $this->getCategoryForId($result['categoryId']);
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
    public function getCategories($ids=null, $languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        if (!isset($this->categories_[$languageId])) {
            $this->categories_[$languageId] = array();
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
     * @param int languageId Optional language id; default is <code>null</code>.
     * @return array A list of all top level categories (<code>parentId == 0</code>).
     */
    public function getCategoryTree($languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        if (!isset($this->categories_[$languageId])) {
            $this->load($languageId);
            $this->buildTree($languageId);
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
    public function getCategoryForId($categoryId, $languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        if (!isset($this->categories_[$languageId])) {
            $this->load($languageId);
            $this->buildTree($languageId);
        }

        $category = $this->categories_[$languageId][$categoryId];
        return $category;
    }


    /**
     * Update an existing category.
     *
     * @param ZMCategory category The category.
     * @return Category The updated category.
     */
    function updateCategory($category) {
        ZMRuntime::getDatabase()->updateModel(TABLE_CATEGORIES, $category, ZMCategories::$CATEGORY_MAPPING);
        ZMRuntime::getDatabase()->updateModel(TABLE_CATEGORIES_DESCRIPTION, $category, ZMCategories::$CATEGORY_DESCRIPTION_MAPPING);
    }

    /**
     * Create a new category.
     *
     * @param ZMCategory category The category.
     * @return Category The updated category.
     */
    function createCategory($category) {
        $category = ZMRuntime::getDatabase()->createModel(TABLE_CATEGORIES, $category, ZMCategories::$CATEGORY_MAPPING);
        $category = ZMRuntime::getDatabase()->createModel(TABLE_CATEGORIES_DESCRIPTION, $category, ZMCategories::$CATEGORY_DESCRIPTION_MAPPING);
        return $category;
    }

    /**
     * Load all categories.
     *
     * @param int languageId Optional language id; default is <code>null</code>.
     */
    protected function load($languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        // load all straight away - should be faster to sort them later on
        $sql = "SELECT c.categories_id, c.parent_id, c.categories_image, c.sort_order, c.categories_status,
                cd.categories_name, cd.categories_description, cd.language_id
                FROM " . TABLE_CATEGORIES . " c
                LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON c.categories_id = cd.categories_id
                WHERE cd.language_id = :languageId
                ORDER BY sort_order, cd.categories_name";

        $args = array('languageId' => $languageId);
        foreach (ZMRuntime::getDatabase()->query($sql, $args, ZMCategories::$CATEGORY_MAPPING, 'Category') as $category) {
            $this->categories_[$languageId][$category->id_] = $category;
        }
    }


    /**
     * Create tree data.
     *
     * @param int languageId Optional language id; default is <code>null</code>.
     */
    protected function buildTree($languageId=null) {
        $languageId = null !== $languageId ? $languageId : $this->languageId_;

        foreach ($this->categories_[$languageId] as $id => $category) {
            if (0 != $category->parentId_) {
                $parent = $this->categories_[$languageId][$category->parentId_];
                $parent->childrenIds_[] = $id;
            }
        }
    }

}

?>
