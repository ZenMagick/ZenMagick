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
 * Orders.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMOrders extends ZMObject {

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
        return ZMObject::singleton('Orders');
    }


    /**
     * Get order for the given id.
     *
     * @param int id The order id.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return ZMOrder A order or <code>null</code>.
     */
    function getOrderForId($orderId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }
        
        $db = ZMRuntime::getDB();
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
                and s.language_id = :languageId";
        $sql = $db->bindVars($sql, ":orderId", $orderId, "integer");
        $sql = $db->bindVars($sql, ":languageId", $languageId, "integer");

        $results = $db->Execute($sql);

        $order = null;
        if (!$results->EOF) {
            $order = $this->_newOrder($results->fields);
        }

        return $order;
    }

    /**
     * Get all orders for the given account id.
     *
     * @param int accountId The account id.
     * @param int limit Optional result limit.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array List of <code>ZMOrder</code> instances.
     */
    function getOrdersForAccountId($accountId, $limit=0, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }
        
        $db = ZMRuntime::getDB();
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
        $sql = $db->bindVars($sql, ":accountId", $accountId, "integer");
        $sql = $db->bindVars($sql, ":languageId", $languageId, "integer");
        $results = $db->Execute($sql);

        $orders = array();
        while (!$results->EOF) {
            $order = $this->_newOrder($results->fields);
            array_push($orders, $order);
            $results->MoveNext();
        }

        return $orders;
    }

    /**
     * Get all orders for a given order status.
     *
     * @param int statusId The order status.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     * @return array List of <code>ZMOrder</code> instances.
     */
    function getOrdersForStatusId($statusId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }
        
        $db = ZMRuntime::getDB();
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
                where o.orders_status = :statusId
                and o.orders_id = ot.orders_id
                and ot.class = 'ot_total'
                and o.orders_status = s.orders_status_id
                and s.language_id = :languageId
                order by orders_id desc".$sqlLimit;
        $sql = $db->bindVars($sql, ":statusId", $statusId, "integer");
        $sql = $db->bindVars($sql, ":languageId", $languageId, "integer");
        $results = $db->Execute($sql);

        $orders = array();
        while (!$results->EOF) {
            $order = $this->_newOrder($results->fields);
            array_push($orders, $order);
            $results->MoveNext();
        }

        return $orders;
    }

    /**
     * Get order status history for order id.
     *
     * @param int orderId The order id.
     * @param int languageId Optional language id; default is <code>null</code> for session language.
     */
    function getOrderStatusHistoryForId($orderId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "select os.orders_status_id, os.orders_status_name, osh.date_added, osh.comments, osh.orders_id, osh.customer_notified
                  from " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh
                  where osh.orders_id = :orderId
                  and osh.orders_status_id = os.orders_status_id
                  and os.language_id = :languageId
                  order by osh.date_added";
        $sql = $db->bindVars($sql, ":orderId", $orderId, "integer");
        $sql = $db->bindVars($sql, ":languageId", $languageId, "integer");

        $results = $db->Execute($sql);

        $stati = array();
        while (!$results->EOF) {
            $status = $this->_newOrderStatus($results->fields);
            array_push($stati, $status);
            $results->MoveNext();
        }

        return $stati;
    }

    /**
     * Create new order status history entry.
     *
     * @param ZMOrderStatus orderStatus The new order status.
     * @return ZMOrderStatus The created order status (incl id).
     */
    function createOrderStatusHistory($orderStatus) {
        $db = ZMRuntime::getDB();
        $sql = "INSERT INTO " .  TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments)
                VALUES (:orderId;integer, :id;integer, now(), :customerNotified;integer, :comment;string)";
        $sql = ZMDbUtils::bindObject($sql, $orderStatus, false);

        $results = $db->Execute($sql);
        $orderStatus->id_ = $db->Insert_ID();

        return $orderStatus;
    }

    /**
     * Create new order status instance.
     */
    function _newOrderStatus($fields) {
        $status = ZMLoader::make("OrderStatus");
        $status->id_ = $fields['orders_status_id'];
        $status->orderId_ = $fields['orders_id'];
        $status->name_ = $fields['orders_status_name'];
        $status->dateAdded_ = $fields['date_added'];
        $status->customerNotified_ = 1 == $fields['customer_notified'];
        $status->comment_ = $fields['comments'];
        return $status;
    }

    /**
     * Create new order instance.
     */
    function _newOrder($fields) {
        $order = ZMLoader::make("Order");
        $order->id_ = $fields['orders_id'];
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

        return $order;
    }

    /**
     * Get order items
     */
    function _getOrderItems($order) {
        $orderItems = array();

        ZMLoader::resolveZCClass('order');
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
     * Create new order item instance.
     */
    function _newOrderItem($zenItem) {
        // keep reference of used variables
        $attributesLookup = array();

        if (isset($zenItem['attributes']) && 0 < sizeof($zenItem['attributes'])) {
            foreach ($zenItem['attributes'] as $zenAttribute) {
                $name = $zenAttribute['option'];
                if (array_key_exists($name, $attributesLookup)) {
                    $atname = $attributesLookup[$name];
                } else {
                    $atname = str_replace(' ', '', $name);
                    $$atname = ZMLoader::make("Attribute", 0, $name, null);
                    $attributesLookup[$name] = $atname;
                }
                $attributeValue = ZMLoader::make("AttributeValue", 0, $zenAttribute['value']);
                $attributeValue->pricePrefix_ = $zenAttribute['prefix'];
                $attributeValue->price_ = $zenAttribute['price'];
                array_push($$atname->values_, $attributeValue);
            }
        }

        $orderItem = ZMLoader::make("OrderItem");
        $orderItem->productId_ = $zenItem['id'];
        $orderItem->qty_ = $zenItem['qty'];
        $orderItem->name_ = $zenItem['name'];
        $orderItem->model_ = $zenItem['model'];
        $taxRate = ZMLoader::make("TaxRate");
        $taxRate->setRate($zenItem['tax']);
        $orderItem->taxRate_ = $taxRate;
        $taxRate = ZMLoader::make("TaxRate");
        $taxRate->setRate($zenItem['tax']);
        $orderItem->calculatedPrice_ = $taxRate->addTax($zenItem['final_price']);
        foreach ($attributesLookup as $name => $atname) {
            array_push($orderItem->attributes_, $$atname);
        }

        return $orderItem;
    }

    /**
     * Create new account instance.
     */
    function _newAccount($fields) {
        $account = ZMLoader::make("Account");
        $account->setAccountId($fields['customers_id']);
        // orders has only name, not first/last...
        $account->setLastName($fields['customers_name']);
        $account->setEmail($fields['customers_email_address']);
        $account->setPhone($fields['customers_telephone']);
        return $account;
    }

    /**
     * Create new address instance.
     */
    function _newAddress($fields, $prefix) {
        $address = ZMLoader::make("Address");
        $address->setAddressId(0);
        // orders has only name, not first/last...
        $address->setLastName($fields[$prefix.'_name']);
        $address->setCompanyName($fields[$prefix.'_company']);
        $address->setAddress($fields[$prefix.'_street_address']);
        $address->setSuburb($fields[$prefix.'_suburb']);
        $address->setPostcode($fields[$prefix.'_postcode']);
        $address->setCity($fields[$prefix.'_city']);
        $address->setState($fields[$prefix.'_state']);
        $address->setCountry(ZMCountries::instance()->getCountryForName($fields[$prefix.'_country']));
        $address->setFormat($fields[$prefix.'_address_format_id']);
        return $address;
    }

    /**
     * Get order totals.
     *
     * @param int orderId The order id.
     * @return array List of <code>ZMOrderItem</code> instances.
     */
    function getOrderTotals($orderId) {
        $db = ZMRuntime::getDB();
        $sql = "select * from " . TABLE_ORDERS_TOTAL . "
                where orders_id = :orderId
                order by sort_order";
        $sql = $db->bindVars($sql, ":orderId", $orderId, "integer");

        $results = $db->Execute($sql);

        $totals = array();
        while (!$results->EOF) {
            $fields = $results->fields;
            $total = ZMLoader::make("OrderTotal", $fields['title'], $fields['text'], $fields['value'], $fields['class']);
            $totals[$total->getType()] = $total;
            $results->MoveNext();
        }

        return $totals;
    }

    /**
     * Update an existing order.
     *
     * <p><strong>NOTE: Currently not all properties are supported!</strong></p>
     *
     * @param ZMOrder The order.
     * @return ZMOrder The updated order.
     */
    function updateOrder($order) {
        $db = ZMRuntime::getDB();

/*
                customers_name = :customers_name;string,
                customers_company = :customers_company;string,
                customers_street_address = :customers_street_address;string,
                customers_suburb = :customers_suburb;string,
                customers_city = :customers_city;string,
                customers_postcode = :customers_postcode;string,
                customers_state = :customers_state;string,
                customers_country = :customers_country;string,
                customers_telephone = :customers_telephone;string,
                customers_email_address = :customers_email_address;string,
                customers_address_format_id = :customers_address_format_id;string,

                delivery_name = :delivery_name;string,
                delivery_company = :delivery_company;string,
                delivery_street_address = :delivery_street_address;string,
                delivery_suburb = :delivery_suburb;string,
                delivery_city = :delivery_city;string,
                delivery_postcode = :delivery_postcode;string,
                delivery_state = :delivery_state;string,
                delivery_country = :delivery_country;string,
                delivery_address_format_id = :delivery_address_format_id;string,

                billing_name = :billing_name;string,
                billing_company = :billing_company;string,
                billing_street_address = :billing_street_address;string,
                billing_suburb = :billing_suburb;string,
                billing_city = :billing_city;string,
                billing_postcode = :billing_postcode;string,
                billing_state = :billing_state;string,
                billing_country = :billing_country;string,
                billing_address_format_id = :billing_address_format_id;string,

                payment_method = :payment_method;string,
                payment_module_code = :payment_module_code;string,

                shipping_method = :shipping_method;string,
                shipping_module_code = :shipping_module_code;string,

                coupon_code = :coupon_code;string,

                cc_type = :cc_type;string,
                cc_owner = :cc_owner;string,
                cc_number = :cc_number;string,
                cc_expires = :cc_expires;string,

                currency = :currency;string,
                currency_value = :currency_value;string,

                date_purchased = :date_purchased;string,
                last_modified = :last_modified;string,
                order_total = :order_total;string,
                order_tax = :order_tax;string,

*/
        $sql = "update " . TABLE_ORDERS . " set
                :customFields,
                customers_id = :accountId;integer,
                orders_status = :status;integer
                where orders_id = :orderId";
        $sql = $db->bindVars($sql, ":orderId", $order->getId(), "integer");
        $sql = ZMDbUtils::bindObject($sql, $order, false);
        $sql = ZMDbUtils::bindCustomFields($sql, $order, TABLE_ORDERS);
        $db->Execute($sql);

        return $order;
    }

    /**
     * Get downloads for order.
     *
     * @param int orderId The order id.
     * @param array orderStatusList Optional array of order stati to check; default is null to use the configured range, (empty array will load all).
     * @return array A list of <code>ZMDownload</code> instances.
     */
    public function getDownloadsForOrderId($orderId, $orderStatusList=null) {
        if (null === $orderStatusList) {
            $orderStatusList = ZMSettings::get('downloadOrderStatusList');
        }
        $sql = "SELECT o.date_purchased, o.orders_status, opd.*
                FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
                WHERE o.orders_id = :orderId
                  AND o.orders_id = op.orders_id
                  AND op.orders_products_id = opd.orders_products_id
                  AND opd.orders_products_filename != ''";
        if (0 < count($orderStatusList)) {
            $sql .= ' AND o.orders_status in (:status)';
        }

        $mapping = array(TABLE_ORDERS_PRODUCTS_DOWNLOAD, TABLE_ORDERS_PRODUCTS, TABLE_ORDERS);
        return ZMRuntime::getDatabase()->query($sql, array('orderId' => $orderId, 'status' => $orderStatusList), $mapping, 'Download');
    }

}

?>
