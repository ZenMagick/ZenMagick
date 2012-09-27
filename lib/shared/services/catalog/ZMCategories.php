<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Base\ZMObject;

/**
 * Category DAO.
 *
 * <p>The cache implementation used can be configured via the setting '<em>apps.store.categories.cache</em>'.
 * Default is <code>ZenMagick\Base\Cache\Cache::TRANSIENT</code>.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.catalog
 */
class ZMCategories extends ZMObject {
    /**
     * Flat list of <code>ZenMagick\StoreBundle\Entity\Catalog\Category</code> instances.
     *
     * <p>This gets loaded on demand, so subclasses have to ensure this is populated before
     * using it.</p>
     */
    private $cache_;
    private $categories_;
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
     * Set the cache.
     *
     * @param ZenMagick\Base\Cache\Cache cache The cache.
     */
    public function setCache($cache) {
        $this->cache_ = $cache;
    }

    /**
     * Get the cache.
     *
     * @return ZenMagick\Base\Cache\Cache The cache.
     */
    public function getCache() {
        return $this->cache_;
    }

    /**
     * Get the default category for the given product id.
     * <p>This will return the first mapped category.</p>
     *
     * @param int productId The product id.
     * @param int languageId Language id.
     * @return ZenMagick\StoreBundle\Entity\Catalog\Category The default category (or <code>null</code>).
     */
    public function getDefaultCategoryForProductId($productId, $languageId) {
        $sql = "SELECT categories_id, products_id
                FROM %table.products_to_categories%
                WHERE products_id = :productId";
        $args = array('productId' => $productId);
        $category = null;
        $result = ZMRuntime::getDatabase()->querySingle($sql, $args, 'products_to_categories');
        if (null !== $result) {
            $category = $this->getCategoryForId($result['categoryId'], $languageId);
        }

        return $category;
    }

    /**
     * Get all root categories.
     *
     * <p>This method got added for performance reasons and using the default parameters will only load
     * the categories itself, but no child categories.</p>
     *
     * @param int languageId Language id.
     * @param boolean includeChildren Optional flag to also populate child categories; default is <code>false</code>.
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Category</code> instances.
     */
    public function getRootCategories($languageId, $includeChildren=false) {
        $rootCategoriesKey = $languageId.'-'.($includeChildren ? 'true' : 'false');
        if (array_key_exists($rootCategoriesKey, $this->rootCategories_)) {
            return $this->rootCategories_[$rootCategoriesKey];
        }

        // first check cache
        if (false !== ($rootCategories = $this->cache_->lookup(Toolbox::hash('categories', 'rootCategories', $rootCategoriesKey)))) {
            $this->rootCategories_[$rootCategoriesKey] = $rootCategories;
            return $rootCategories;
        }

        $rootCategories = array();
        $sql = "SELECT c.*, cd.*
                FROM %table.categories% c
                  LEFT JOIN %table.categories_description% cd ON c.categories_id = cd.categories_id
                  WHERE cd.language_id = :languageId
                    AND c.parent_id = 0
                  ORDER BY c.parent_id, c.sort_order, cd.categories_name";
        $args = array('languageId' => $languageId);
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, $args, array('categories', 'categories_description'), 'ZenMagick\StoreBundle\Entity\Catalog\Category') as $category) {
            $rootCategories[$category->getId()] = $category;
        }

        if ($includeChildren && !empty($rootCategories)) {
            $sql = "SELECT c.*, cd.*
                    FROM %table.categories% c
                      LEFT JOIN %table.categories_description% cd ON c.categories_id = cd.categories_id
                      WHERE cd.language_id = :languageId
                        AND c.parent_id IN (:categoryId)
                      ORDER BY c.parent_id, c.sort_order, cd.categories_name";
            $args = array('categoryId' => array_keys($rootCategories), 'languageId' => $languageId);
            foreach (ZMRuntime::getDatabase()->fetchAll($sql, $args, array('categories', 'categories_description'), 'ZenMagick\StoreBundle\Entity\Catalog\Category') as $category) {
                $rootCategories[$category->getParentId()]->addChild($category);
            }
        }

        // save for later
        $this->cache_->save($rootCategories, Toolbox::hash('categories', 'rootCategories', $rootCategoriesKey));
        $this->rootCategories_[$rootCategoriesKey] = $rootCategories;

        return $rootCategories;
    }

    /**
     * Get all categories.
     *
     * @param int languageId Language id.
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Category</code> instances.
     */
    public function getAllCategories($languageId) {
        return $this->getCategories($languageId);
    }

    /**
     * Get all categories.
     *
     * @param int languageId Language id.
     * @param array ids Optional list of category ids; default is <code>null</code>.
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Category</code> instances.
     */
    public function getCategories($languageId, $ids=null) {
        if (array_key_exists($languageId, $this->categories_)) {
            $categories = $this->categories_[$languageId];
        } else if (false === ($categories = $this->cache_->lookup(Toolbox::hash('categories', 'categories', $languageId)))) {
            $categories = $this->loadAndInitTree($languageId);
            // save for later
            $this->cache_->save($categories, Toolbox::hash('categories', 'categories', $languageId));
        }
        $this->categories_[$languageId] = $categories;

        if (null === $ids) {
            return $categories;
        }

        $tmp = array();
        foreach ($ids as $id) {
            $tmp[$id] = $categories[$id];
        }

        return $tmp;
    }

    /**
     * This returns, in fact, not a real tree, but a list of all top level categories.
     *
     * @param int languageId Language id.
     * @return array A list of all top level categories (<code>parentId == 0</code>).
     */
    public function getCategoryTree($languageId) {
        $categories = $this->getCategories($languageId);

        $tlc = array();
        foreach ($categories as $id => $category) {
            if (0 == $category->getParentId() && 0 < $id) {
                $tlc[] = $category;
            }
        }

        return $tlc;
    }

    /**
     * Get a category for the given id.
     *
     * @param int categoryId The category id.
     * @param int languageId Language id.
     * @return ZenMagick\StoreBundle\Entity\Catalog\Category A <code>ZenMagick\StoreBundle\Entity\Catalog\Category</code> instance or <code>null</code>.
     */
    public function getCategoryForId($categoryId, $languageId) {
        $categories = $this->getCategories($languageId);

        if (array_key_exists($categoryId, $categories)) {
            return $categories[$categoryId];
        }

        return null;
    }

    /**
     * Invalidate all cache entries.
     *
     * @param int languageId The language id.
     * @param ZenMagick\StoreBundle\Entity\Catalog\Category category Optional category to update in runtime cache; default is <code>null</code>.
     */
    protected function invalidateCache($languageId, $category=null) {
        $this->cache_->remove(Toolbox::hash('categories', 'categories', $languageId));
        $this->cache_->remove(Toolbox::hash('categories', 'rootCategories', $languageId));
        $this->cache_->remove(Toolbox::hash('categories', 'productTypeIdMap'));

        if (null != $category && array_key_exists($languageId, $this->categories_)) {
            $this->categories_[$languageId][$category->getId()] = $category;
        }
    }

    /**
     * Create a new category.
     *
     * @param ZenMagick\StoreBundle\Entity\Catalog\Category category The category.
     * @return Category The updated category.
     */
    public function createCategory($category) {
        $languageId = $category->getLanguageId();
        $parent = null;
        if (0 != $category->getParentId()) {
            if (null == ($parent = $this->getCategoryForId($category->getParentId(), $languageId))) {
                // invalid parent
                $category->setParentId(0);
            }
        }
        $category = ZMRuntime::getDatabase()->createModel('categories', $category);
        $category = ZMRuntime::getDatabase()->createModel('categories_description', $category);
        $this->invalidateCache($category->getLanguageId());

        // update children
        foreach ($category->getChildren() as $child) {
            $child->setParentId($category->getId());
            ZMRuntime::getDatabase()->updateModel('categories', $child);
            $this->invalidateCache($languageId, $child);
        }

        if (null != $parent) {
            // update parent
            $parent->addChild($category);
            $this->invalidateCache($category->getLanguageId(), $parent);
        } else {
            // update internal root categories cache
            $rootCategoriesKey = $languageId.'-true';
            if (array_key_exists($rootCategoriesKey, $this->rootCategories_)) {
                $this->rootCategories_[$rootCategoriesKey][$category->getId()] = $category;
            }
        }

        $this->invalidateCache($category->getLanguageId(), $category);

        return $category;
    }

    /**
     * Update an existing category.
     *
     * @param ZenMagick\StoreBundle\Entity\Catalog\Category category The category.
     * @return Category The updated category.
     */
    public function updateCategory($category) {
        $languageId = $category->getLanguageId();
        ZMRuntime::getDatabase()->updateModel('categories', $category);
        ZMRuntime::getDatabase()->updateModel('categories_description', $category);
        $this->invalidateCache($languageId, $category);

        // two way check of parent/child relationships
        $childIds = array_keys($category->getChildren());
        foreach ($this->getCategories($languageId) as $cat) {
            if ($cat->getParentId() != $category->getId() && in_array($cat->getId(), $childIds)) {
                // new child
                $cat->setParentId($category->getId());
                ZMRuntime::getDatabase()->updateModel('categories', $cat);
                $this->invalidateCache($languageId, $cat);
            }

            // check for removed children
            if ($cat->getParentId() == $category->getId() && !in_array($cat->getId(), $childIds)) {
                // removed
                $cat->setParentId(0);
                ZMRuntime::getDatabase()->updateModel('categories', $cat);
                $this->invalidateCache($languageId, $cat);
            }
        }

        return $category;
    }

    /**
     * Delete a category.
     *
     * @param ZenMagick\StoreBundle\Entity\Catalog\Category category The category.
     */
    public function deleteCategory($category) {
        $languageId = $category->getLanguageId();
        ZMRuntime::getDatabase()->removeModel('categories', $category);
        ZMRuntime::getDatabase()->removeModel('categories_description', $category);
        $this->invalidateCache($category->getLanguageId());

        if (array_key_exists($languageId, $this->categories_) && array_key_exists($category->getId(), $this->categories_[$languageId])) {
            unset($this->categories_[$languageId][$category->getId()]);
        }

        // check for dangling child categories
        $categoryId = $category->getId();
        foreach ($this->getCategories($category->getLanguageId()) as $cat) {
            if ($cat->getParentId() == $categoryId) {
                $cat->setParentId(0);
                ZMRuntime::getDatabase()->updateModel('categories', $cat);
                $this->invalidateCache($languageId, $cat);
            }
        }

        // remove all product/category mappings
        $sql = "DELETE FROM %table.products_to_categories% WHERE categories_id = :categoryId";
        ZMRuntime::getDatabase()->updateObj($sql, array('categoryId' => $category->getId()), 'products_to_categories');
        $sql = "DELETE FROM %table.product_types_to_category% WHERE category_id = :categoryId";
        ZMRuntime::getDatabase()->updateObj($sql, array('categoryId' => $category->getId()), 'product_types_to_category');
    }

    /**
     * Get the allowed product types (ids) for the given category id.
     *
     * @return array List of allowed product type ids; an empty list means no restrictions.
     */
    public function getProductTypeIds($categoryId) {
        if (null !== $this->productTypeIdMap_) {
            return array_key_exists($categoryId, $this->productTypeIdMap_) ? $this->productTypeIdMap_[$categoryId] : array();
        }

        // first check cache
        if (false !== ($productTypeIdMap = $this->cache_->lookup(Toolbox::hash('categories', 'productTypeIdMap')))) {
            $this->productTypeIdMap_ = $productTypeIdMap;
            return array_key_exists($categoryId, $this->productTypeIdMap_) ? $this->productTypeIdMap_[$categoryId] : array();
        }

        $productTypeIdMap = array();
        $sql = "SELECT * FROM %table.product_types_to_category%
                ORDER BY category_id";
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array(), 'product_types_to_category') as $result) {
            if (!array_key_exists($result['categoryId'],  $productTypeIdMap)) {
                $productTypeIdMap[$result['categoryId']] = array();
            }
            $productTypeIdMap[$result['categoryId']][] = $result['productTypeId'];
        }

        // save for later
        $this->cache_->save($productTypeIdMap, Toolbox::hash('categories', 'productTypeIdMap'));
        $this->productTypeIdMap_ = $productTypeIdMap;

        return array_key_exists($categoryId, $productTypeIdMap) ? $productTypeIdMap[$categoryId] : array();
    }

    /**
     * Load all categories and init the category tree.
     *
     * @param int languageId Language id.
     * @return array List of all loaded and initialized categories.
     */
    protected function loadAndInitTree($languageId) {
        // load all straight away - should be faster to sort them later on
        $args = array('languageId' => $languageId);
        $sql = "SELECT c.*, cd.*
                FROM %table.categories% c
                  LEFT JOIN %table.categories_description% cd ON c.categories_id = cd.categories_id
                  WHERE cd.language_id = :languageId";
        $sql .= " ORDER BY c.parent_id, c.sort_order, cd.categories_name";

        $categories = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, $args, array('categories', 'categories_description'), 'ZenMagick\StoreBundle\Entity\Catalog\Category') as $category) {
            $categories[$category->getId()] = $category;
        }

        // init tree
        foreach ($categories as $id => $category) {
            if (0 != $category->getParentId()) {
                if (array_key_exists($category->getParentId(), $categories) && null != ($parent = $categories[$category->getParentId()])) {
                    $parent->addChild($id);
                }
            }
        }

        return $categories;
    }

    /**
     * Get meta tag details for the given id and language.
     *
     * @param int categoryId The category id.
     * @param int languageId Language id.
     * @return ZenMagick\StoreBundle\Entity\Catalog\MetaTagDetails The details or <code>null</code>.
     */
    public function getMetaTagDetailsForId($categoryId, $languageId) {
        $sql = "SELECT * from %table.meta_tags_categories_description%
                WHERE categories_id = :categoryId
                  AND language_id = :languageId";
        $args = array('categoryId' => $categoryId, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, 'meta_tags_categories_description', 'ZenMagick\StoreBundle\Entity\Catalog\MetaTagDetails');
    }

}
