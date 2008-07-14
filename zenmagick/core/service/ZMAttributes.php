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
        return ZMObject::singleton('Attributes');
    }


    // create new attribute value
    function _newAttributeValue($fields, $product) {
        $value = ZMLoader::make("AttributeValue", $fields['products_options_values_id'], $fields['products_options_values_name']);
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
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        // set up sort order SQL
        $attributesOrderBy= '';
        if (ZMSettings::get('isSortAttributesByName')) {
            $attributesOrderBy= ' ORDER BY po.products_options_name';
        } else {
            $attributesOrderBy= ' ORDER BY LPAD(po.products_options_sort_order, 11, "0")';
        }

        $sql = "SELECT distinct po.products_options_id, po.products_options_name, po.products_options_sort_order,
                po.products_options_type, po.products_options_length, po.products_options_comment, po.products_options_size,
                po.products_options_images_per_row, po.products_options_images_style
                FROM " . TABLE_PRODUCTS_OPTIONS . " po, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                WHERE pa.products_id = :productId
                  AND pa.options_id = po.products_options_id
                  AND po.language_id = :languageId" .
                $attributesOrderBy;
        $args = array('productId' => $product->getId(), 'languageId' => $languageId);
        $results = ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_PRODUCTS_OPTIONS, TABLE_PRODUCTS_ATTRIBUTES), 'Attribute');

        // set up sort order SQL
        $valuesOrderBy= '';
        if (ZMSettings::get('isSortAttributeValuesByPrice')) {
            $valuesOrderBy= ' ORDER BY LPAD(pa.products_options_sort_order, 11, "0"), pa.options_values_price';
        } else {
            $valuesOrderBy= ' ORDER BY LPAD(pa.products_options_sort_order, 11, "0"), pov.products_options_values_name';
        }

        $db = ZMRuntime::getDB();
        $attributes = array();
        foreach ($results as $attribute) {
            $sql = "SELECT pov.products_options_values_id, pov.products_options_values_name, pa.*
                    FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                    WHERE pa.products_id = :productId
                      AND pa.options_id = :attributeId
                      AND pa.options_values_id = pov.products_options_values_id
                      AND pov.language_id = :languageId " .
                    $valuesOrderBy;
            $sql = $db->bindVars($sql, ":attributeId", $attribute->getId(), "integer");
            $sql = $db->bindVars($sql, ":productId", $product->getId(), "integer");
            $sql = $db->bindVars($sql, ":languageId", $languageId, "integer");
            $valueResults = $db->Execute($sql);

            // get all values for the current attribute
            while (!$valueResults->EOF) {
                $value = $this->_newAttributeValue($valueResults->fields, $product);
                $attribute->addValue($value);
                $valueResults->MoveNext();
            }

            // add to attributes
            $attributes[] = $attribute;
        }

        return $attributes;
    }

}

?>
