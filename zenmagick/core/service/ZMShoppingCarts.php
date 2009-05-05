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
 * Shopping cart service.
 *
 * <p><strong>NOTE1: This is work in progress (as are ZMShoppingCart and ZMShoppingCartItem). Eventually I also hope to work around
 * the fact that basket attributes and items are associated by the customers_id rather than the basket id. Right now it is not
 * possible to have more than one cart per customer (if possible, this could be used as wishlist storage by adding a type...)</strong></p>
 *
 * <p><strong>NOTE2: This service does not use the session to cache any values. Two more queries compared to much more complex
 * code do currently not seem worth the effort.</strong></p>
 *
 * @author DerManoMann
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMShoppingCarts extends ZMObject {

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
        return ZMObject::singleton('ShoppingCarts');
    }


    /**
     * Save the cart content.
     *
     * @param ZMShoppingCart cart The cart to save.
     */
    public function saveCart($cart) {
        // get existing data to decide on whether to INSERT or UPDATE
        $sql = "SELECT products_id FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id = :accountId";
        $skuIds = array();
        foreach (ZMRuntime::getDatabase()->query($sql, array('accountId' => $cart->getAccountId()), TABLE_CUSTOMERS_BASKET) as $result) {
            $skuIds[] = $result['skuId'];
        }

        foreach ($cart->getItems() as $item) {
            if (false && in_array($item->getId(), $skuIds)) {
                // update
                $sql = "UPDATE " . TABLE_CUSTOMERS_BASKET . "
                        SET customers_basket_quantity = :quantity
                        WHERE customers_id = :accountId and products_id = :skuId";
                $args = array('accountId' => $cart->getAccountId(), 'skuId' => $item->getId(), 'quantity' => $item->getQuantity());
                ZMRuntime::getDatabase()->update($sql, $args, TABLE_CUSTOMERS_BASKET);
            } else {
                // insert
                $sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET . "
                          (customers_id, products_id, customers_basket_quantity, customers_basket_date_added)
                        VALUES (:accountId, :skuId, :quantity, :dateAdded)";
                $args = array('accountId' => $cart->getAccountId(), 'skuId' => $item->getId(), 'quantity' => $item->getQuantity(),
                          'dateAdded' => date('Ymd'));
                          //column is 8 char, not date! 'dateAdded' => date(ZMDatabase::DATE_FORMAT));
                ZMRuntime::getDatabase()->update($sql, $args, TABLE_CUSTOMERS_BASKET);
                if ($item->hasAttributes()) {
                    foreach ($item->getAttributes() as $attribute) {
                        foreach ($attribute->getValues() as $value) {
                            $sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                                      (customers_id, products_id, products_options_id,
                                       products_options_value_id, products_options_value_text, products_options_sort_order)
                                    VALUES (:accountId, :skuId, :attributeId, :attributeValueId, :attributeValueText, :sortOrder)";
                            echo $value->getSortOrder();
                            $sortOrder = $attribute->getSortOrder() . '.' . str_pad($value->getSortOrder(), 5, '0', STR_PAD_LEFT);
                            $args = array('accountId' => $cart->getAccountId(), 'skuId' => $item->getId(), 'attributeId' => $attribute->getId(),
                                      'attributeValueId' => $value->getId(), 'attributeValueText' => $value->getName(), 'sortOrder' => $sortOrder);
                            ZMRuntime::getDatabase()->update($sql, $args, TABLE_CUSTOMERS_BASKET_ATTRIBUTES);
                        }
                    }
                }
            }
        }
    }

    /**
     * Clear a cart.
     *
     * <p>This will remove all database entries and session data.</p>
     *
     * @param ZMShoppingCart cart The cart to save.
     */
    public function clearCart($cart) {
        $sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET . "
                WHERE customers_id = :accountId";
        ZMRuntime::getDatabase()->update($sql, array('accountId' => $cart->getAccountId()), TABLE_CUSTOMERS_BASKET);
        $sql = "DELETE FRMO " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                WHERE customers_id = :accountId";
        ZMRuntime::getDatabase()->update($sql, array('accountId' => $cart->getAccountId()), TABLE_CUSTOMERS_BASKET_ATTRIBUTES);
    }

    /**
     * Load and populate a cart.
     *
     * @param int accountId The owner's account id.
     * @return ZMShoppingCart The cart.
     */
    public function loadCart($accountId) {
        // first read attributes so they are availabe to restore the products
        $sql = "SELECT * FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                WHERE customers_id = :accountId
                ORDER BY LPAD(products_options_sort_order, 11, '0')";
        $attributeResults = ZMRuntime::getDatabase()->query($sql, array('accountId' => $accountId), TABLE_CUSTOMERS_BASKET_ATTRIBUTES);

        $cart = ZMLoader::make('ShoppingCart');
        $items = array();

        $sql = "SELECT * FROM " . TABLE_CUSTOMERS_BASKET . "
                WHERE customers_id = :accountId";
        foreach (ZMRuntime::getDatabase()->query($sql, array('accountId' => $accountId), TABLE_CUSTOMERS_BASKET) as $productResult) {
            $item = ZMLoader::make('ShoppingCartItem');
            $item->setId($productResult['skuId']);
            $item->setQuantity($productResult['quantity']);
            $productAttributes = null;
            $attributeMap = array();
            $attributes = array();
            // find attributes
            foreach ($attributeResults as $attributeResult) {
                if ($item->getId() == $attributeResult['skuId']) {
                    if (null === $productAttributes) {
                        // load only if attributes are in the db
                        $productAttributes = $item->getProduct()->getAttributes();
                    }
                    // find attribute in product's attributes and clone to avoid breaking anything
                    foreach ($productAttributes as $productAttribute) {
                        if ($productAttribute->getId() == $attributeResult['attributeId']) {
                            break;
                        }
                    }

                    // make sure the attribute itself is ready with the first value
                    if (!array_key_exists($attributeResult['attributeId'], $attributeMap)) {
                        $attribute = clone $productAttribute;
                        $attribute->clearValues();
                        $attributeMap[$attribute->getId()] = $attribute;
                        $attributes[] = $attribute;
                    } else {
                        $attribute = $attributeMap[$attributeResult['attributeId']];
                    }

                    // now we have $productAttribute - a complete attribute with all available values,
                    // so lets find the value we are currently processing
                    foreach ($productAttribute->getValues() as $value) {
                        if ($value->getId() == $attrbuteResult['attributeValueId']) {
                            $tmp = clone $value;
                            // check for text/upload attributes where we also need to set the name...
                            if (in_array($attribute->getType(), array(PRODUCTS_OPTIONS_TYPE_TEXT, PRODUCTS_OPTIONS_TYPE_FILE))) {
                                $tmp->setName($attributeResult['attributeValueText']);
                            }
                            var_dump($tmp);
                            $attribute->addValue($tmp);
                            break;
                        }
                    }
                }
            }
            $item->setAttributes($attributes);
            $items[] = $item;
        }

        $cart->setItems($items);
        return $cart;
    }

}

?>
