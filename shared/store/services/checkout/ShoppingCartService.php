<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\services\checkout;

use ZMRuntime;
use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\apps\store\model\checkout\ShoppingCart;

/**
 * Shopping cart service.
 *
 * <p><strong>NOTE1: This is work in progress (as are ShoppingCart and ShoppingCartItem). Eventually I also hope to work around
 * the fact that basket attributes and items are associated by the customers_id rather than the basket id. Right now it is not
 * possible to have more than one cart per customer (if possible, this could be used as wishlist storage by adding a type...)</strong></p>
 *
 * <p><strong>NOTE2: This service does not use the session to cache any values. Two more queries compared to much more complex
 * code do currently not seem worth the effort.</strong></p>
 *
 * @author DerManoMann
 */
class ShoppingCartService extends ZMObject {

    /**
     * Save the cart content.
     *
     * @param ShoppingCart shoppingCart The cart to save.
     */
    public function saveCart($shoppingCart) {
        if (0 == $shoppingCart->getAccountId()) {
            return;
        }

        // get existing data to decide on whether to INSERT or UPDATE
        $sql = "SELECT products_id FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id = :accountId";
        $skuIds = array();
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array('accountId' => $shoppingCart->getAccountId()), 'customers_basket') as $result) {
            $skuIds[] = $result['skuId'];
        }

        foreach ($shoppingCart->getItems() as $item) {
            if (false && in_array($item->getId(), $skuIds)) {
                // update
                $sql = "UPDATE " . TABLE_CUSTOMERS_BASKET . "
                        SET customers_basket_quantity = :quantity
                        WHERE customers_id = :accountId and products_id = :skuId";
                $args = array('accountId' => $shoppingCart->getAccountId(), 'skuId' => $item->getId(), 'quantity' => $item->getQuantity());
                ZMRuntime::getDatabase()->updateObj($sql, $args, 'customers_basket');
            } else {
                // insert
                $sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET . "
                          (customers_id, products_id, customers_basket_quantity, customers_basket_date_added)
                        VALUES (:accountId, :skuId, :quantity, :dateAdded)";
                $args = array('accountId' => $shoppingCart->getAccountId(), 'skuId' => $item->getId(), 'quantity' => $item->getQuantity(),
                          'dateAdded' => date('Ymd')); //column is 8 char, not date!
                ZMRuntime::getDatabase()->updateObj($sql, $args, 'customers_basket');
                if ($item->hasAttributes()) {
                    foreach ($item->getAttributes() as $attribute) {
                        foreach ($attribute->getValues() as $value) {
                            $sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                                      (customers_id, products_id, products_options_id,
                                       products_options_value_id, products_options_value_text, products_options_sort_order)
                                    VALUES (:accountId, :skuId, :attributeId, :attributeValueId, :attributeValueText, :sortOrder)";
                            $sortOrder = $attribute->getSortOrder() . '.' . str_pad($value->getSortOrder(), 5, '0', STR_PAD_LEFT);
                            $args = array('accountId' => $shoppingCart->getAccountId(), 'skuId' => $item->getId(), 'attributeId' => $attribute->getId(),
                                      'attributeValueId' => $value->getId(), 'attributeValueText' => $value->getName(), 'sortOrder' => $sortOrder);
                            ZMRuntime::getDatabase()->updateObj($sql, $args, 'customers_basket_attributes');
                        }
                    }
                }
            }
        }
    }

    /**
     * Clear a cart.
     *
     * <p>This will remove all cart data fom the database.</p>
     *
     * @param ShoppingCart shoppingCart The cart to save.
     */
    public function clearCart($shoppingCart) {
        $sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET . "
                WHERE customers_id = :accountId";
        ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $shoppingCart->getAccountId()), 'customers_basket');
        $sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                WHERE customers_id = :accountId";
        ZMRuntime::getDatabase()->updateObj($sql, array('accountId' => $shoppingCart->getAccountId()), 'customers_basket_attributes');
    }

    /**
     * Update the given cart.
     *
     * @param ShoppingCart shoppingCart The cart to save.
     */
    public function updateCart($shoppingCart) {
        $this->clearCart($shoppingCart);
        $this->saveCart($shoppingCart);
    }

    /**
     * Get contents for the given account id.
     *
     * @param int accountId The owner's account id.
     * @return array The shopping cart contents.
     */
    public function getContentsForAccountId($accountId) {
        // build contents
        $contents = array();

        // read all in one go
        $sql = "SELECT * FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                WHERE customers_id = :accountId
                ORDER BY LPAD(products_options_sort_order, 11, '0'), products_id";
        $attributeResults = ZMRuntime::getDatabase()->fetchAll($sql, array('accountId' => $accountId), 'customers_basket_attributes');

        $sql = "SELECT * FROM " . TABLE_CUSTOMERS_BASKET . "
                WHERE customers_id = :accountId";
        foreach (ZMRuntime::getDatabase()->fetchAll($sql, array('accountId' => $accountId), 'customers_basket') as $result) {
            $id = $result['skuId'];
            $quantity = $result['quantity'];

            // match attributes
            $attributes = array();
            $attributes_values = array();
            foreach ($attributeResults as $result) {
                if ($result['skuId'] == $id) {
                    $attributes[$result['attributeId']] = $result['attributeValueId'];
                    $attributes_values[$result['attributeId']] = $result['attributeValueText'];
                }
            }
            $contents[$id] = array(
                'qty' => $quantity,
                'attributes' => $attributes,
                'attributes_values' => $attributes_values,
            );
        }

        return $contents;
    }

    /**
     * Load and populate a cart.
     *
     * <p>This will load and instantiate a <strong>new</strong> shopping cart instance.</p>
     *
     * @param int accountId The owner's account id.
     * @return ShoppingCart The cart.
     * @deprecated Use getContentsForAccountId($accountId) to load the contents and set that on the shared shopping cart instance instead
     */
    public function loadCartForAccountId($accountId) {
        $shoppingCart = Beans::getBean('zenmagick\apps\store\model\checkout\ShoppingCart');
        $shoppingCart->setCheckoutHelper($this->container->get('checkoutHelper'));
        $shoppingCart->setContents($this->getContentsForAccountId($accountId));
        return $shoppingCart;
    }

}
