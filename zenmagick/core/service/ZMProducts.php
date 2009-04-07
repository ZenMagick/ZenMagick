<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Product access.
 *
 * @author DerManoMann
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMProducts extends ZMObject implements ZMSQLAware {
    // image size constants
    const IMAGE_SMALL = 'small';
    const IMAGE_MEDIUM = 'medium';
    const IMAGE_LARGE = 'large';

    private $cache;
    private $categoryProductMap_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->cache = ZMCaches::instance()->getCache('services', array(), ZMCache::TRANSIENT);
        $this->categoryProductMap_ = null;
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
        return ZMObject::singleton('Products');
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
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return ZMQueryDetails Query details.
     */
    protected function getAllProductsQueryDetails($active=true, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT p.*, pd.*, s.specials_new_products_price
                FROM " . TABLE_PRODUCTS . " p 
                  LEFT JOIN " . TABLE_SPECIALS . " s ON (s.products_id = p.products_id AND s.status = 1), 
                  " . TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE pd.products_id = p.products_id
                  AND pd.language_id = :languageId";
        if ($active) {
            $sql .= " AND p.products_status = 1";
        }
        $sql .= " ORDER BY p.products_sort_order, pd.products_name";
        $args = array('languageId' => $languageId);
        return ZMLoader::make('QueryDetails', $sql, $args, array(TABLE_PRODUCTS, TABLE_SPECIALS, TABLE_PRODUCTS_DESCRIPTION), 'Product', 'p.products_id');
    }

    /**
     * Get all products.
     *
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    public function getAllProducts($active=true, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT p.products_id
                FROM " . TABLE_PRODUCTS . " p, " .  TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE ";
        if ($active) {
            $sql .= " p.products_status = 1 AND ";
        }
        $sql .= " pd.products_id = p.products_id
                    AND pd.language_id = :languageId
                  ORDER BY p.products_sort_order, pd.products_name";
        $results = ZMRuntime::getDatabase()->query($sql, array('languageId' => $languageId), array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION));
        $productIds = array();
        foreach ($results as $result) {
            $productIds[] = $result['productId'];
        }
        return $this->getProductsForIds($productIds, false, $languageId);
    }

    /**
     * Get list of all active product ids for a given category.
     *
     * <p>This is a faster version of <code>getProductsForCategoryId(int)</code>. In addition,
     * this will ignore language preferences.
     *
     * @param int categoryId The category id.
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param boolean includeChildren Optional flag to include subcategories; default is <code>false</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of product ids.
     */
    public function getProductIdsForCategoryId($categoryId, $active=true, $includeChildren=false, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

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
                    FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd, " .  TABLE_PRODUCTS . " p, " .  TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                    WHERE ";
            if ($active) {
                $sql .= " p.products_status = 1 AND ";
            }
            $sql .= " p.products_id = p2c.products_id AND pd.products_id = p2c.products_id
                        AND pd.language_id = :languageId
                      ORDER BY p.products_sort_order, pd.products_name";
            $args = array('languageId' => $languageId);
            $results = ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION, TABLE_PRODUCTS_TO_CATEGORIES));
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
            $category = ZMCategories::instance()->getCategoryForId($categoryId);
            foreach ($category->getChildren() as $child) {
                $ids = array_merge($ids, $this->getProductIdsForCategoryId($child->getId(), $active, true, $languageId));
            }
        }

        return $ids;
    }

    /**
     * Get all (active) products for the given category id.
     *
     * @param int categoryId The category id.
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    protected function getProductsForCategoryIdQueryDetails($categoryId, $active=true, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT p.*, pd.*, m.*, s.specials_new_products_price
                FROM " . TABLE_PRODUCTS . " p 
                  LEFT JOIN " . TABLE_SPECIALS . " s ON (s.products_id = p.products_id AND s.status = 1)
                  LEFT JOIN " . TABLE_MANUFACTURERS . " m ON (m.manufacturers_id = p.manufacturers_id),
                  " . TABLE_PRODUCTS_DESCRIPTION . " pd, " .  TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                WHERE pd.products_id = p.products_id AND p2c.categories_id = :categoryId
                  AND p.products_id = p2c.products_id AND pd.products_id = p2c.products_id
                  AND pd.language_id = :languageId";
        if ($active) {
            $sql .= " AND p.products_status = 1";
        }
        $sql .= " ORDER BY p.products_sort_order, pd.products_name";
        $args = array('categoryId' => $categoryId, 'languageId' => $languageId);
        return ZMLoader::make('QueryDetails', $sql, $args, array(TABLE_PRODUCTS, TABLE_SPECIALS, TABLE_PRODUCTS_DESCRIPTION, TABLE_PRODUCTS_TO_CATEGORIES), 'Product', 'p.products_id');
    }

    /**
     * Get all (active) products for the given category id.
     *
     * @param int categoryId The category id.
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    public function getProductsForCategoryId($categoryId, $active=true, $languageId=null) {
        return $this->getProductsForIds($this->getProductIdsForCategoryId($categoryId, $active, false, $languageId));
    }

    /*
     * Get all active products for a manufacturer.
     *
     * @param int manufacturerId The manufacturers id.
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    public function getProductsForManufacturerId($manufacturerId, $active=true, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT p.products_id
                FROM " . TABLE_PRODUCTS . " p, " .  TABLE_PRODUCTS_DESCRIPTION . " pd, " .  TABLE_MANUFACTURERS . " m
                WHERE ";
        if ($active) {
            $sql .= " p.products_status = 1 AND ";
        }
        $sql .= " pd.products_id = p.products_id
                    AND pd.language_id = :languageId
                    AND p.manufacturers_id = m.manufacturers_id AND p.manufacturers_id = :manufacturerId
                  ORDER BY p.products_sort_order, pd.products_name";
        $args = array('manufacturerId' => $manufacturerId, 'languageId' => $languageId);
        $results = ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION, TABLE_MANUFACTURERS));
        $productIds = array();
        foreach ($results as $result) {
            $productIds[] = $result['productId'];
        }
        return $this->getProductsForIds($productIds, false, $languageId);
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
        $sql = "select products_type from " . TABLE_PRODUCTS . "
                where products_id = :productId";
        $typeResult = $database->querySingle($sql, array('productId' => $productId), TABLE_PRODUCTS, ZMDatabase::MODEL_RAW);

        $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . "
                where type_id = :id";
        $keyResult = $database->querySingle($sql, array('id' => $typeResult['products_type']), TABLE_PRODUCT_TYPES, ZMDatabase::MODEL_RAW);

        $key = strtoupper($keySuffix . $keyResult['type_handler'] . $keyPprefix . $fieldPrefix . $field . $fieldSuffix);

        $sql = "select configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . "
                where configuration_key = :key";
        $valueResult = $database->querySingle($sql, array('key' => $key), TABLE_PRODUCT_TYPE_LAYOUT, ZMDatabase::MODEL_RAW);

        if (null !== $valueResult) {
            // type result
            return 1 == $valueResult['configuration_value'];
        } else {
            // fallback general configuration
            $sql = "select configuration_value from " . TABLE_CONFIGURATION . "
                    where configuration_key = :key";
            $valueResult = $database->querySingle($sql, array('key' => $key), TABLE_CONFIGURATION, ZMDatabase::MODEL_RAW);

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
     * @return array A list of <code>ZMProduct</code> instances.
     */
    public function getFeaturedProducts($categoryId=null, $max=0, $includeChildren=false, $languageId=null) {
		    $sql = null;
        if (null == $categoryId) {
            $sql = "select distinct p.products_id
                    from " . TABLE_PRODUCTS . " p 
                    left join " . TABLE_FEATURED . " f on p.products_id = f.products_id
                    where p.products_id = f.products_id 
                      and p.products_status = '1'
                      and f.status = '1'";
        } else {
            $categoryCond = $includeChildren ? '(c.parent_id = :categoryId or c.categories_id = :categoryId)' : 'c.categories_id = :categoryId';
            $sql = "select distinct p.products_id
                    from (" . TABLE_PRODUCTS . " p
                    left join " . TABLE_FEATURED . " f on p.products_id = f.products_id), " .
                     TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " .  TABLE_CATEGORIES . " c
                    where p.products_id = p2c.products_id
                      and p2c.categories_id = c.categories_id
                      and " . $categoryCond . "
                      and p.products_id = f.products_id
                      and p.products_status = 1 and f.status = 1";
        }

        $args =  array('categoryId' => $categoryId);
        $tables = array(TABLE_PRODUCTS, TABLE_PRODUCTS_TO_CATEGORIES);
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
     * @return array A list of <code>ZMProduct</code> instances.
     */
    public function getNewProducts($categoryId=null, $max=0, $timeLimit=null, $languageId=null) {
        $timeLimit = null === $timeLimit ? ZMSettings::get('maxNewProducts') : $timeLimit;

        $queryLimit = '';
        switch ($timeLimit) {
            case '0':
                // no global limit
                $queryLimit = '';
                $date = '';
                break;
            case '1':
                // this month
                $date = date('Ym', time()) . '01';
                $queryLimit = ' and p.products_date_added >= :dateAdded';
                break;
            default:
                // X days; 24 hours; 60 mins; 60secs
                $dateRange = time() - ($timeLimit * 24 * 60 * 60);
                $date = date('Ymd', $dateRange);
                $queryLimit = ' and p.products_date_added >= :dateAdded';
                break;
        }

        $sql = null;
        if (null == $categoryId) {
            $sql = "select p.products_id
                      from " . TABLE_PRODUCTS . " p
                      where p.products_status = 1" . $queryLimit;
        } else {
            $sql = "select distinct p.products_id
                    from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " .  TABLE_CATEGORIES . " c
                    where p.products_id = p2c.products_id
                    and p2c.categories_id = c.categories_id
                    and c.categories_id = :categoryId
                    and p.products_status = 1" . $queryLimit;
        }
        $sql .= " order by products_date_added";

        $args =  array('categoryId' => $categoryId, 'dateAdded' => $date);
        $tables = array(TABLE_PRODUCTS, TABLE_PRODUCTS_TO_CATEGORIES);
        $productIds = 0 != $max ? $this->getRandomProductIds($sql, $max, $args, $tables) : $this->getProductIds($sql, $args, $tables);
        return $this->getProductsForIds($productIds, false, $languageId);
    }

    /**
     * Get best seller products.
     *
     * @param int categoryId Optional category id to narrow down results; default is <code>null</code> for all.
     * @param int max The maximum number of results; default is <code>null</code> to use the setting <em>maxBestSellers</em>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    public function getBestSellers($categoryId=null, $max=null, $languageId=null) {
        $max = null === $max ? ZMSettings::get('maxBestSellers') : $max;

        $sql = null;
        if (null !== $categoryId) {
            $sql = "select distinct p.products_id
                    from " . TABLE_PRODUCTS . " p, "
                    . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c 
                    where p.products_status = '1'
                    and p.products_ordered > 0
                    and p.products_id = p2c.products_id
                    and p2c.categories_id = c.categories_id
                    and :categoryId in (c.categories_id, c.parent_id)
                    order by p.products_ordered desc";
        } else {
            $sql = "select distinct p.products_id, p.products_ordered
                    from " . TABLE_PRODUCTS . " p
                    where p.products_status = '1'
                    and p.products_ordered > 0
                    order by p.products_ordered desc";
        }

        $args =  array('categoryId' => $categoryId);
        $tables = array(TABLE_PRODUCTS, TABLE_PRODUCTS_TO_CATEGORIES);
        $productIds = $this->getProductIds($sql, $args, $tables);
        if (count($productIds) > $max) {
            $productIds = array_splice($productIds, 0, $max);
        }
        return $this->getProductsForIds($productIds, false, $languageId);
    }

    /**
     * Get random products marked as specials.
     *
     * @param int max The maximum number of results; default is <code>null</code> to use the setting <em>maxSpecialProducts</em>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    public function getSpecials($max=null, $languageId=null) {
        $max = null === $max ? ZMSettings::get('maxSpecialProducts') : $max;

        $sql = "select distinct p.products_id
                from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s
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
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return ZMProduct The product or <code>null</code>.
     */
    public function getProductForModel($model, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT p.*, pd.*, s.specials_new_products_price
                FROM " . TABLE_PRODUCTS . " p 
                LEFT JOIN " . TABLE_SPECIALS . " s ON (s.products_id = p.products_id AND s.status = 1), 
                " . TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE p.products_status = '1'
                  AND p.products_model = :model
                  AND pd.products_id = p.products_id
                  AND pd.language_id = :languageId";
        $args = array('model' => $model, 'languageId' => $languageId);

        $product = ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION, TABLE_SPECIALS), 'Product');
        if (null != $product) {
            $this->cache->save($product, ZMTools::mkUnique('product', $product->getId(), $product->getLanguageId()));
        }

        return $product;
    }

    /**
     * Get a product for the given product id.
     *
     * @param int productId The product id.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return ZMProduct The product or <code>null</code>.
     */
    public function getProductForId($productId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        if (false !== ($product = $this->cache->lookup(ZMTools::mkUnique('product', $productId, $languageId)))) {
            return $product;
        }

        $sql = "SELECT p.*, pd.*, s.specials_new_products_price
                FROM " . TABLE_PRODUCTS . " p 
                LEFT JOIN " . TABLE_SPECIALS . " s ON (s.products_id = p.products_id AND s.status = 1), 
                " . TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE p.products_id = :productId
                  AND pd.products_id = p.products_id
                  AND pd.language_id = :languageId";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        $product = ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION, TABLE_SPECIALS), 'Product');

        $this->cache->save($product, ZMTools::mkUnique('product', $productId, $languageId));

        return $product;
    }

    /**
     * Load a list of products.
     *
     * @param array productIds A list of (int) product ids.
     * @param boolean preserveOrder Optional flag to return the products in the order of the given id list, rather 
     *  than using the default product sort order; default is <code>false</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return ZMProduct The product or <code>null</code>.
     */
    public function getProductsForIds($productIds, $preserveOrder=false, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $products = array();
        if (0 == count($productIds)) {
            return $products;
        }

        // check cache first
        $needLoadIds = array();
        foreach ($productIds as $id) {
            if (false !== ($product = $this->cache->lookup(ZMTools::mkUnique('product', $id, $languageId)))) {
                $products[] = $product;
            } else {
                $needLoadIds[$id] = $id;
            }
        }

        if (0 < count($needLoadIds)) {
            $sql = "SELECT p.*, pd.*, s.specials_new_products_price
                    FROM " . TABLE_PRODUCTS . " p 
                    LEFT JOIN " . TABLE_SPECIALS . " s ON (s.products_id = p.products_id AND s.status = 1), 
                    " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    WHERE p.products_id in (:productId)
                      AND pd.products_id = p.products_id
                      AND pd.language_id = :languageId";
            if (!$preserveOrder) {
                $sql .= " ORDER BY p.products_sort_order, pd.products_name";
            }
            $args = array('productId' => $needLoadIds, 'languageId' => $languageId);
            $results = ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION, TABLE_SPECIALS), 'Product');
            foreach ($results as $product) {
                $products[] = $product;
                // put in cache
                $this->cache->save($product, ZMTools::mkUnique('product', $product->getId(), $languageId));
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
     * <p><strong>NOTE: Currently only the products table is updated!</strong></p>
     *
     * @param ZMProduct product The product.
     * @return ZMProduct The updated product.
     */
    public function updateProduct($product) {
        ZMRuntime::getDatabase()->updateModel(TABLE_PRODUCTS, $product);
        ZMRuntime::getDatabase()->updateModel(TABLE_PRODUCTS_DESCRIPTION, $product);

        // update cache
        $this->cache->remove(ZMTools::mkUnique('product', $product->getId(), $product->getLanguageId()));

        return $product;
    }

    /**
     * Update the view count for a product.
     *
     * @param int productId The product id.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     */
    public function updateViewCount($productId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "UPDATE " . TABLE_PRODUCTS_DESCRIPTION . "
                SET products_viewed = products_viewed+1
                WHERE products_id = :productId
                AND language_id = :languageId";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->update($sql, $args, TABLE_PRODUCTS_DESCRIPTION);
    }

    /**
     * Execute the given SQL and extract the resulting product ids.
     *
     * @param string sql Some SQL.
     * @param array args Optional query args; default is an empty array.
     * @param mixed tables Optional list of mapping table(s); default is <code>TABLE_PRODUCTS</code>.
     * @return array A list of product ids.
     */
    private function getProductIds($sql, $args=array(), $tables=TABLE_PRODUCTS) {
        $productIds = array();
        foreach (ZMRuntime::getDatabase()->query($sql, $args, $tables) as $result) {
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
     * @param mixed tables Optional list of mapping table(s); default is <code>TABLE_PRODUCTS</code>.
     * @return array A list of product ids.
     */
    private function getRandomProductIds($sql, $max=0, $args=array(), $tables=TABLE_PRODUCTS) {
        $productIds = array();
        foreach (ZMRuntime::getDatabase()->query($sql, $args, $tables) as $result) {
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
                from " . TABLE_PRODUCTS . "
                where products_id = :productId";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('productId' => $productId), TABLE_PRODUCTS);
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
     * @return array A list of <code>ZMProduct</code> instances.
     */
    public function getProductsForSQL($sql, $languageId=null) {
        $productIds = $this->getProductIds($sql);
        return $this->getProductsForIds($productIds, true, $languageId);
    }

}

?>
