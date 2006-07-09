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
                  and pd.language_id = '".$zm_request->getLanguageId()."' and p2c.categories_id = '".$categoryId."'
                  order by p.products_sort_order, pd.products_name";
        $results = $this->db_->Execute($query);
        // product hash
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
                  and pd.language_id = '".$zm_request->getLanguageId()."' and p.manufacturers_id = '".$manufacturerId."'
                  order by p.products_sort_order, pd.products_name";
        $results = $this->db_->Execute($query);
        // product hash
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
                  and p2c.categories_id = '" . $categoryId . "'";
        $results = $this->db_->Execute($query);
        // productIds
        $productIds = array();
        while (!$results->EOF) {
            $productId = $results->fields['products_id'];
            $productIds[$productId] = $productId;
            $results->MoveNext();
        }
        return $productIds;
    }


    // get featured products
    function getFeaturedProducts($categoryId = null, $max = 1) {
    global $zm_request;
		$query = null;
        if (null == $categoryId || 0 == $categoyId) {
            $query = "select distinct p.products_id
                      from " . TABLE_PRODUCTS . " p 
                      left join " . TABLE_FEATURED . " f on p.products_id = f.products_id
                      where p.products_id = f.products_id 
                      and p.products_status = '1'
                      and f.status = '1'";
        } else {
            $query = "select distinct p.products_id
                      from " . TABLE_PRODUCTS . " p
                      left join " . TABLE_FEATURED . " f on p.products_id = f.products_id " .
                       TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                      where p.products_id = p2c.products_id
                      and c.parent_id = '" . $categoryId . "'
                      and p.products_id = f.products_id
                      and p.products_status = '1'
                      and f.status = '1'";
        }

        $productIds = array();
        $left = $max;
        while (0 < $left) {
            $results = $this->db_->ExecuteRandomMulti($query, $left);
            if (0 == $results->RecordCount()) {
                echo "no results; left = $left, results: " . count ($productIds);
                break;
            }
            while (!$results->EOF) {
                // make sure we do not have duplicates
                $productIds[$results->fields['products_id']] = $results->fields['products_id'];
                $results->MoveNext();
                if ($max == count($productIds))
                    break;
            }
            $left = $max - count($productIds);
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
                  limit " . zm_setting('maxNewProducts');

        // productIds
        $productIds = array();
        while ($max > count($productIds)) {
            $results = $this->db_->ExecuteRandomMulti($query, $max);
            if (0 == $results->RecordCount())
                break;
            while (!$results->EOF) {
                // make sure we do not have duplicates
                $productIds[$results->fields['products_id']] = $results->fields['products_id'];
                $results->MoveNext();
                if ($max == count($productIds))
                    break;
            }
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
                      and '" . $categoryId . "' in (c.categories_id, c.parent_id)
                      order by p.products_ordered desc
                      limit " . $max;
        } else {
            $query = "select distinct p.products_id, p.products_ordered
                      from " . TABLE_PRODUCTS . " p
                      where p.products_status = '1'
                      and p.products_ordered > 0
                      order by p.products_ordered desc
                      limit " . $max;
        }
        $results = $this->db_->Execute($query);

        // productIds
        $productIds = array();
        while (!$results->EOF) {
            if (0 == $results->RecordCount())
                break;
            $productId = $results->fields['products_id'];
            $productIds[$productId] = $productId;
            $results->MoveNext();
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
                 and p.products_model = '" . $model . "'
                 and pd.products_id = p.products_id
                 and pd.language_id = '" . $zm_request->getLanguageId() . "'";

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
                 where p.products_id = '" . $productId . "'
                 and pd.products_id = p.products_id
                 and pd.language_id = '" . $zm_request->getLanguageId() . "'";

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

        $sql = "select p.products_id, p.products_status, pd.products_name, pd.products_description, p.products_model,
                    p.products_quantity, p.products_image, pd.products_url, p.products_price,
                    p.products_tax_class_id, p.products_date_added, p.products_date_available, p.master_categories_id,
                    p.manufacturers_id, p.products_quantity, p.products_weight, p.products_priced_by_attribute,
                    p.product_is_call, p.product_is_free, p.products_qty_box_status, p.products_quantity_order_max,
                    p.products_discount_type, p.products_discount_type_from, p.products_sort_order, p.products_price_sorter
                 from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                 where p.products_id in (" . zm_db_array($productIds) . ")
                 and pd.products_id = p.products_id
                 and pd.language_id = '" . $zm_request->getLanguageId() . "'";

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
                where products_id = '" . $productId ."'
                and language_id = '" . $zm_request->getLanguageId() . "'";
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
