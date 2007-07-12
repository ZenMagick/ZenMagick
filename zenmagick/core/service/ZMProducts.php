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
 * Product access.
 *
 * @author mano
 * @package net.radebatz.zenmagick.service
 * @version $Id$
 */
class ZMProducts extends ZMService {

    /**
     * Default c'tor.
     */
    function ZMProducts() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMProducts();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    // get products for category
    function getProductsForCategoryId($categoryId) {
    global $zm_runtime;

        $db = $this->getDB();
        $query = "select p.products_image, pd.products_name, p.products_id, p.manufacturers_id, p.products_model,
                p.products_price, p.products_priced_by_attribute, p.product_is_free, p.product_is_call,
                p.products_tax_class_id, pd.products_description,
                IF(s.status = 1, s.specials_new_products_price, NULL) as specials_new_products_price, 
                IF(s.status =1, s.specials_new_products_price, p.products_price) as final_price, p.products_sort_order, 
                p.product_is_call, p.product_is_always_free_shipping, p.products_qty_box_status
                from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " .
                TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id, " .
                TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS . " s on p2c.products_id = s.products_id
                where p.products_status = 1
                and p.products_id = p2c.products_id
                and pd.products_id = p2c.products_id
                and pd.language_id = :languageId
                and p2c.categories_id = :categoryId
                order by p.products_sort_order, pd.products_name";
        $query = $db->bindVars($query, ":languageId", $zm_runtime->getLanguageId(), "integer");
        $query = $db->bindVars($query, ":categoryId", $categoryId, "integer");

        $results = $db->Execute($query);

        $products = array();
        while (!$results->EOF) {
            $product = $this->_newProduct($results->fields);
            $products[$product->getId()] = $product;
            $results->MoveNext();
        }
        return $products;
    }


    // get products for manufacturer
    function getProductsForManufacturerId($manufacturerId) {
    global $zm_runtime;

        $db = $this->getDB();
        $query = "select p.products_image, pd.products_name, p.products_id, p.manufacturers_id, p.products_model,
                    p.products_price, p.products_priced_by_attribute, p.product_is_free, p.product_is_call,
                    p.products_tax_class_id, pd.products_description,
                    IF(s.status = 1, s.specials_new_products_price, NULL) as specials_new_products_price, 
                    IF(s.status = 1, s.specials_new_products_price, p.products_price) as final_price, p.products_sort_order, 
                    p.product_is_call, p.product_is_always_free_shipping, p.products_qty_box_status
                    from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " .
                    TABLE_PRODUCTS_DESCRIPTION . " pd, " .
                    TABLE_MANUFACTURERS . " m
                    where p.products_status = 1
                    and pd.products_id = p.products_id
                    and pd.language_id = :languageId
                    and p.manufacturers_id = m.manufacturers_id
                    and p.manufacturers_id = :manufacturerId
                    order by p.products_sort_order, pd.products_name";
        $query = $db->bindVars($query, ":manufacturerId", $manufacturerId, 'integer');
        $query = $db->bindVars($query, ":languageId", $zm_runtime->getLanguageId(), 'integer');
        $results = $db->Execute($query);

        $products = array();
        while (!$results->EOF) {
            $product = $this->_newProduct($results->fields);
            $products[$product->getId()] = $product;
            $results->MoveNext();
        }
        return $products;
    }


    // get all products for a given category (for filters)
    function getProductIdsForCategoryId($categoryId) {
        $db = $this->getDB();
        $query = "select p.products_id
                  from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                  where p.products_status = '1'
                  and p.products_id = p2c.products_id
                  and p2c.categories_id = :categoryId";
        $query = $db->bindVars($query, ":categoryId", $categoryId, 'integer');

        return $this->_getProductIds($query);
    }


    // product type options
    function getProductTypeSetting($productId, $field, $keyPprefix='_INFO', $keySuffix='SHOW_', $fieldPrefix= '_', $fieldSuffix='') {
        $db = $this->getDB();
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


    // get featured products
    function getFeaturedProducts($categoryId=null, $max=1) {
    global $zm_runtime;

        $db = $this->getDB();
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
        return $this->getProductsForIds($productIds);
    }


    // get new products
    // optional category limit, max, manual time limit in days (if globalLimit > 2); defaults to 120 days; 0 == use globalLimit
    function getNewProducts($categoryId=null, $max=0, $timeLimit=120) {
        $timeLimit = 0 == $timeLimit ? zm_setting('globalNewProductsLimit') : $timeLimit;

        $db = $this->getDB();
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
        return $this->getProductsForIds($productIds);
    }


    function getBestSellers($categoryId=null, $max=0) {
        $max = 0 == $max ? zm_setting('maxBestSellers') : $max;

        $db = $this->getDB();
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
        return $this->getProductsForIds($productIds);
    }


    // get specials
    function getSpecials($max=0) {
        $max = 0 == $max ? zm_setting('maxSpecialProducts') : $max;

        $db = $this->getDB();
        $sql = "select distinct p.products_id
                from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s
                where p.products_status = 1
                and p.products_id = s.products_id
                and s.status = 1
                limit :limit";
        $sql = $db->bindVars($sql, ":limit", $max, "integer");

        $productIds = $this->_getRandomProductIds($sql, $max);
        return $this->getProductsForIds($productIds);
    }


    function &getProductForModel($model) {
    global $zm_runtime;

        $db = $this->getDB();
        $sql = "select p.products_id, p.products_status, pd.products_name, pd.products_description, p.products_model,
                    p.products_quantity, p.products_image, pd.products_url, p.products_price,
                    p.products_tax_class_id, p.products_date_added, p.products_date_available, p.master_categories_id,
                    p.manufacturers_id, p.products_quantity, p.products_weight, p.products_priced_by_attribute,
                    p.product_is_call, p.product_is_free, p.products_qty_box_status, p.products_quantity_order_max,
                    p.products_quantity_order_min,
                    p.products_discount_type, p.products_discount_type_from, p.products_sort_order, p.products_price_sorter
                 from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                 where p.products_status = '1'
                 and p.products_model = :model
                 and pd.products_id = p.products_id
                 and pd.language_id = :languageId";
        $sql = $db->bindVars($sql, ":model", $model, "integer");
        $sql = $db->bindVars($sql, ":languageId", $zm_runtime->getLanguageId(), "integer");

        $results = $db->Execute($sql);

        if (0 == $results->RecordCount()) {
            return null;
        }

        return $this->_newProduct($results->fields);
    }


    // will load product with any status
    function &getProductForId($productId) {
    global $zm_runtime;

        $db = $this->getDB();
        $sql = "select p.products_id, p.products_status, pd.products_name, pd.products_description, p.products_model,
                    p.products_quantity, p.products_image, pd.products_url, p.products_price,
                    p.products_tax_class_id, p.products_date_added, p.products_date_available, p.master_categories_id,
                    p.manufacturers_id, p.products_quantity, p.products_weight, p.products_priced_by_attribute,
                    p.product_is_call, p.product_is_free, p.products_qty_box_status, p.products_quantity_order_max,
                    p.products_quantity_order_min,
                    p.products_discount_type, p.products_discount_type_from, p.products_sort_order, p.products_price_sorter
                 from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                 where p.products_id = :productId
                 and pd.products_id = p.products_id
                 and pd.language_id = :languageId";
        $sql = $db->bindVars($sql, ":productId", $productId, "integer");
        $sql = $db->bindVars($sql, ":languageId", $zm_runtime->getLanguageId(), "integer");

        $results = $db->Execute($sql);
        if (0 == $results->RecordCount()) {
            return null;
        }

        return $this->_newProduct($results->fields);
    }


    // will load products with any status
    function getProductsForIds($productIds) {
    global $zm_runtime;

        if (0 == count($productIds))
            return array();

        $db = $this->getDB();
        $sql = "select p.products_id, p.products_status, pd.products_name, pd.products_description, p.products_model,
                    p.products_quantity, p.products_image, pd.products_url, p.products_price,
                    p.products_tax_class_id, p.products_date_added, p.products_date_available, p.master_categories_id,
                    p.manufacturers_id, p.products_quantity, p.products_weight, p.products_priced_by_attribute,
                    p.product_is_call, p.product_is_free, p.products_qty_box_status, p.products_quantity_order_max,
                    p.products_quantity_order_min,
                    p.products_discount_type, p.products_discount_type_from, p.products_sort_order, p.products_price_sorter
                 from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                 where p.products_id in (:productIdList)
                 and pd.products_id = p.products_id
                 and pd.language_id = :languageId";
        $sql = $this->bindValueList($sql, ":productIdList", $productIds, "integer");
        $sql = $db->bindVars($sql, ":languageId", $zm_runtime->getLanguageId(), "integer");

        $results = $db->Execute($sql);

        $products = array();
        while (!$results->EOF) {
            $product = $this->_newProduct($results->fields);
            array_push($products, $product);
            $results->MoveNext();
        }

        return $products;
    }


    function updateViewCount($productId) {
    global $zm_runtime;

        if (null == $product)
            return;

        $db = $this->getDB();
        $sql = "update " . TABLE_PRODUCTS_DESCRIPTION . "
                set products_viewed = products_viewed+1
                where products_id = :productId
                and language_id = :languageId";
        $sql = $db->bindVars($sql, ":productId", $productId, "integer");
        $sql = $db->bindVars($sql, ":languageId", $zm_runtime->getLanguageId(), "integer");

        $result = $db->Execute($sql);
    }


    function _getProductIds($sql) {
        $db = $this->getDB();
        $results = $db->Execute($sql);

        $productIds = array();
        while (!$results->EOF) {
            $productId = $results->fields['products_id'];
            $productIds[$productId] = $productId;
            $results->MoveNext();
        }

        return $productIds;
    }


    function _getRandomProductIds($sql, $max=0) {
        0 == $max && zm_log("invalid max value: ".$max, ZM_LOG_DEBUG);

        $db = $this->getDB();
        $productIds = array();
        $left = $max;
        while (0 < $left) {
            $results = $db->ExecuteRandomMulti($sql, $left);
            if (0 == $results->RecordCount()) {
                break;
            }
            while (!$results->EOF) {
                // make sure we do not have duplicates
                $productIds[$results->fields['products_id']] = $results->fields['products_id'];
                $results->MoveNext();
                if ($max == count($productIds))
                    break;
            }
            $reminder = $max - count($productIds);
            if ($reminder == $left) {
                // can't find any more
                break;
            }
            $left = $reminder;
        }

        return $productIds;
    }


    // will load products that are found with teh given SQL
    function getProductsForSQL($sql) {
        $db = $this->getDB();
        $results = $db->Execute($sql);
        if (0 == $results->RecordCount()) {
            return null;
        }

        $productIds = array();
        while (!$results->EOF) {
            // make sure we do not have duplicates
            $productIds[$results->fields['products_id']] = $results->fields['products_id'];
            $results->MoveNext();
        }
        return $this->getProductsForIds($productIds);
    }


    function &_newProduct($fields) {
    global $zm_features;

        $product = $this->create("Product", $fields['products_id'], $fields['products_name'], $fields['products_description']);
        $product->status = $fields['products_status'];
        $product->model_ = $fields['products_model'];
        $product->image_ = $fields['products_image'];
        $product->url_ = $fields['products_url'];
        $product->dateAvailable_ = $fields['products_date_available'];
        $product->dateAdded_ = $fields['products_date_added'];
        $product->manufacturerId_ = $fields['manufacturers_id'];
        $product->weight_ = $fields['products_weight'];
        $product->quantity_ = $fields['products_quantity'];
        $product->qtyBoxStatus_ = $fields['products_qty_box_status'];
        $product->qtyOrderMin_ = $fields['products_quantity_order_min'];
        $product->qtyOrderMax_ = $fields['products_quantity_order_max'];
        $product->isFree_ = $fields['product_is_free'];
        $product->isCall_ = $fields['product_is_call'];
        $product->taxClassId_ = $fields['products_tax_class_id'];
        $product->discountType_ = $fields['products_discount_type'];
        $product->discountTypeFrom_ = $fields['products_discount_type_from'];
        $product->taxRate_ = zm_get_tax_rate($fields['products_tax_class_id']);
        $product->priceSorter_ = $fields['products_price_sorter'];
        $product->pricedByAttributes_ = $fields['products_priced_by_attribute'];
        $product->masterCategoryId_ = $fields['master_categories_id'];
        // raw price
        $product->price_ = $fields['products_price'] ? $fields['products_price'] : 0;
        // some magick
        $product->offers_ = $this->create("Offers", $product);
        $product->attributes_ = $this->create("Attributes", $product);
        //TODO
        $product->features_ = $zm_features->getFeaturesForProductId($product->getId());
        return $product;
    }

}

?>
