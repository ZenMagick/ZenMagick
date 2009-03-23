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
 * Attribute service.
 *
 * @author DerManoMann
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


    /**
     * Load attributes for the given product and language.
     *
     * @param ZMProduct product The product.
     * @param int languageId The languageId; default is <code>null</code> for session language.
     * @return boolean <code>true</code> if attributes eixst, <code>false</code> if not.
     */ 
    public function getAttributesForProduct($product, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        // set up sort order SQL
        $attributesOrderBy = '';
        if (ZMSettings::get('isSortAttributesByName')) {
            $attributesOrderBy= ' ORDER BY po.products_options_name';
        } else {
            $attributesOrderBy= ' ORDER BY LPAD(po.products_options_sort_order, 11, "0")';
        }

        $sql = "SELECT distinct po.products_options_id, po.products_options_name, po.products_options_sort_order,
                po.products_options_type, po.products_options_length, po.products_options_comment, po.products_options_size,
                po.products_options_images_per_row, po.products_options_images_style, pa.products_id
                FROM " . TABLE_PRODUCTS_OPTIONS . " po, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                WHERE pa.products_id = :productId
                  AND pa.options_id = po.products_options_id
                  AND po.language_id = :languageId" .
                $attributesOrderBy;
        $args = array('productId' => $product->getId(), 'languageId' => $languageId);
        $attributes = ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_PRODUCTS_OPTIONS, TABLE_PRODUCTS_ATTRIBUTES), 'Attribute');
        if (0 == count($attributes)) {
            return $attributes;
        }

        // put in map for easy lookup
        $attributeMap = array();
        foreach ($attributes as $attribute) {
            $attributeMap[$attribute->getId()] = $attribute;
        }

        $sql = "SELECT pov.products_options_values_id, pov.products_options_values_name, pa.*
                FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                WHERE pa.products_id = :productId
                  AND pa.options_id IN (:attributeId)
                  AND pa.options_values_id = pov.products_options_values_id
                  AND pov.language_id = :languageId ";
        // set up sort order SQL
        if (ZMSettings::get('isSortAttributeValuesByPrice')) {
            $sql .= ' ORDER BY pa.options_id, LPAD(pa.products_options_sort_order, 11, "0"), pa.options_values_price';
        } else {
            $sql .= ' ORDER BY pa.options_id, LPAD(pa.products_options_sort_order, 11, "0"), pov.products_options_values_name';
        }

        // read all in one go
        $args = array('attributeId' => array_keys($attributeMap), 'productId' => $product->getId(), 'languageId' => $languageId);
        $mapping = array(TABLE_PRODUCTS_OPTIONS_VALUES, TABLE_PRODUCTS_ATTRIBUTES);
        foreach (ZMRuntime::getDatabase()->query($sql, $args, $mapping, 'AttributeValue') as $value) {
            $attribute = $attributeMap[$value->getAttributeId()];
            $value->setAttribute($attribute);
            $value->setTaxRate($product->getTaxRate());
            $attribute->addValue($value);
        }

        return $attributes;
    }

}

?>
