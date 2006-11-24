<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMProducts {
    // db access
    var $db_;


    // create new instance
    function ZMProducts() {
    global $zm_runtime;
        $this->db_ = $zm_runtime->getDB();
    }

    // create new instance
    function __construct() {
        $this->ZMProducts();
    }

    function __destruct() {
    }


    // get products for category
    function getProductsForCategoryId($categoryId) {
    global $zm_request;
        $query = "select p.products_image, pd.products_name, p.products_id, p.manufacturers_id, p.products_model,
                  p.products_price, p.products_priced_by_attribute, p.product_is_free, p.product_is_call,
                  p.products_tax_class_id, pd.products_description,
                  IF(s.status = '1', s.specials_new_products_price, NULL) as specials_new_products_price,
                  IF(s.status ='1', s.specials_new_products_price, p.products_price) as final_price, p.products_sort_order
                  from ". TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p
                  left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id, " .
                  TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id
                  where p.products_status = '1' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id
                  and pd.language_id = :languageId
                  and p2c.categories_id = :categoryId
                  order by p.products_sort_order, pd.products_name";
        $query = $this->db_->bindVars($query, ":productId", $zm_request->getLanguageId(), "integer");
        $query = $this->db_->bindVars($query, ":categoryId", $categoryId, "integer");

        $results = $this->db_->Execute($query);

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
    global $zm_request;
        $query = "select p.products_image, pd.products_name, p.products_id, p.manufacturers_id, p.products_model,
                    p.products_price, p.products_priced_by_attribute, p.product_is_free, p.product_is_call,
                    p.products_tax_class_id, pd.products_description,
                    IF(s.status = '1', s.specials_new_products_price, NULL) as specials_new_products_price,
                    IF(s.status ='1', s.specials_new_products_price, p.products_price) as final_price, p.products_sort_order
                  from ". TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p
                    left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id, " .
                    TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id
                  where p.products_status = '1' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id
                    and pd.language_id = :languageId
                    and p.manufacturers_id = :manufacturerId
                  order by p.products_sort_order, pd.products_name";
        $query = $this->db_->bindVars($query, ":manufacturerId", $manufacturerId, 'integer');
        $query = $this->db_->bindVars($query, ":languageId", $zm_request->getLanguageId(), 'integer');
        $results = $this->db_->Execute($query);

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
        $query = "select p.products_id
                  from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                  where p.products_status = '1'
                  and p.products_id = p2c.products_id
                  and p2c.categories_id = :categoryId";
        $query = $this->db_->bindVars($query, ":categoryId", $categoryId, 'integer');
        $results = $this->db_->Execute($query);

        $productIds = array();
        while (!$results->EOF) {
            $productId = $results->fields['products_id'];
            $productIds[$productId] = $productId;
            $results->MoveNext();
        }
        return $productIds;
    }


    // product type options
    function getProductTypeSetting($productId, $field, $keyPprefix='_INFO', $keySuffix='SHOW_', $fieldPrefix= '_', $fieldSuffix='') {
        $sql = "select products_type from " . TABLE_PRODUCTS . "
                where products_id = :productId";
        $sql = $this->db_->bindVars($sql, ":productId", $productId, 'integer');
        $typeResults = $this->db_->Execute($sql);

        $sql = "select type_handler from " . TABLE_PRODUCT_TYPES . "
                where type_id = :typeId";
        $sql = $this->db_->bindVars($sql, ":typeId", $typeResults->fields['products_type'], 'integer');
        $keyResults = $this->db_->Execute($sql);

        $key = strtoupper($keySuffix . $keyResults->fields['type_handler'] . $keyPprefix . $fieldPrefix . $field . $fieldSuffix);

        $sql = "select configuration_value from " . TABLE_PRODUCT_TYPE_LAYOUT . "
                where configuration_key = :key";
        $sql = $this->db_->bindVars($sql, ":key", $key, 'string');
        $valueResults = $this->db_->Execute($sql);

        if ($valueResults->RecordCount() > 0) {
            // type result
            return 1 == $valueResults->fields['configuration_value'];
        } else {
            // fallback general configuration
            $sql = "select configuration_value from " . TABLE_CONFIGURATION . "
                    where configuration_key = :key";
            $sql = $this->db_->bindVars($sql, ":typeId", $key, 'string');
            $valueResults = $this->db_->Execute($sql);

            if ($valueResults->RecordCount() > 0) {
                return 1 == $valueResults->fields['configuration_value'];
            }
        }
        return false;
    }


    // get featured products
    function getFeaturedProducts($categoryId=null, $max=1) {
    global $zm_request;
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
            $query = $this->db_->bindVars($query, ":categoryId", $categoryId, "integer");
        }

        $productIds = array();
        $left = $max;
        while (0 < $left) {
            $results = $this->db_->ExecuteRandomMulti($query, $left);
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
        return $this->getProductsForIds($productIds);
    }


    // get new products
    function getNewProducts($max=1) {
        $queryLimit = '';
        switch (zm_setting('newProductsLimit')) {
            case '0':
                $queryLimit = '';
                break;
            case '1':
                $queryLimit = " and date_format(p.products_date_added, '%Y%m') >= date_format(now(), '%Y%m')";
                break;
            default:
                $queryLimit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= '.zm_setting('newProductsLimit');
                break;
        }
        $query = "select p.products_id
                  from " . TABLE_PRODUCTS . " p
                  where p.products_status = '1' " . $queryLimit . "
                  limit :limit"; zm_setting('maxNewProducts');
        $query = $this->db_->bindVars($query, ":limit", zm_setting('maxNewProducts'), "integer");

        // productIds
        $productIds = array();
        $left = $max;
        while ($max > count($productIds)) {
            $results = $this->db_->ExecuteRandomMulti($query, $max);
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
        return $this->getProductsForIds($productIds);
    }


    function getBestSellers($categoryId, $max=0) {
        $max = 0 == $max ? zm_setting('maxBestSellers') : $max;

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
            $query = $this->db_->bindVars($query, ":categoryId", $categoryId, "integer");
            $query = $this->db_->bindVars($query, ":limit", $max, "integer");
        } else {
            $query = "select distinct p.products_id, p.products_ordered
                      from " . TABLE_PRODUCTS . " p
                      where p.products_status = '1'
                      and p.products_ordered > 0
                      order by p.products_ordered desc
                      limit :limit";
            $query = $this->db_->bindVars($query, ":limit", $max, "integer");
        }
        $results = $this->db_->Execute($query);

        $productIds = array();
        while (!$results->EOF) {
            // make sure we do not have duplicates
            $productIds[$results->fields['products_id']] = $results->fields['products_id'];
            $results->MoveNext();
        }
        return $this->getProductsForIds($productIds);
    }


    // get specials
    function getSpecials($max=0) {
        $max = 0 == $max ? zm_setting('maxSpecialProducts') : $max;

        $sql = "select distinct p.products_id
                from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s
                where p.products_status = 1
                and p.products_id = s.products_id
                and s.status = 1
                limit :limit";
        $sql = $this->db_->bindVars($sql, ":limit", $max, "integer");

        // productIds
        $productIds = array();
        $left = $max;
        while ($max > count($productIds)) {
            $results = $this->db_->ExecuteRandomMulti($sql, $max);
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
        return $this->getProductsForIds($productIds);
    }


    function getProductForModel($model) {
    global $zm_request;
        $sql = "select p.products_id, p.products_status, pd.products_name, pd.products_description, p.products_model,
                    p.products_quantity, p.products_image, pd.products_url, p.products_price,
                    p.products_tax_class_id, p.products_date_added, p.products_date_available, p.master_categories_id,
                    p.manufacturers_id, p.products_quantity, p.products_weight, p.products_priced_by_attribute,
                    p.product_is_call, p.product_is_free, p.products_qty_box_status, p.products_quantity_order_max,
                    p.products_discount_type, p.products_discount_type_from, p.products_sort_order, p.products_price_sorter
                 from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                 where p.products_status = '1'
                 and p.products_model = :model
                 and pd.products_id = p.products_id
                 and pd.language_id = :languageId";
        $sql = $this->db_->bindVars($sql, ":model", $model, "integer");
        $sql = $this->db_->bindVars($sql, ":languageId", $zm_request->getLanguageId(), "integer");
        $results = $this->db_->Execute($sql);

        if (0 == $results->RecordCount()) {
            return null;
        }

        return $this->_newProduct($results->fields);
    }


    // will load product with any status
    function getProductForId($productId) {
    global $zm_request;
        $sql = "select p.products_id, p.products_status, pd.products_name, pd.products_description, p.products_model,
                    p.products_quantity, p.products_image, pd.products_url, p.products_price,
                    p.products_tax_class_id, p.products_date_added, p.products_date_available, p.master_categories_id,
                    p.manufacturers_id, p.products_quantity, p.products_weight, p.products_priced_by_attribute,
                    p.product_is_call, p.product_is_free, p.products_qty_box_status, p.products_quantity_order_max,
                    p.products_discount_type, p.products_discount_type_from, p.products_sort_order, p.products_price_sorter
                 from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                 where p.products_id = :productId
                 and pd.products_id = p.products_id
                 and pd.language_id = :languageId";
        $sql = $this->db_->bindVars($sql, ":productId", $productId, "integer");
        $sql = $this->db_->bindVars($sql, ":languageId", $zm_request->getLanguageId(), "integer");

        $results = $this->db_->Execute($sql);
        if (0 == $results->RecordCount()) {
            return null;
        }

        return $this->_newProduct($results->fields);
    }


    // will load products with any status
    function getProductsForIds($productIds) {
    global $zm_request;
        if (0 == count($productIds))
            return array();

        //XXX TODO bindVars
        $sql = "select p.products_id, p.products_status, pd.products_name, pd.products_description, p.products_model,
                    p.products_quantity, p.products_image, pd.products_url, p.products_price,
                    p.products_tax_class_id, p.products_date_added, p.products_date_available, p.master_categories_id,
                    p.manufacturers_id, p.products_quantity, p.products_weight, p.products_priced_by_attribute,
                    p.product_is_call, p.product_is_free, p.products_qty_box_status, p.products_quantity_order_max,
                    p.products_discount_type, p.products_discount_type_from, p.products_sort_order, p.products_price_sorter
                 from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                 where p.products_id in (" . zm_db_array($productIds) . ")
                 and pd.products_id = p.products_id
                 and pd.language_id = :languageId";
        //XXX$sql = $this->db_->bindVars($sql, ":productId", $productId, "integer");
        $sql = $this->db_->bindVars($sql, ":languageId", $zm_request->getLanguageId(), "integer");
        $results = $this->db_->Execute($sql);

        $products = array();
        while (!$results->EOF) {
            $product = $this->_newProduct($results->fields);
            array_push($products, $product);
            $results->MoveNext();
        }

        return $products;
    }


    function updateViewCount($productId) {
    global $zm_request;
        if (null == $product)
            return;

        $sql = "update " . TABLE_PRODUCTS_DESCRIPTION . "
                set products_viewed = products_viewed+1
                where products_id = :productId
                and language_id = :languageId";
        $sql = $this->db_->bindVars($sql, ":productId", $productId, "integer");
        $sql = $this->db_->bindVars($sql, ":languageId", $zm_request->getLanguageId(), "integer");
        $result = $this->db_->Execute($sql);
    }


    function _newProduct($fields) {
    global $zm_features;
        $product = new ZMProduct($fields['products_id'], $fields['products_name'], $fields['products_description']);
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
        $product->offers_ = new ZMOffers($product);
        $product->attributes_ = new ZMAttributes($product);
        //TODO
        $product->features_ = $zm_features->getFeaturesForProductId($product->getId());
        return $product;
    }

}

?>
