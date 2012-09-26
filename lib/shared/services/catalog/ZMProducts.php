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
use ZenMagick\Base\Database\Connection;
use ZenMagick\Base\Database\QueryDetails;
use ZenMagick\Base\Database\SqlAware;

/**
 * Product access.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.catalog
 */
class ZMProducts extends ZMObject implements SqlAware {
    // image size constants
    const IMAGE_SMALL = 'small';
    const IMAGE_MEDIUM = 'medium';
    const IMAGE_LARGE = 'large';

    private $cache_;
    private $categoryProductMap_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->categoryProductMap_ = null;
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
     * {@inheritDoc}
     */
    public function getQueryDetails($method=null, $args=array()) {
        $methods = array('getAllProducts', 'getProductsForCategoryId');
        if (in_array($method, $methods)) {
            return call_user_func_array(array($this, $method.'QueryDetails'), $args);
        }
        return null;
    }

    /**
     * Get all products.
     *
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Optional language id.
     * @return ZenMagick\Base\Database\QueryDetails Query details.
     */
    protected function getAllProductsQueryDetails($active=true, $languageId) {
        $sql = "SELECT p.*, pd.*, s.specials_new_products_price
                FROM %table.products% p
                  LEFT JOIN %table.specials% s ON (s.products_id = p.products_id AND s.status = 1),
                  %table.products_description% pd
                WHERE pd.products_id = p.products_id
                  AND pd.language_id = :languageId";
        if ($active) {
            $sql .= " AND p.products_status = 1";
        }
        $sql .= " ORDER BY p.products_sort_order, pd.products_name";
        $args = array('languageId' => $languageId);
        return new QueryDetails(ZMRuntime::getDatabase(), $sql, $args, array('products', 'specials', 'products_description'), 'ZenMagick\StoreBundle\Entity\Catalog\Product', 'p.products_id');
    }

    /**
     * Get all product ids.
     *
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Language id.
     * @return array A list of product ids.
     */
    public function getAllProductIds($active=true, $languageId) {
        $sql = "SELECT p.products_id
                FROM %table.products% p, %table.products_description% pd
                WHERE ";
        if ($active) {
            $sql .= " p.products_status = 1 AND ";
        }
        $sql .= " pd.products_id = p.products_id
                    AND pd.language_id = :languageId
                  ORDER BY p.products_sort_order, pd.products_name";
        $results = ZMRuntime::getDatabase()->fetchAll($sql, array('languageId' => $languageId), array('products', 'products_description'));
        $productIds = array();
        foreach ($results as $result) {
            $productIds[] = $result['productId'];
        }
        return $productIds;
    }

    /**
     * Get all products.
     *
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Language id.
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Product</code> instances.
     */
    public function getAllProducts($active=true, $languageId) {
        $sql = "SELECT p.products_id
                FROM %table.products% p, %table.products_description% pd
                WHERE ";
        if ($active) {
            $sql .= " p.products_status = 1 AND ";
        }
        $sql .= " pd.products_id = p.products_id
                    AND pd.language_id = :languageId
                  ORDER BY p.products_sort_order, pd.products_name";
        $results = ZMRuntime::getDatabase()->fetchAll($sql, array('languageId' => $languageId), array('products', 'products_description'));
        $productIds = array();
        foreach ($results as $result) {
            $productIds[] = $result['productId'];
        }
        return $this->getProductsForIds($this->getAllProductIds($active, $languageId), true, $languageId);
    }

    /**
     * Get list of all active product ids for a given category.
     *
     * <p>This is a faster version of <code>getProductsForCategoryId(int)</code>. In addition,
     * this will ignore language preferences.
     *
     * @param int categoryId The category id.
     * @param int languageId Language id.
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param boolean includeChildren Optional flag to include subcategories; default is <code>false</code>.
     * @return array A list of product ids.
     */
    public function getProductIdsForCategoryId($categoryId, $languageId, $active=true, $includeChildren=false) {
        // asuming that if we do this once we might do this more often...
        $mainKey = $active ? 'active' : 'all';
        $mainKey .= ':'.$languageId;

        if (null === $this->categoryProductMap_ || !isset($this->categoryProductMap_[$mainKey])) {
            if (null === $this->categoryProductMap_) {
                $this->categoryProductMap_ = array();
            }
            if (!isset($this->categoryProductMap_[$mainKey])) {
                $this->categoryProductMap_[$mainKey] = array();
            }

            $sql = "SELECT p.products_id, p2c.categories_id
                    FROM %table.products_description% pd, %table.products% p, %table.products_to_categories% p2c
                    WHERE ";
            if ($active) {
                $sql .= " p.products_status = 1 AND ";
            }
            $sql .= " p.products_id = p2c.products_id AND pd.products_id = p2c.products_id
                        AND pd.language_id = :languageId
                      ORDER BY p.products_sort_order, pd.products_name";
            $args = array('languageId' => $languageId);
            $results = ZMRuntime::getDatabase()->fetchAll($sql, $args, array('products', 'products_description', 'products_to_categories'));
            foreach ($results as $result) {
                $cId = $result['categoryId'];
                if (!isset($this->categoryProductMap_[$mainKey][$cId])) {
                    $this->categoryProductMap_[$mainKey][$cId] = array();
                }
                $this->categoryProductMap_[$mainKey][$cId][] = $result['productId'];
            }
        }

        $ids = isset($this->categoryProductMap_[$mainKey][$categoryId]) ? $this->categoryProductMap_[$mainKey][$categoryId] : array();
        if ($includeChildren) {
            $category = $this->container->get('categoryService')->getCategoryForId($categoryId, $languageId);
            foreach ($category->getChildren() as $child) {
                $ids = array_merge($ids, $this->getProductIdsForCategoryId($child->getId(), $languageId, $active));
            }
        }
        return $ids;
    }

    /**
     * Get all (active) products for the given category id.
     *
     * @param int categoryId The category id.
     * @param boolean active If <code>true</code> return only active products.
     * @param int languageId Language id.
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Product</code> instances.
     */
    protected function getProductsForCategoryIdQueryDetails($categoryId, $active, $languageId) {
        $sql = "SELECT p.*, pd.*, m.*, s.specials_new_products_price
                FROM %table.products% p
                  LEFT JOIN %table.specials% s ON (s.products_id = p.products_id)
                  LEFT JOIN %table.manufacturers% m ON (m.manufacturers_id = p.manufacturers_id),
                  %table.products_description% pd, %table.products_to_categories% p2c
                WHERE pd.products_id = p.products_id AND p2c.categories_id = :categoryId
                  AND p.products_id = p2c.products_id AND pd.products_id = p2c.products_id
                  AND pd.language_id = :languageId";
        if ($active) {
            $sql .= " AND p.products_status = 1";
        }
        $sql .= " ORDER BY p.products_sort_order, pd.products_name";
        $args = array('categoryId' => $categoryId, 'languageId' => $languageId);
        return new QueryDetails(ZMRuntime::getDatabase(), $sql, $args, array('products', 'specials', 'products_description', 'products_to_categories'), 'ZenMagick\StoreBundle\Entity\Catalog\Product', 'p.products_id');
    }

    /**
     * Get all (active) products for the given category id.
     *
     * @param int categoryId The category id.
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Product</code> instances.
     */
    public function getProductsForCategoryId($categoryId, $active=true, $languageId=null) {
        return $this->getProductsForIds($this->getProductIdsForCategoryId($categoryId, $languageId, $active), true, $languageId);
    }

    /*
     * Get all active products for a manufacturer.
     *
     * @param int manufacturerId The manufacturers id.
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Language id.
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Product</code> instances.
     */
    public function getProductsForManufacturerId($manufacturerId, $active=true, $languageId) {
        $sql = "SELECT p.products_id
                FROM %table.products% p, %table.products_description% pd, %table.manufacturers% m
                WHERE ";
        if ($active) {
            $sql .= " p.products_status = 1 AND ";
        }
        $sql .= " pd.products_id = p.products_id
                    AND pd.language_id = :languageId
                    AND p.manufacturers_id = m.manufacturers_id AND p.manufacturers_id = :manufacturerId
                  ORDER BY p.products_sort_order, pd.products_name";
        $args = array('manufacturerId' => $manufacturerId, 'languageId' => $languageId);
        $results = ZMRuntime::getDatabase()->fetchAll($sql, $args, array('products', 'products_description', 'manufacturers'));
        $productIds = array();
        foreach ($results as $result) {
            $productIds[] = $result['productId'];
        }
        return $this->getProductsForIds($productIds, true, $languageId);
    }


    /**
     * Test if a given product type option is enabled for a given product.
     *
     * @param int productId The product id.
     * @param string field The option name.
     * @param string keyPrefix Optional key prefix; default is <em>_INFO</em>.
     * @param string keySuffix Optional key suffix; default is <em>SHOW_</em>.
     * @param string fieldPrefix Optional field prefix; default is <em>_</em>.
     * @param string fieldSuffix Optional field suffix; default is an empty string.
     * @return boolean <code>true</code> if the specified type option is enabled, <code>false</code> if not.
     */
    public function getProductTypeSetting($productId, $field, $keyPprefix='_INFO', $keySuffix='SHOW_', $fieldPrefix= '_', $fieldSuffix='') {
        $database = ZMRuntime::getDatabase();
        $sql = "select products_type from %table.products%
                where products_id = :productId";
        $typeResult = $database->querySingle($sql, array('productId' => $productId), 'products', Connection::MODEL_RAW);

        $sql = "select type_handler from %table.product_types%
                where type_id = :id";
        $keyResult = $database->querySingle($sql, array('id' => $typeResult['products_type']), 'product_types', Connection::MODEL_RAW);

        $key = strtoupper($keySuffix . $keyResult['type_handler'] . $keyPprefix . $fieldPrefix . $field . $fieldSuffix);

        $sql = "select configuration_value from %table.product_type_layout%
                where configuration_key = :key";
        $valueResult = $database->querySingle($sql, array('key' => $key), 'product_type_layout', Connection::MODEL_RAW);

        if (null !== $valueResult) {
            // type result
            return 1 == $valueResult['configuration_value'];
        } else {
            // fallback general configuration
            $sql = "select configuration_value from %table.configuration%
                    where configuration_key = :key";
            $valueResult = $database->querySingle($sql, array('key' => $key), 'configuration', Connection::MODEL_RAW);

            if (null !== $valueResult) {
                return 1 == $valueResult['configuration_value'];
            }
        }
        return false;
    }

    /**
     * Get random featured products.
     *
     * @param int categoryId Optional category id to narrow down results; default is <code>null</code> for all.
     * @param int max The maximum number of results; default is <code>0</code> for all.
     * @param boolean includeChildren Optional flag to include child categories in the search; default is <code>false</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Product</code> instances.
     */
    public function getFeaturedProducts($categoryId=null, $max=0, $includeChildren=false, $languageId=null) {
        $sql = null;
        if (null == $categoryId) {
            $sql = "select distinct p.products_id
                    from %table.products% p
                    left join %table.featured% f on p.products_id = f.products_id
                    where p.products_id = f.products_id
                      and p.products_status = '1'
                      and f.status = '1'";
        } else {
            $categoryCond = $includeChildren ? '(c.parent_id = :categoryId or c.categories_id = :categoryId)' : 'c.categories_id = :categoryId';
            $sql = "select distinct p.products_id
                    from (%table.products% p
                    left join %table.featured% f on p.products_id = f.products_id),
                    %table.products_to_categories% p2c, %table.categories% c
                    where p.products_id = p2c.products_id
                      and p2c.categories_id = c.categories_id
                      and " . $categoryCond . "
                      and p.products_id = f.products_id
                      and p.products_status = 1 and f.status = 1";
        }

        $args =  array('categoryId' => $categoryId);
        $tables = array('products', 'products_to_categories');
        $productIds = 0 != $max ? $this->getRandomProductIds($sql, $max, $args, $tables) : $this->getProductIds($sql, $args, $tables);
        return $this->getProductsForIds($productIds, false, $languageId);
    }

    /**
     * Get random new products.
     *
     * @param int categoryId Optional category id to narrow down results; default is <code>null</code> for all.
     * @param int max The maximum number of results; default is <code>0</code> for all.
     * @param int timeLimit Optional time limit in days (or first of month for using <em>1</em);
     *  default is <code>null</code> to use the setting 'maxNewProducts'.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Product</code> instances.
     */
    public function getNewProducts($categoryId=null, $max=0, $timeLimit=null, $languageId=null) {
        $timeLimit = (int)(null === $timeLimit ? Runtime::getSettings()->get('maxNewProducts') : $timeLimit);

        $queryLimit = '';
        $orderBy = ' ORDER BY products_date_added DESC';
        switch ($timeLimit) {
            case 0:
                // no global limit, so use some same limits just in case...
                $queryLimit = '';
                $date = null;
                break;
            case 1:
                // this month
                $date = new DateTime();
                $date->modify('first day of this month');
                $queryLimit = ' AND p.products_date_added >= :dateAdded';
                break;
            default:
                // X days; 24 hours; 60 mins; 60secs
                $date = new DateTime();
                $date->modify(sprintf('-%s days', $timeLimit));
                $queryLimit = ' AND p.products_date_added >= :dateAdded';
                break;
        }

        $totalLimit = 0 != $max ? ($max * 3) : 50;
        $orderBy .= ' LIMIT 0, '.$totalLimit;

        $sql = null;
        if (null == $categoryId) {
            $sql = "SELECT p.products_id
                      FROM %table.products% p
                      WHERE p.products_status = 1" . $queryLimit;
        } else {
            $sql = "SELECT DISTINCT p.products_id
                    FROM %table.products% p, %table.products_to_categories% p2c, %table.categories% c
                    WHERE p.products_id = p2c.products_id
                      AND p2c.categories_id = c.categories_id
                      AND c.categories_id = :categoryId
                      AND p.products_status = 1" . $queryLimit;
        }
        $sql .= $orderBy;

        $args =  array('categoryId' => $categoryId, 'dateAdded' => $date);
        $tables = array('products', 'products_to_categories');
        $productIds = 0 != $max ? $this->getRandomProductIds($sql, $max, $args, $tables) : $this->getProductIds($sql, $args, $tables);
        return $this->getProductsForIds($productIds, true, $languageId);
    }

    /**
     * Get best seller products.
     *
     * @param int categoryId Optional category id to narrow down results; default is <code>null</code> for all.
     * @param int max The maximum number of results; default is <code>null</code> to use the setting <em>maxBestSellers</em>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Product</code> instances.
     */
    public function getBestSellers($categoryId=null, $max=null, $languageId=null) {
        $max = null === $max ? Runtime::getSettings()->get('maxBestSellers') : $max;

        $sql = null;
        if (null !== $categoryId) {
            $sql = "SELECT DISTINCT p.products_id
                    FROM %table.products% p,
                    %table.products_to_categories% p2c, %table.categories% c
                    WHERE p.products_status = '1'
                      AND p.products_ordered > 0
                      AND p.products_id = p2c.products_id
                      AND p2c.categories_id = c.categories_id
                      AND :categoryId IN (c.categories_id, c.parent_id)
                    ORDER BY p.products_ordered desc";
        } else {
            $sql = "SELECT DISTINCT p.products_id, p.products_ordered
                    FROM %table.products% p
                    WHERE p.products_status = '1'
                      AND p.products_ordered > 0
                    ORDER BY p.products_ordered desc";
        }

        $args =  array('categoryId' => $categoryId);
        $tables = array('products', 'products_to_categories');
        $productIds = $this->getProductIds($sql, $args, $tables);
        if (count($productIds) > $max) {
            $productIds = array_splice($productIds, 0, $max);
        }
        return $this->getProductsForIds($productIds, true, $languageId);
    }

    /**
     * Get random products marked as specials.
     *
     * @param int max The maximum number of results; default is <code>null</code> to use the setting <em>maxSpecialProducts</em>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Product</code> instances.
     */
    public function getSpecials($max=null, $languageId=null) {
        $max = null === $max ? Runtime::getSettings()->get('maxSpecialProducts') : $max;

        $sql = "select distinct p.products_id
                from %table.products% p, %table.specials% s
                where p.products_status = 1
                  AND p.products_id = s.products_id
                  AND s.status = 1";

        $productIds = 0 !== $max ? $this->getRandomProductIds($sql, $max) : $this->getProductIds($sql);
        return $this->getProductsForIds($productIds, false, $languageId);
    }

    /**
     * Get a product for the given model name.
     *
     * @param string model The model name.
     * @param int languageId Language id.
     * @return ZenMagick\StoreBundle\Entity\Catalog\Product The product or <code>null</code>.
     */
    public function getProductForModel($model, $languageId) {
        $sql = "SELECT p.*, pd.*, s.specials_new_products_price
                FROM %table.products% p
                LEFT JOIN %table.specials% s ON (s.products_id = p.products_id AND s.status = 1),
                %table.products_description% pd
                WHERE p.products_status = '1'
                  AND p.products_model = :model
                  AND pd.products_id = p.products_id
                  AND pd.language_id = :languageId";
        $args = array('model' => $model, 'languageId' => $languageId);

        $product = ZMRuntime::getDatabase()->querySingle($sql, $args, array('products', 'products_description', 'specials'), 'ZenMagick\StoreBundle\Entity\Catalog\Product');
        if (null != $product && null != $this->cache_) {
            $this->cache_->save($product, Toolbox::hash('product', $product->getId(), $product->getLanguageId()));
        }

        return $product;
    }

    /**
     * Get a product for the given product id.
     *
     * @param int productId The product id.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return ZenMagick\StoreBundle\Entity\Catalog\Product The product or <code>null</code>.
     */
    public function getProductForId($productId, $languageId=null) {
        if (null === $languageId) {
            $session = $this->container->get('session');
            $languageId = $session->getLanguageId();
        }

        if (null != $this->cache_ && false !== ($product = $this->cache_->lookup(Toolbox::hash('product', $productId, $languageId)))) {
            return $product;
        }

        $sql = "SELECT p.*, pd.*, s.specials_new_products_price
                FROM %table.products% p
                LEFT JOIN %table.specials% s ON (s.products_id = p.products_id AND s.status = 1),
                %table.products_description% pd
                WHERE p.products_id = :productId
                  AND pd.products_id = p.products_id
                  AND pd.language_id = :languageId";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        $product = ZMRuntime::getDatabase()->querySingle($sql, $args, array('products', 'products_description', 'specials'), 'ZenMagick\StoreBundle\Entity\Catalog\Product');

        if (null != $this->cache_ && null != $product) {
            $this->cache_->save($product, Toolbox::hash('product', $productId, $languageId));
        }

        return $product;
    }

    /**
     * Load a list of products.
     *
     * @param array productIds A list of (int) product ids.
     * @param boolean preserveOrder Optional flag to return the products in the order of the given id list, rather
     *  than using the default product sort order; default is <code>false</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return ZenMagick\StoreBundle\Entity\Catalog\Product The product or <code>null</code>.
     */
    public function getProductsForIds($productIds, $preserveOrder=false, $languageId=null) {
        if (null === $languageId) {
            $session = $this->container->get('session');
            $languageId = $session->getLanguageId();
        }

        $products = array();
        if (0 == count($productIds)) {
            return $products;
        }

        // check cache first
        $needLoadIds = array();
        foreach ($productIds as $id) {
            if (null != $this->cache_ && false !== ($product = $this->cache_->lookup(Toolbox::hash('product', $id, $languageId)))) {
                $products[] = $product;
            } else {
                $needLoadIds[$id] = $id;
            }
        }

        if (0 < count($needLoadIds)) {
            $sql = "SELECT p.*, pd.*, s.specials_new_products_price
                    FROM %table.products% p
                    LEFT JOIN %table.specials% s ON (s.products_id = p.products_id AND s.status = 1),
                    %table.products_description% pd
                    WHERE p.products_id in (:productId)
                      AND pd.products_id = p.products_id
                      AND pd.language_id = :languageId";
            if (!$preserveOrder) {
                $sql .= " ORDER BY p.products_sort_order, pd.products_name";
            }
            $args = array('productId' => $needLoadIds, 'languageId' => $languageId);
            $results = ZMRuntime::getDatabase()->fetchAll($sql, $args, array('products', 'products_description', 'specials'), 'ZenMagick\StoreBundle\Entity\Catalog\Product');
            foreach ($results as $product) {
                $products[] = $product;
                // put in cache
                if (null != $this->cache_) {
                    $this->cache_->save($product, Toolbox::hash('product', $product->getId(), $languageId));
                }
            }
        }

        if ($preserveOrder) {
            // rearrange to same order as original id list; breaks array_slice and foreach order if not done
            $orderLookup = array_flip($productIds);
            $reordered = array();
            foreach ($products as $id => $product) {
                $reordered[(int)($orderLookup[$products[$id]->getId()])] = $products[$id];
            }
            $products = $reordered;
            ksort($products);
        }

        return $products;
    }

    /**
     * Update an existing product.
     *
     * @param ZenMagick\StoreBundle\Entity\Catalog\Product product The product.
     * @return ZenMagick\StoreBundle\Entity\Catalog\Product The updated product.
     */
    public function updateProduct($product) {
        ZMRuntime::getDatabase()->updateModel('products', $product);
        ZMRuntime::getDatabase()->updateModel('products_description', $product);
        ZMRuntime::getDatabase()->updateModel('meta_tags_products_description', $product->getMetaTagDetails());

        // update cache
        if (null != $this->cache_) {
            $this->cache_->remove(Toolbox::hash('product', $product->getId(), $product->getLanguageId()));
        }

        return $product;
    }

    /**
     * Update the view count for a product.
     *
     * @param int productId The product id.
     * @param int languageId Language id.
     */
    public function updateViewCount($productId, $languageId) {
        $sql = "UPDATE %table.products_description%
                SET products_viewed = products_viewed+1
                WHERE products_id = :productId
                AND language_id = :languageId";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->updateObj($sql, $args, 'products_description');
    }

    /**
     * Execute the given SQL and extract the resulting product ids.
     *
     * @param string sql Some SQL.
     * @param array args Optional query args; default is an empty array.
     * @param mixed tables Optional list of mapping table(s); default is <code>'products'</code>.
     * @return array A list of product ids.
     */
    private function getProductIds($sql, $args=array(), $tables='products') {
        $productIds = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, $args, $tables) as $result) {
            $productId = $result['productId'];
            $productIds[] = $productId;
        }

        return $productIds;
    }


    /**
     * Get some random product ids.
     *
     * @param string sql Some SQL.
     * @param int max The maximum number of results; default is <em>0</em> for all.
     * @param array args Optional query args; default is an empty array.
     * @param mixed tables Optional list of mapping table(s); default is <code>'products'</code>.
     * @return array A list of product ids.
     */
    private function getRandomProductIds($sql, $max=0, $args=array(), $tables='products') {
        $productIds = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, $args, $tables) as $result) {
            $productId = $result['productId'];
            $productIds[$productId] = $productId;
        }

        shuffle($productIds);

        if (0 < $max && count($productIds) > $max) {
            $productIds = array_slice($productIds, 0, $max);
        }

        return $productIds;
    }

    /**
     * Check if a certain quantity of a given product is available.
     *
     * @param int productId The product id.
     * @param int quantity The desired quantity.
     * @return boolean <code>true</code> if the requested quantity is available, <code>false</code> if not.
     */
    public function isQuantityAvailable($productId, $quantity) {
        $sql = "SELECT products_quantity
                from %table.products%
                where products_id = :productId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('productId' => $productId), 'products');
        $available = 0;
        if (null != $result) {
            $available = $result['quantity'];
        }

        return 0 <= ($available - $quantity);
    }

    /**
     * Execute the given SQL and return the resulting product.
     *
     * @param string sql Some SQL.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Product</code> instances.
     */
    public function getProductsForSQL($sql, $languageId=null) {
        $productIds = $this->getProductIds($sql);
        return $this->getProductsForIds($productIds, true, $languageId);
    }

    /**
     * Get meta tag details for the given id and language.
     *
     * @param int productId The product id.
     * @param int languageId Language id.
     * @return ZenMagick\StoreBundle\Entity\Catalog\MetaTagDetails The details or <code>null</code>.
     */
    public function getMetaTagDetailsForId($productId, $languageId) {
        $sql = "SELECT * from %table.meta_tags_products_description%
                WHERE products_id = :productId
                  AND language_id = :languageId";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, 'meta_tags_products_description', 'ZenMagick\StoreBundle\Entity\Catalog\MetaTagDetails');
    }

    /**
     * Update price sorter price for the given product id.
     *
     * @param int productId The id of the product to update.
     */
    public function updateSortPrice($productId) {
        if (null != ($product = $this->getProductForId($productId))) {
            $product->setPriceSorter($product->getCalculatedPrice());
            $this->updateProduct($product);
        }
    }

}
