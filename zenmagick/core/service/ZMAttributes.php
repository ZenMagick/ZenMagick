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
 * Attribute service.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMAttributes extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
        return parent::instance('Attributes');
    }


    /**
     * Simple lookup to check for existing attributes.
     *
     * @param int productId The product id.
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return boolean <code>true</code> if attributes eixst, <code>false</code> if not.
     */ 
    function checkForAttributes($productId, $languageId=null) {
    global $zm_request;

        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "select count(*) as total
                from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib
                where patrib.products_id = :productId
                and patrib.options_id = popt.products_options_id
                and popt.language_id = :languageId
                limit 1";
        $sql = $db->bindVars($sql, ":productId", $productId, "integer");
        $sql = $db->bindVars($sql, ":languageId", $languageId, "integer");
        $results = $db->Execute($sql);
        return 0 < $results->fields['total'];
    }


    // create new attribute
    function _newAttribute($fields) {
        $attribute = $this->create("Attribute", $fields['products_options_id'], $fields['products_options_name'], $fields['products_options_type']);
        $attribute->sortOrder_ = $fields['products_options_sort_order'];
        $attribute->comment_ = $fields['products_options_comment'];
        return $attribute;
    }


    // create new attribute value
    function _newAttributeValue($fields, $product) {
        $value = $this->create("AttributeValue", $fields['products_options_values_id'], $fields['products_options_values_name']);
        // let's start with the easy ones
        $value->pricePrefix_ = $fields['price_prefix'];
        $value->isFree_ = ('1' == $fields['product_attribute_is_free']);
        $value->weight_ = $fields['products_attributes_weight'];
        $value->weightPrefix_ = $fields['products_attributes_weight_prefix'];
        $value->isDisplayOnly_ = $fields['attributes_display_only'];
        $value->isDefault_ = ('1' == $fields['attributes_default']);
        $value->isDiscounted_ = $fields['attributes_discounted'];
        $value->image_ = $fields['attributes_image'];
        $value->isOneTime_ = $fields['attributes_price_onetime'];
        $value->isPriceFactorOneTime_ = $fields['attributes_price_factor_onetime'];

        // and now the funky stuff
        if ($value->isDiscounted_) {
            $price = zm_get_attributes_price_final($fields["products_attributes_id"], 1, '', 'false');
            $value->price_ = zm_get_discount_calc((int)$product->getId(), true, $price);
        } else {
            $value->price_ = $fields['options_values_price'];
        }
        $taxRate = $product->getTaxRate();
        $value->price_ = $taxRate->addTax($value->price_);

        if ($value->isOneTime_ || $value->isPriceFactorOneTime_) {
            $onetimeCharges = zm_get_attributes_price_final_onetime($fields["products_attributes_id"], 1, '');
            $value->oneTimePrice_ = $taxRate->addTax($onetimeCharges);
        } else {
            $value->oneTimePrice_ = 0;
        }

        return $value;
    }


    /**
     * Load attributes for the given product and language.
     *
     * @param ZMProduct product The product.
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return boolean <code>true</code> if attributes eixst, <code>false</code> if not.
     */ 
    function getAttributesForProduct($product, $languageId=null) {
    global $zm_request;

        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $attributesOrderBy= '';
        if (zm_setting('isSortAttributesByName')) {
            $attributesOrderBy= ' order by popt.products_options_name';
        } else {
            $attributesOrderBy= ' order by LPAD(popt.products_options_sort_order,11,"0")';
        }

        $valuesOrderBy= '';
        if (zm_setting('isSortAttributeValuesByPrice')) {
            $valuesOrderBy= ' order by LPAD(pa.products_options_sort_order,11,"0"), pa.options_values_price';
        } else {
            $valuesOrderBy= ' order by LPAD(pa.products_options_sort_order,11,"0"), pov.products_options_values_name';
        }

        $db = ZMRuntime::getDB();
        $sql = "select distinct popt.products_options_id, popt.products_options_name, popt.products_options_sort_order,
                popt.products_options_type, popt.products_options_length, popt.products_options_comment, popt.products_options_size,
                popt.products_options_images_per_row, popt.products_options_images_style
                from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib
                where patrib.products_id = :productId
                and patrib.options_id = popt.products_options_id
                and popt.language_id = :languageId" .
                $attributesOrderBy;
        $sql = $db->bindVars($sql, ":productId", $product->getId(), "integer");
        $sql = $db->bindVars($sql, ":languageId", $languageId, "integer");
        $attributeResults = $db->Execute($sql);

        $attributes = array();
        // iterate over all attributes
        while (!$attributeResults->EOF) {
            $attribute = $this->_newAttribute($attributeResults->fields);

            $sql = "select pov.products_options_values_id, pov.products_options_values_name, pa.*
                    from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                    where pa.products_id = :productId
                    and pa.options_id = :attributeId
                    and pa.options_values_id = pov.products_options_values_id
                    and pov.language_id = :languageId " .
                    $valuesOrderBy;
            $sql = $db->bindVars($sql, ":attributeId", $attribute->getId(), "integer");
            $sql = $db->bindVars($sql, ":productId", $product->getId(), "integer");
            $sql = $db->bindVars($sql, ":languageId", $languageId, "integer");
            $valueResults = $db->Execute($sql);

            // get all values for the current attribute
            while (!$valueResults->EOF) {
                $value = $this->_newAttributeValue($valueResults->fields, $product);
                // add to attribute
                array_push($attribute->values_, $value);
                $valueResults->MoveNext();
            }

            // add to attributes
            $attributes[] = $attribute;
            $attributeResults->MoveNext();
        }

        return $attributes;
    }

}

?>
