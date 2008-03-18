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


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->cache_ = array();
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
        return parent::instance('Products');
    }


    /**
     * Get all products.
     *
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    function getProducts($active=true, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $query = "select p.products_id
                    from " . TABLE_PRODUCTS . " p, " .  TABLE_PRODUCTS_DESCRIPTION . " pd
                    where ";
        if ($active) {
            $query .= " p.products_status = 1 and ";
        }
        $query .= " pd.products_id = p.products_id
                    and pd.language_id = :languageId
                    order by p.products_sort_order, pd.products_name";
        $query = $db->bindVars($query, ":languageId", $languageId, 'integer');
        $results = $db->Execute($query);

        $productIds = array();
        while (!$results->EOF) {
            $product = $this->_newProduct($results->fields);
            $productIds[] = $results->fields['products_id'];
            $results->MoveNext();
        }
        return $this->getProductsForIds($productIds, false, $languageId);
    }

    /**
     * Get all active products for the given category id.
     *
     * @param int categoryId The category id.
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    function getProductsForCategoryId($categoryId, $active=true, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $query = "select p.products_id
                from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " .  TABLE_PRODUCTS . " p, " .  TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                where ";
        if ($active) {
            $query .= " p.products_status = 1 and ";
        }
        $query .= " p.products_id = p2c.products_id and pd.products_id = p2c.products_id
                and pd.language_id = :languageId and p2c.categories_id = :categoryId
                order by p.products_sort_order, pd.products_name";
        $query = $db->bindVars($query, ":languageId", $languageId, "integer");
        $query = $db->bindVars($query, ":categoryId", $categoryId, "integer");

        $results = $db->Execute($query);

        $productIds = array();
        while (!$results->EOF) {
            $productIds[] = $results->fields['products_id'];
            $results->MoveNext();
        }

        return $this->getProductsForIds($productIds, false, $languageId);
    }


    /*
     * Get all active products for a manufacturer.
     *
     * @param int manufacturerId The manufacturers id.
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    function getProductsForManufacturerId($manufacturerId, $active=true, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $query = "select p.products_id
                    from " . TABLE_PRODUCTS . " p, " .  TABLE_PRODUCTS_DESCRIPTION . " pd, " .  TABLE_MANUFACTURERS . " m
                    where ";
        if ($active) {
            $query .= " p.products_status = 1 and ";
        }
        $query .= " pd.products_id = p.products_id
                    and pd.language_id = :languageId
                    and p.manufacturers_id = m.manufacturers_id and p.manufacturers_id = :manufacturerId
                    order by p.products_sort_order, pd.products_name";
        $query = $db->bindVars($query, ":manufacturerId", $manufacturerId, 'integer');
        $query = $db->bindVars($query, ":languageId", $languageId, 'integer');
        $results = $db->Execute($query);

        $productIds = array();
        while (!$results->EOF) {
            $product = $this->_newProduct($results->fields);
            $productIds[] = $results->fields['products_id'];
            $results->MoveNext();
        }
        return $this->getProductsForIds($productIds, false, $languageId);
    }


    /**
     * Get  list of all active product ids for a given category.
     *
     * <p>This is a faster version of <code>getProductsForCategoryId(int)</code>. In addition,
     * this will ignore language preferences.
     *
     * @param int categoryId The category id.
     * @param boolean active If <code>true</code> return only active products; default is <code>true</code>.
     * @return array A list of (int)product ids.
     */
    function getProductIdsForCategoryId($categoryId, $active=true) {
        $db = ZMRuntime::getDB();
        $query = "select p.products_id
                  from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                  where ";
        if ($active) {
            $query .= " p.products_status = 1 and ";
        }
        $query .= " p.products_id = p2c.products_id
                  and p2c.categories_id = :categoryId
                  order by p.products_sort_order";
        $query = $db->bindVars($query, ":categoryId", $categoryId, 'integer');

        return $this->_getProductIds($query);
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
    function getProductTypeSetting($productId, $field, $keyPprefix='_INFO', $keySuffix='SHOW_', $fieldPrefix= '_', $fieldSuffix='') {
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
     * Get featured products.
     *
     * @param int categoryId Optional category id to narrow down results; default is <code>null</code> for all.
     * @param int max The maximum number of results; default is <code>1</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    function getFeaturedProducts($categoryId=null, $max=1, $languageId=null) {
        $db = ZMRuntime::getDB();
		    $query = null;
        if (null == $categoryId || 0 == $categoryId) {
            $query = "select distinct p.products_id
                      from " . TABLE_PRODUCTS . " p 
                      left join " . TABLE_FEATURED . " f on p.products_id = f.products_id
                      where p.products_id = f.products_id 
                      and p.products_status = '1'
                      and f.status = '1'";
        } else {
            $query = "select distinct p.products_id
                     from (" . TABLE_PRODUCTS . " p
                     left join " . TABLE_FEATURED . " f on p.products_id = f.products_id), " .
                      TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " .
                      TABLE_CATEGORIES . " c
                     where p.products_id = p2c.products_id
                     and p2c.categories_id = c.categories_id
                     and c.parent_id = :categoryId
                     and p.products_id = f.products_id
                     and p.products_status = 1 and f.status = 1";
            $query = $db->bindVars($query, ":categoryId", $categoryId, "integer");
        }

        $productIds = 0 != $max ? $this->_getRandomProductIds($query, $max) : $this->_getProductIds($query);
        return $this->getProductsForIds($productIds, false, $languageId);
    }


    /**
     * Get new products.
     *
     * <p>Find products added in the past number of days specified by <code>$timeLimit</code>.</p>
     *
     * @param int categoryId Optional category id to narrow down results; default is <code>null</code> for all.
     * @param int max The maximum number of results; default is <code>1</code>.
     * @param int timeLimit Optional time limit in days; default is <em>120</em>, use <em>0</em> for no limit.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    function getNewProducts($categoryId=null, $max=0, $timeLimit=120, $languageId=null) {
        $timeLimit = 0 == $timeLimit ? zm_setting('globalNewProductsLimit') : $timeLimit;

        $db = ZMRuntime::getDB();
        $queryLimit = '';
        switch (zm_setting('globalNewProductsLimit')) {
            case '0':
                // no global limit
                $queryLimit = '';
                break;
            case '1':
                // global limit of one date
                $newDate = date('Ym', time()) . '01';
                $queryLimit = $db->bindVars(' and p.products_date_added >= :date', ':date', $date, "date");
                break;
            default:
                // 120 days; 24 hours; 60 mins; 60secs
                $dateRange = time() - ($timeLimit * 24 * 60 * 60);
                $newDate = date('Ymd', $dateRange);
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
                      left join " . TABLE_SPECIALS . " s
                      on p.products_id = s.products_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " .  TABLE_CATEGORIES . " c
                      where p.products_id = p2c.products_id
                      and p2c.categories_id = c.categories_id
                      and c.parent_id = :categoryId
                      and p.products_status = 1" . $queryLimit;
            $query = $db->bindVars($query, ":categoryId", $categoryId, "integer");
        }
        $query .= " order by products_date_added";

        $productIds = 0 != $max ? $this->_getRandomProductIds($query, $max) : $this->_getProductIds($query);
        return $this->getProductsForIds($productIds, false, $languageId);
    }


    /**
     * Get best seller products.
     *
     * @param int categoryId Optional category id to narrow down results; default is <code>null</code> for all.
     * @param int max The maximum number of results; default is <code>1</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    function getBestSellers($categoryId=null, $max=0, $languageId=null) {
        $max = 0 == $max ? zm_setting('maxBestSellers') : $max;

        $db = ZMRuntime::getDB();
        $query = null;
        if (null != $categoryId) {
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
            $query = $db->bindVars($query, ":limit", $max, "integer");
        } else {
            $query = "select distinct p.products_id, p.products_ordered
                      from " . TABLE_PRODUCTS . " p
                      where p.products_status = '1'
                      and p.products_ordered > 0
                      order by p.products_ordered desc
                      limit :limit";
            $query = $db->bindVars($query, ":limit", $max, "integer");
        }

        $results = $db->Execute($query);

        $productIds = $this->_getProductIds($query);
        return $this->getProductsForIds($productIds, false, $languageId);
    }


    /**
     * Get products marked as specials.
     *
     * @param int max The maximum number of results; default is <code>1</code>.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    function getSpecials($max=0, $languageId=null) {
        $max = 0 == $max ? zm_setting('maxSpecialProducts') : $max;

        $db = ZMRuntime::getDB();
        $sql = "select distinct p.products_id
                from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s
                where p.products_status = 1
                and p.products_id = s.products_id
                and s.status = 1
                limit :limit";
        $sql = $db->bindVars($sql, ":limit", $max, "integer");

        $productIds = $this->_getRandomProductIds($sql, $max);
        return $this->getProductsForIds($productIds, false, $languageId);
    }


    /**
     * Get a product for the given model name.
     *
     * @param string model The model name.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return ZMProduct The product or <code>null</code>.
     */
    function getProductForModel($model, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "select p.products_id, p.products_status, pd.products_name, pd.products_description, p.products_model,
                    p.products_image, pd.products_url, p.products_price,
                    p.products_tax_class_id, p.products_date_added, p.products_date_available, p.master_categories_id,
                    p.manufacturers_id, p.products_quantity, p.products_weight, p.products_priced_by_attribute,
                    p.product_is_call, p.product_is_free, p.products_qty_box_status, p.products_quantity_order_max,
                    p.products_quantity_order_min, p.products_quantity_mixed,
                    p.products_discount_type, p.products_discount_type_from, p.products_sort_order, p.products_price_sorter
                 ".ZMDbUtils::getCustomFieldsSQL(TABLE_PRODUCTS, 'p')."
                 from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                 where p.products_status = '1'
                 and p.products_model = :model
                 and pd.products_id = p.products_id
                 and pd.language_id = :languageId";
        $sql = $db->bindVars($sql, ":model", $model, "string");
        $sql = $db->bindVars($sql, ":languageId", $languageId, "integer");

        $results = $db->Execute($sql);

        if (0 == $results->RecordCount()) {
            return null;
        }

        return $this->_newProduct($results->fields);
    }


    /**
     * Get a product for the given product id.
     *
     * @param int productId The product id.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return ZMProduct The product or <code>null</code>.
     */
    function getProductForId($productId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "select p.products_id, p.products_status, pd.products_name, pd.products_description, p.products_model,
                    p.products_image, pd.products_url, p.products_price,
                    p.products_tax_class_id, p.products_date_added, p.products_date_available, p.master_categories_id,
                    p.manufacturers_id, p.products_quantity, p.products_weight, p.products_priced_by_attribute,
                    p.product_is_call, p.product_is_free, p.products_qty_box_status, p.products_quantity_order_max,
                    p.products_quantity_order_min, p.products_quantity_mixed,
                    p.products_discount_type, p.products_discount_type_from, p.products_sort_order, p.products_price_sorter
                 ".ZMDbUtils::getCustomFieldsSQL(TABLE_PRODUCTS, 'p')."
                 from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                 where p.products_id = :productId
                 and pd.products_id = p.products_id
                 and pd.language_id = :languageId";
        $sql = $db->bindVars($sql, ":productId", $productId, "integer");
        $sql = $db->bindVars($sql, ":languageId", $languageId, "integer");

        $results = $db->Execute($sql);
        if (0 == $results->RecordCount()) {
            return null;
        }

        return $this->_newProduct($results->fields);
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
    function getProductsForIds($productIds, $preserveOrder=false, $languageId=null) {
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
            $db = ZMRuntime::getDB();
            $sql = "select p.products_id, p.products_status, pd.products_name, pd.products_description, p.products_model,
                        p.products_image, pd.products_url, p.products_price,
                        p.products_tax_class_id, p.products_date_added, p.products_date_available, p.master_categories_id,
                        p.manufacturers_id, p.products_quantity, p.products_weight, p.products_priced_by_attribute,
                        p.product_is_call, p.product_is_free, p.products_qty_box_status, p.products_quantity_order_max,
                        p.products_quantity_order_min, p.products_quantity_mixed,
                        p.products_discount_type, p.products_discount_type_from, p.products_sort_order, p.products_price_sorter
                     ".ZMDbUtils::getCustomFieldsSQL(TABLE_PRODUCTS, 'p')."
                     from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                     where p.products_id in (:productIdList)
                     and pd.products_id = p.products_id
                     and pd.language_id = :languageId";
            if (!$preserveOrder) {
                $sql .= " order by p.products_sort_order, pd.products_name";
            }
            $sql = ZMDbUtils::bindValueList($sql, ":productIdList", $needLoadIds, "integer");
            $sql = $db->bindVars($sql, ":languageId", $languageId, "integer");

            $results = $db->Execute($sql);

            while (!$results->EOF) {
                $product = $this->_newProduct($results->fields);
                $products[] = $product;
                // put in cache
                $this->cache_[$product->getId()] = $product;
                $results->MoveNext();
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
     * <p><strong>NOTE: Currently not all properties are supported!</strong></p>
     *
     * @param ZMProduct The product.
     * @return ZMProduct The updated product.
     */
    function updateProduct($product) {
        $db = ZMRuntime::getDB();
        $sql = "update " . TABLE_PRODUCTS . " set
                :customFields,
                products_status = :status;integer,
                products_model = :model;string,
                products_image = :defaultImage;string,
                products_quantity = :quantity;integer,
                products_price = :productPrice;float,
                products_tax_class_id = :taxClassId;integer,
                products_date_added = :dateAdded;date,
                products_date_available = :dateAvailable;date,
                master_categories_id = :masterCategoryId;integer,
                manufacturers_id = :manufacturerId;integer,
                products_weight = :weight;float,
                product_is_call = :call;integer,
                product_is_free = :free;integer,
                products_qty_box_status = :qtyBoxStatus;integer,
                products_quantity_order_max = :maxOrderQty;integer,
                products_quantity_order_min = :minOrderQty;integer,
                products_quantity_mixed = :qtyMixed;integer,
                products_discount_type = :discountType;integer,
                products_discount_type_from = :discountTypeFrom;integer,
                products_sort_order = :sortOrder;integer,
                products_price_sorter = :priceSorter;integer
                where products_id = :productId";
        $sql = $db->bindVars($sql, ":productId", $product->getId(), "integer");
        $sql = ZMDbUtils::bindObject($sql, $product, false);
        $sql = ZMDbUtils::bindCustomFields($sql, $product, TABLE_PRODUCTS);
        $db->Execute($sql);

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
    function updateViewCount($productId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        if (null == $product)
            return;

        $db = ZMRuntime::getDB();
        $sql = "update " . TABLE_PRODUCTS_DESCRIPTION . "
                set products_viewed = products_viewed+1
                where products_id = :productId
                and language_id = :languageId";
        $sql = $db->bindVars($sql, ":productId", $productId, "integer");
        $sql = $db->bindVars($sql, ":languageId", $languageId, "integer");

        $result = $db->Execute($sql);
    }


    /**
     * Execute the given SQL and extract the resulting product ids.
     *
     * @param string sql Some SQL.
     * @return array A list of product ids.
     */
    function _getProductIds($sql) {
        $db = ZMRuntime::getDB();
        $results = $db->Execute($sql);

        $productIds = array();
        while (!$results->EOF) {
            $productId = $results->fields['products_id'];
            $productIds[$productId] = $productId;
            $results->MoveNext();
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
    function _getRandomProductIds($sql, $max=0) {
        $db = ZMRuntime::getDB();
        $productIds = array();
        $results = $db->Execute($sql);
        while (!$results->EOF) {
            // make sure we do not have duplicates
            $productIds[$results->fields['products_id']] = $results->fields['products_id'];
            $results->MoveNext();
        }

        shuffle($productIds);

        if (0 < $max && count($productIds) > $max) {
            $productIds = array_slice($productIds, 0, $max);
        }

        return $productIds;
    }


    /**
     * Execute the given SQL and return the resulting product.
     *
     * @param string sql Some SQL.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array A list of <code>ZMProduct</code> instances.
     */
    function getProductsForSQL($sql, $languageId=null) {
        $db = ZMRuntime::getDB();
        $results = $db->Execute($sql);
        if (0 == $results->RecordCount()) {
            return null;
        }

        $productIds = array();
        while (!$results->EOF) {
            $productIds[] = $results->fields['products_id'];
            $results->MoveNext();
        }

        return $this->getProductsForIds($productIds, true, $languageId);
    }


    /**
     * Create new product instance.
     */
    function _newProduct($fields) {
        $product = $this->create("Product", $fields['products_id'], $fields['products_name'], $fields['products_description']);
        $product->status_ = $fields['products_status'];
        $product->model_ = $fields['products_model'];
        $product->image_ = $fields['products_image'];
        $product->url_ = $fields['products_url'];
        $product->dateAvailable_ = $fields['products_date_available'];
        $product->dateAdded_ = $fields['products_date_added'];
        $product->manufacturerId_ = $fields['manufacturers_id'];
        $product->weight_ = $fields['products_weight'];
        $product->quantity_ = $fields['products_quantity'];
        $product->qtyMixed_ = $fields['products_quantity_mixed'] == '1';
        $product->qtyBoxStatus_ = $fields['products_qty_box_status'];
        $product->qtyOrderMin_ = $fields['products_quantity_order_min'];
        $product->qtyOrderMax_ = $fields['products_quantity_order_max'];
        $product->isFree_ = $fields['product_is_free'];
        $product->isCall_ = $fields['product_is_call'];
        $product->taxClassId_ = $fields['products_tax_class_id'];
        $product->discountType_ = $fields['products_discount_type'];
        $product->discountTypeFrom_ = $fields['products_discount_type_from'];
        $product->priceSorter_ = $fields['products_price_sorter'];
        $product->pricedByAttributes_ = $fields['products_priced_by_attribute'];
        $product->masterCategoryId_ = $fields['master_categories_id'];
        $product->sortOrder_ = $fields['products_sort_order'];
        // the raw price
        $product->productPrice_ = $fields['products_price'] ? $fields['products_price'] : 0;

        // custom fields
        foreach (ZMDbUtils::getCustomFields(TABLE_PRODUCTS) as $field) {
            if (isset($fields[$field[0]])) {
                $product->set($field[0], $fields[$field[0]]);
            }
        }

        return $product;
    }

}

?>
