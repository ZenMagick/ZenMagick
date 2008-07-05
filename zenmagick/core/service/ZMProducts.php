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
 * Product access.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMProducts extends ZMObject {
    private $cache_;
    private $categoryProductMap_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->cache_ = array();
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
     * Get all products.
     *
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    public function getProducts($active=true, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $query = "SELECT p.products_id
                  FROM " . TABLE_PRODUCTS . " p, " .  TABLE_PRODUCTS_DESCRIPTION . " pd
                  WHERE ";
        if ($active) {
            $query .= " p.products_status = 1 and ";
        }
        $query .= " pd.products_id = p.products_id
                      AND pd.language_id = :languageId
                    ORDER BY p.products_sort_order, pd.products_name";
        $results = ZMRuntime::getDatabase()->query($sql, array('lanugageId' => $languageId), array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION));
        $productIds = array();
        foreach ($results as $result) {
            $productIds[] = $result['id'];
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
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of product ids.
     */
    public function getProductIdsForCategoryId($categoryId, $active=true, $languageId=null) {
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
                $cId = $keyPrefix.$result['categoryId'];
                if (!isset($this->categoryProductMap_[$mainKey][$cId])) {
                    $this->categoryProductMap_[$mainKey][$cId] = array();
                }
                $this->categoryProductMap_[$mainKey][$cId][] = $result['id'];
            }
        }

        return isset($this->categoryProductMap_[$mainKey][$categoryId]) ? $this->categoryProductMap_[$mainKey][$categoryId] : array();
    }

    /**
     * Get all active products for the given category id.
     *
     * @param int categoryId The category id.
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    public function getProductsForCategoryId($categoryId, $active=true, $languageId=null) {
        return $this->getProductsForIds($this->getProductIdsForCategoryId($categoryId, $active, $languageId));
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
            $productIds[] = $result['id'];
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
        $db = ZMRuntime::getDB();
        $sql = "select products_type from " . TABLE_PRODUCTS . "
                where products_id = :productId";
        $sql = $db->bindVars($sql, ":productId", $productId, 'integer');
        $typeResults = $db->Execute($sql);

        $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . "
                where type_id = :typeId";
        $sql = $db->bindVars($sql, ":typeId", $typeResults->fields['products_type'], 'integer');
        $keyResults = $db->Execute($sql);

        $key = strtoupper($keySuffix . $keyResults->fields['type_handler'] . $keyPprefix . $fieldPrefix . $field . $fieldSuffix);

        $sql = "select configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . "
                where configuration_key = :key";
        $sql = $db->bindVars($sql, ":key", $key, 'string');
        $valueResults = $db->Execute($sql);

        if ($valueResults->RecordCount() > 0) {
            // type result
            return 1 == $valueResults->fields['configuration_value'];
        } else {
            // fallback general configuration
            $sql = "select configuration_value from " . TABLE_CONFIGURATION . "
                    where configuration_key = :key";
            $sql = $db->bindVars($sql, ":typeId", $key, 'string');
            $valueResults = $db->Execute($sql);

            if ($valueResults->RecordCount() > 0) {
                return 1 == $valueResults->fields['configuration_value'];
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
        $db = ZMRuntime::getDB();

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
            $sql = $db->bindVars($sql, ":categoryId", $categoryId, "integer");
        }

        $productIds = 0 != $max ? $this->getRandomProductIds($sql, $max) : $this->getProductIds($sql);
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

        $db = ZMRuntime::getDB();
        $queryLimit = '';
        switch ($timeLimit) {
            case '0':
                // no global limit
                $queryLimit = '';
                break;
            case '1':
                // this month
                $date = date('Ym', time()) . '01';
                $queryLimit = $db->bindVars(' and p.products_date_added >= :date', ':date', $date, "date");
                break;
            default:
                // X days; 24 hours; 60 mins; 60secs
                $dateRange = time() - ($timeLimit * 24 * 60 * 60);
                $date = date('Ymd', $dateRange);
                $queryLimit = $db->bindVars(' and p.products_date_added >= :date', ':date', $date, "date");
                break;
        }

        $query = null;
        if (null == $categoryId) {
            $query = "select p.products_id
                      from " . TABLE_PRODUCTS . " p
                      where p.products_status = 1" . $queryLimit;
        } else {
            $query = "select distinct p.products_id
                      from " . TABLE_PRODUCTS . " p
                      on p.products_id = s.products_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " .  TABLE_CATEGORIES . " c
                      where p.products_id = p2c.products_id
                      and p2c.categories_id = c.categories_id
                      and c.categories_id = :categoryId
                      and p.products_status = 1" . $queryLimit;
            $query = $db->bindVars($query, ":categoryId", $categoryId, "integer");
        }
        $query .= " order by products_date_added";

        $productIds = 0 !== $max ? $this->getRandomProductIds($query, $max) : $this->getProductIds($query);
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

        $db = ZMRuntime::getDB();
        $query = null;
        if (null !== $categoryId) {
            $query = "select distinct p.products_id
                      from " . TABLE_PRODUCTS . " p, "
                      . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c 
                      where p.products_status = '1'
                      and p.products_ordered > 0
                      and p.products_id = p2c.products_id
                      and p2c.categories_id = c.categories_id
                      and :categoryId in (c.categories_id, c.parent_id)
                      order by p.products_ordered desc
                      limit :limit";
            $query = $db->bindVars($query, ":categoryId", $categoryId, "integer");
        } else {
            $query = "select distinct p.products_id, p.products_ordered
                      from " . TABLE_PRODUCTS . " p
                      where p.products_status = '1'
                      and p.products_ordered > 0
                      order by p.products_ordered desc
                      limit :limit";
        }
        $query = $db->bindVars($query, ":limit", $max, "integer");

        $productIds = $this->getProductIds($query);
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

        $sql = "SELECT *
                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE p.products_status = '1'
                  AND p.products_model = :model
                  AND pd.products_id = p.products_id
                  AND pd.language_id = :languageId";
        $args = array('model' => $model, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION), 'Product');
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

        $sql = "SELECT *
                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE p.products_id = :id
                  AND pd.products_id = p.products_id
                  AND pd.language_id = :languageId";
        $args = array('id' => $productId, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION), 'Product');
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
            if (isset($this->cache_[$id])) {
                $products[] = $this->cache_[$id];
            } else {
                $needLoadIds[$id] = $id;
            }
        }

        if (0 < count($needLoadIds)) {
            $sql = "SELECT *
                    FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    WHERE p.products_id in (:id)
                      AND pd.products_id = p.products_id
                      AND pd.language_id = :languageId";
            if (!$preserveOrder) {
                $sql .= " ORDER BY p.products_sort_order, pd.products_name";
            }
            $args = array('id' => $needLoadIds, 'languageId' => $languageId);
            $results = ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION), 'Product');
            foreach ($results as $product) {
                $products[] = $product;
                // put in cache
                $this->cache_[$product->getId()] = $product;
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

        // update cache
        $this->cache_[$product->getId()] = $product;

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
                WHERE products_id = :id
                AND language_id = :languageId";
        $args = array('id' => $productId, 'languageId' => $languageId);
        return ZMRuntime::getDatabase()->update($sql, $args, TABLE_PRODUCTS_DESCRIPTION);
    }

    /**
     * Execute the given SQL and extract the resulting product ids.
     *
     * @param string sql Some SQL.
     * @return array A list of product ids.
     */
    private function getProductIds($sql) {
        $productIds = array();
        foreach (ZMRuntime::getDatabase()->query($sql, array(), TABLE_PRODUCTS) as $result) {
            $productId = $result['id'];
            $productIds[] = $productId;
        }

        return $productIds;
    }


    /**
     * Get some random product ids.
     *
     * @param string sql Some SQL.
     * @param int max The maximum number of results; default is <em>0</em> for all.
     * @return array A list of product ids.
     */
    private function getRandomProductIds($sql, $max=0) {
        $productIds = array();
        foreach (ZMRuntime::getDatabase()->query($sql, array(), TABLE_PRODUCTS) as $result) {
            $productId = $result['id'];
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
                where products_id = :id";
        $result = ZMRuntime::getDatabase()->querySingle($sql, array('id' => $productId), TABLE_PRODUCTS);
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
