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
     * @param ZMAccount account The cart owner.
     */
    function saveCart($cart, $account) {
        // get existing data to decide on whether to INSERT or UPDATE
        $sql = "SELECT products_id FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id = :accountId";
        $skuIds = array();
        foreach (ZMRuntime::getDatabase()->query($sql, array('accountId' => $account->getId()), TABLE_CUSTOMERS_BASKET) as $result) {
            $skuIds[] = $result['skuId'];
        }

        foreach ($cart->getItems() as $item) {
            if (false && in_array($item->getId(), $skuIds)) {
                // update
                $sql = "UPDATE " . TABLE_CUSTOMERS_BASKET . "
                        SET customers_basket_quantity = :quantity
                        WHERE customers_id = :accountId and products_id = :skuId";
                $args = array('accountId' => $account->getId(), 'skuId' => $item->getId(), 'quantity' => $item->getQuantity());
                ZMRuntime::getDatabase()->update($sql, $args, TABLE_CUSTOMERS_BASKET);
            } else {
                // insert
                $sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET . "
                          (customers_id, products_id, customers_basket_quantity, customers_basket_date_added)
                        VALUES (:accountId, :skuId, :quantity, :dateAdded)";
                $args = array('accountId' => $account->getId(), 'skuId' => $item->getId(), 'quantity' => $item->getQuantity(),
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
                            $args = array('accountId' => $account->getId(), 'skuId' => $item->getId(), 'attributeId' => $attribute->getId(),
                                      'attributeValueId' => $value->getId(), 'attributeValueText' => $value->getName(), 'sortOrder' => $sortOrder);
                            ZMRuntime::getDatabase()->update($sql, $args, TABLE_CUSTOMERS_BASKET_ATTRIBUTES);
                        }
                    }
                }
            }
        }
    }

/****************

    // reset
    /    if (isset($_SESSION['customer_id']) && ($reset_database == true)) {
      $sql = "delete from " . TABLE_CUSTOMERS_BASKET . "
                where customers_id = '" . (int)$_SESSION['customer_id'] . "'";

      $db->Execute($sql);

      $sql = "delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                where customers_id = '" . (int)$_SESSION['customer_id'] . "'";

      $db->Execute($sql);
    }


        $sql = "insert into " . TABLE_CUSTOMERS_BASKET . "
                              (customers_id, products_id, customers_basket_quantity,
                              customers_basket_date_added)
                              values ('" . (int)$_SESSION['customer_id'] . "', '" . zen_db_input($products_id) . "', '" .
        $qty . "', '" . date('Ymd') . "')";

        $db->Execute($sql);
      }

      if (is_array($attributes)) {
        reset($attributes);
        while (list($option, $value) = each($attributes)) {
          //CLR 020606 check if input was from text box.  If so, store additional attribute information
          //CLR 020708 check if text input is blank, if so do not add to attribute lists
          //CLR 030228 add htmlspecialchars processing.  This handles quotes and other special chars in the user input.
          $attr_value = NULL;
          $blank_value = FALSE;
          if (strstr($option, TEXT_PREFIX)) {
            if (trim($value) == NULL) {
              $blank_value = TRUE;
            } else {
              $option = substr($option, strlen(TEXT_PREFIX));
              $attr_value = stripslashes($value);
              $value = PRODUCTS_OPTIONS_VALUES_TEXT_ID;
              $this->contents[$products_id]['attributes_values'][$option] = $attr_value;
            }
          }

          if (!$blank_value) {
            if (is_array($value) ) {
              reset($value);
              while (list($opt, $val) = each($value)) {
                $this->contents[$products_id]['attributes'][$option.'_chk'.$val] = $val;
              }
            } else {
              $this->contents[$products_id]['attributes'][$option] = $value;
            }
            // insert into database
            //CLR 020606 update db insert to include attribute value_text. This is needed for text attributes.
            //CLR 030228 add zen_db_input() processing
            if (isset($_SESSION['customer_id'])) {

              //              if (zen_session_is_registered('customer_id')) zen_db_query("insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (customers_id, products_id, products_options_id, products_options_value_id, products_options_value_text) values ('" . (int)$customer_id . "', '" . zen_db_input($products_id) . "', '" . (int)$option . "', '" . (int)$value . "', '" . zen_db_input($attr_value) . "')");
              if (is_array($value) ) {
                reset($value);
                while (list($opt, $val) = each($value)) {
                  $products_options_sort_order= zen_get_attributes_options_sort_order(zen_get_prid($products_id), $option, $opt);
                  $sql = "insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                                        (customers_id, products_id, products_options_id, products_options_value_id, products_options_sort_order)
                                        values ('" . (int)$_SESSION['customer_id'] . "', '" . zen_db_input($products_id) . "', '" .
                                        (int)$option.'_chk'.$val . "', '" . $val . "',  '" . $products_options_sort_order . "')";

                                        $db->Execute($sql);
                }
              } else {
                if ($attr_value) {
                  $attr_value = zen_db_input($attr_value);
                }
                $products_options_sort_order= zen_get_attributes_options_sort_order(zen_get_prid($products_id), $option, $value);
                $sql = "insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                                      (customers_id, products_id, products_options_id, products_options_value_id, products_options_value_text, products_options_sort_order)
                                      values ('" . (int)$_SESSION['customer_id'] . "', '" . zen_db_input($products_id) . "', '" .
                                      (int)$option . "', '" . $value . "', '" . $attr_value . "', '" . $products_options_sort_order . "')";

                                      $db->Execute($sql);
              }
            }
          }
        }
      }
    }







    if (isset($_SESSION['customer_id'])) {
      $sql = "update " . TABLE_CUSTOMERS_BASKET . "
                set customers_basket_quantity = '" . (float)$quantity . "'
                where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                and products_id = '" . zen_db_input($products_id) . "'";

      $db->Execute($sql);

    }

    if (is_array($attributes)) {
      reset($attributes);
      while (list($option, $value) = each($attributes)) {
        //CLR 020606 check if input was from text box.  If so, store additional attribute information
        //CLR 030108 check if text input is blank, if so do not update attribute lists
        //CLR 030228 add htmlspecialchars processing.  This handles quotes and other special chars in the user input.
        $attr_value = NULL;
        $blank_value = FALSE;
        if (strstr($option, TEXT_PREFIX)) {
          if (trim($value) == NULL) {
            $blank_value = TRUE;
          } else {
            $option = substr($option, strlen(TEXT_PREFIX));
            $attr_value = stripslashes($value);
            $value = PRODUCTS_OPTIONS_VALUES_TEXT_ID;
            $this->contents[$products_id]['attributes_values'][$option] = $attr_value;
          }
        }

        if (!$blank_value) {
          if (is_array($value) ) {
            reset($value);
            while (list($opt, $val) = each($value)) {
              $this->contents[$products_id]['attributes'][$option.'_chk'.$val] = $val;
            }
          } else {
            $this->contents[$products_id]['attributes'][$option] = $value;
          }
          // update database
          //CLR 020606 update db insert to include attribute value_text. This is needed for text attributes.
          //CLR 030228 add zen_db_input() processing
          //          if (zen_session_is_registered('customer_id')) zen_db_query("update " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " set products_options_value_id = '" . (int)$value . "', products_options_value_text = '" . zen_db_input($attr_value) . "' where customers_id = '" . (int)$customer_id . "' and products_id = '" . zen_db_input($products_id) . "' and products_options_id = '" . (int)$option . "'");

          if ($attr_value) {
            $attr_value = zen_db_input($attr_value);
          }
          if (is_array($value) ) {
            reset($value);
            while (list($opt, $val) = each($value)) {
              $products_options_sort_order= zen_get_attributes_options_sort_order(zen_get_prid($products_id), $option, $opt);
              $sql = "update " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                        set products_options_value_id = '" . $val . "'
                        where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                        and products_id = '" . zen_db_input($products_id) . "'
                        and products_options_id = '" . (int)$option.'_chk'.$val . "'";

              $db->Execute($sql);
            }
          } else {
            if (isset($_SESSION['customer_id'])) {
              $sql = "update " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                        set products_options_value_id = '" . $value . "', products_options_value_text = '" . $attr_value . "'
                        where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                        and products_id = '" . zen_db_input($products_id) . "'
                        and products_options_id = '" . (int)$option . "'";

              $db->Execute($sql);
            }
          }
        }
      }
    }
    $this->n




      $sql = "delete from " . TABLE_CUSTOMERS_BASKET . "
                where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                and products_id = '" . zen_db_input($products_id) . "'";

      $db->Execute($sql);

      //        zen_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . zen_db_input($products_id) . "'");

      $sql = "delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                and products_id = '" . zen_db_input($products_id) . "'";

      $db->Execute($sql);

    }
    ***********/

}

?>
