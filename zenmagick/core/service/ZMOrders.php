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
 * Orders.
 *
 * @author mano
 * @package net.radebatz.zenmagick.service
 * @version $Id$
 */
class ZMOrders extends ZMService {

    /**
     * Default c'tor.
     */
    function ZMOrders() {
        parent::__construct();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMOrders();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get order for the given id.
     *
     * @param int id The order id.
     * @return ZMOrder A order or <code>null</code>.
     */
    function &getOrderForId($orderId) {
    global $zm_runtime;

        $sql = "select o.orders_id, o.customers_id, o.customers_name, o.customers_company,
                o.customers_street_address, o.customers_suburb, o.customers_city,
                o.customers_postcode, o.customers_state, o.customers_country,
                o.customers_telephone, o.customers_email_address, o.customers_address_format_id,
                o.delivery_name, o.delivery_company, o.delivery_street_address, o.delivery_suburb,
                o.delivery_city, o.delivery_postcode, o.delivery_state, o.delivery_country,
                o.delivery_address_format_id, o.billing_name, o.billing_company,
                o.billing_street_address, o.billing_suburb, o.billing_city, o.billing_postcode,
                o.billing_state, o.billing_country, o.billing_address_format_id,
                o.payment_method, o.payment_module_code, o.shipping_method, o.shipping_module_code,
                o.coupon_code, o.cc_type, o.cc_owner, o.cc_number, o.cc_expires, o.currency, o.currency_value,
                o.date_purchased, o.orders_status, o.last_modified, o.order_total, o.order_tax, o.ip_address,
                s.orders_status_name
                from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . "  ot, " . TABLE_ORDERS_STATUS . " s
                where o.orders_id = :orderId
                and o.orders_id = ot.orders_id
                and ot.class = 'ot_total'
                and o.orders_status = s.orders_status_id
                and s.language_id = :languageId
                order by orders_id desc".$sqlLimit;
        $sql = $this->getDB()->bindVars($sql, ":orderId", $orderId, "integer");
        $sql = $this->getDB()->bindVars($sql, ":languageId", $zm_runtime->getLanguageId(), "integer");

        $results = $this->getDB()->Execute($sql);

        $order = null;
        if (!$results->EOF) {
            $order =& $this->_newOrder($results->fields);
        }

        return $order;
    }

    /**
     * Get all orders for the given account id.
     *
     * @param int accountId The account id.
     * @param int limit Optional result limit.
     * @return array List of <code>ZMOrder</code> instances.
     */
    function getOrdersForAccountId($accountId, $limit=0) {
    global $zm_runtime;

        // order only
        $sqlLimit = 0 != $limit ? " limit ".$limit : "";
        $sql = "select o.orders_id, o.customers_id, o.customers_name, o.customers_company,
                o.customers_street_address, o.customers_suburb, o.customers_city,
                o.customers_postcode, o.customers_state, o.customers_country,
                o.customers_telephone, o.customers_email_address, o.customers_address_format_id,
                o.delivery_name, o.delivery_company, o.delivery_street_address, o.delivery_suburb,
                o.delivery_city, o.delivery_postcode, o.delivery_state, o.delivery_country,
                o.delivery_address_format_id, o.billing_name, o.billing_company,
                o.billing_street_address, o.billing_suburb, o.billing_city, o.billing_postcode,
                o.billing_state, o.billing_country, o.billing_address_format_id,
                o.payment_method, o.payment_module_code, o.shipping_method, o.shipping_module_code,
                o.coupon_code, o.cc_type, o.cc_owner, o.cc_number, o.cc_expires, o.currency, o.currency_value,
                o.date_purchased, o.orders_status, o.last_modified, o.order_total, o.order_tax, o.ip_address,
                s.orders_status_name
                from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . "  ot, " . TABLE_ORDERS_STATUS . " s
                where o.customers_id = :accountId
                and o.orders_id = ot.orders_id
                and ot.class = 'ot_total'
                and o.orders_status = s.orders_status_id
                and s.language_id = :languageId
                order by orders_id desc".$sqlLimit;
        $sql = $this->getDB()->bindVars($sql, ":accountId", $accountId, "integer");
        $sql = $this->getDB()->bindVars($sql, ":languageId", $zm_runtime->getLanguageId(), "integer");
        $results = $this->getDB()->Execute($sql);

        $orders = array();
        while (!$results->EOF) {
            $order =& $this->_newOrder($results->fields);
            array_push($orders, $order);
            $results->MoveNext();
        }

        return $orders;
    }

    /**
     * Get order stati for order.
     */
    function _getOrderStatiForId($orderId) {
    global $zm_runtime;

        $sql = "select os.orders_status_id, os.orders_status_name, osh.date_added, osh.comments
                  from " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh
                  where osh.orders_id = :orderId
                  and osh.orders_status_id = os.orders_status_id
                  and os.language_id = :languageId
                  order by osh.date_added";
        $sql = $this->getDB()->bindVars($sql, ":orderId", $orderId, "integer");
        $sql = $this->getDB()->bindVars($sql, ":languageId", $zm_runtime->getLanguageId(), "integer");

        $results = $this->getDB()->Execute($sql);

        $stati = array();
        while (!$results->EOF) {
            $status = $this->_newOrderStatus($results->fields);
            array_push($stati, $status);
            $results->MoveNext();
        }

        return $stati;
    }

    /**
     * Create new order status instance.
     */
    function &_newOrderStatus($fields) {
        $status =& $this->create("OrderStatus", $fields['orders_status_id'], $fields['orders_status_name'], $fields['date_added']);
        $status->comment_ = $fields['comments'];
        return $status;
    }

    /**
     * Create new order instance.
     */
    function &_newOrder($fields) {
        $order =& $this->create("Order", $fields['orders_id']);
        $order->status_ = $fields['orders_status_name'];
        $order->orderDate_ = $fields['date_purchased'];
        $order->accountId_ = $fields['customers_id'];
        $order->id_ = $fields['orders_id'];
        $order->total_ = $fields['order_total'];
        $order->account_ = $this->_newAccount($fields);
        $order->shippingAddress_ = $this->_newAddress($fields, 'delivery');
        $order->billingAddress_ = $this->_newAddress($fields, 'billing');

        /*
        fields['currency'],
        fields['currency_value'],
        fields['payment_method'],
        fields['payment_module_code'],
        fields['shipping_method'],
        fields['shipping_module_code'],
        fields['coupon_code'],
        fields['cc_type'],
        fields['cc_owner'],
        fields['cc_number'],
        fields['cc_expires'],
        fields['date_purchased'],
        fields['last_modified'],
        fields['order_tax'],
        fields['ip_address']
        */

        $order->zmOrders_ = $this;
        return $order;
    }

    /**
     * Get order items
     */
    function _getOrderItems($order) {
        $orderItems = array();

        zm_resolve_zc_class("order");
        $zenOrder = new order($order->getId());
        // keep ref for further use
        $order->zenOrder_ = $zenOrder;
        foreach ($zenOrder->products as $zenItem) {
            $orderItem = $this->_newOrderItem($zenItem);
            array_push($orderItems, $orderItem);
        }

        return $orderItems;
    }

    /**
     * Create new order item instance (PHP5 only)
     */
    function &_newOrderItem_v5($zenItem) {
        $orderItem =& $this->create("OrderItem");
        $orderItem->productId_ = $zenItem['id'];
        $orderItem->qty_ = $zenItem['qty'];
        $orderItem->name_ = $zenItem['name'];
        $orderItem->taxRate_ = $zenItem['tax'];

        if (isset($zenItem['attributes']) && 0 < sizeof($zenItem['attributes'])) {
            $attributesLookup = array();
            foreach ($zenItem['attributes'] as $zenAttribute) {
                $name = $zenAttribute['option'];
                if (array_key_exists($name, $attributesLookup)) {
                    $attribute = $attributesLookup[$name];
                } else {
                    $attribute =& $this->create("Attribute", 0, $name, null);
                    $attributesLookup[$name] = $attribute;
                }
                $attributeValue =& $this->create("AttributeValue", 0, $zenAttribute['value']);
                $attributeValue->pricePrefix_ = $zenAttribute['prefix'];
                $attributeValue->price_ = $zenAttribute['price'];
                array_push($attribute->values_, $attributeValue);
            }
            $orderItem->attributes_ = $attributesLookup;
        }
        return $orderItem;
    }

    /**
     * Create new order item instance.
     */
    function &_newOrderItem($zenItem) {
        // keep reference of used variables
        $attributesLookup = array();

        if (isset($zenItem['attributes']) && 0 < sizeof($zenItem['attributes'])) {
            foreach ($zenItem['attributes'] as $zenAttribute) {
                $name = $zenAttribute['option'];
                if (array_key_exists($name, $attributesLookup)) {
                    $atname = $attributesLookup[$name];
                } else {
                    $atname = str_replace(' ', '', $name);
                    $$atname =& $this->create("Attribute", 0, $name, null);
                    $attributesLookup[$name] = $atname;
                }
                $attributeValue =& $this->create("AttributeValue", 0, $zenAttribute['value']);
                $attributeValue->pricePrefix_ = $zenAttribute['prefix'];
                $attributeValue->price_ = $zenAttribute['price'];
                array_push($$atname->values_, $attributeValue);
            }
        }

        $orderItem =& $this->create("OrderItem");
        $orderItem->productId_ = $zenItem['id'];
        $orderItem->qty_ = $zenItem['qty'];
        $orderItem->name_ = $zenItem['name'];
        $orderItem->model_ = $zenItem['model'];
        $orderItem->taxRate_ = $zenItem['tax'];
        $orderItem->calculatedPrice_ = $zenItem['final_price'];
        foreach ($attributesLookup as $name => $atname) {
            array_push($orderItem->attributes_, $$atname);
        }

        return $orderItem;
    }

    /**
     * Create new account instance.
     */
    function &_newAccount($fields) {
        $account =& $this->create("Account");
        $account->accountId_ = $fields['customers_id'];
        //$account->firstName_ = $fields['customers_firstname'];
        $account->lastName_ = $fields['customers_name'];
        //$account->dob_ = $fields['customers_dob'];
        //$account->gender_ = $fields['customers_gender'];
        $account->email_ = $fields['customers_email_address'];
        $account->phone_ = $fields['customers_telephone'];
        //$account->fax_ = $fields['customers_fax'];
        //$account->emailFormat_ = $fields['customers_email_format'];
        //$account->referrals_ = $fields['customers_referral'];
        //$account->defaultAddressId_ = $fields['customers_default_address_id'];
        //XXX
        $account->accounts_ =& $this->create("Account");
        return $account;
    }

    /**
     * Create new address instance.
     */
    function &_newAddress($fields, $prefix) {
    global $zm_countries;
        $address =& $this->create("Address");
        $address->addressId_ = 0;
        //$address->firstName_ = $fields['entry_firstname'];
        $address->lastName_ = $fields[$prefix.'_name'];
        $address->companyName_ = $fields[$prefix.'_company'];
        //$address->gender_ = $fields['entry_gender'];
        $address->address_ = $fields[$prefix.'_street_address'];
        $address->suburb_ = $fields[$prefix.'_suburb'];
        $address->postcode_ = $fields[$prefix.'_postcode'];
        $address->city_ = $fields[$prefix.'_city'];
        $address->state_ = $fields[$prefix.'_state'];
        //$address->zoneId_ = $fields['entry_zone_id'];
        $address->country_ = $zm_countries->getCountryForName($fields[$prefix.'_country']);
        $address->format_ = $fields[$prefix.'_address_format_id'];
        return $address;
    }

    /**
     * Get order totals.
     *
     * @param int orderId The order id.
     * @return array List of <code>ZMOrderItem</code> instances.
     */
    function getOrderTotals($orderId) {
        $sql = "select * from " . TABLE_ORDERS_TOTAL . "
                where orders_id = :orderId
                order by sort_order";
        $sql = $this->getDB()->bindVars($sql, ":orderId", $orderId, "integer");

        $results = $this->getDB()->Execute($sql);

        $totals = array();
        while (!$results->EOF) {
            $fields = $results->fields;
            $total =& $this->create("OrderTotal", $fields['title'], $fields['text'], $fields['value'], $fields['class']);
            $totals[$total->getType()] = $total;
            $results->MoveNext();
        }

        return $totals;
    }

}

?>
