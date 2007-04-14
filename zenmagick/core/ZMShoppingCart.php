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
 * Shopping cart.
 * <p>This is assuming a properly configured zen cart.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMShoppingCart extends ZMDao {
    var $cart_;
    var $zenTotals_;
    var $payments_;


    /**
     * Default c'tor.
     */
    function ZMShoppingCart() {
        parent::__construct();

        $this->refresh();
        $this->zenTotals_ = null;
        $this->payments_ = null;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMShoppingCart();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Refresh the cart.
     */
    function refresh() { $this->cart_ = $_SESSION['cart']; }


    /**
     * Check if the cart is empty.
     *
     * @return bool <code>true</code> if the cart is empty, <code>false</code> if the cart is not empty.
     */
    function isEmpty() { return 0 == $this->getSize(); }

    /**
     * Get the size of the cart.
     *
     * <p><strong>NOTE:</strong> This is the number of line items in the cart, not the total number of products.</p>
     *
     * @return int The number of different products in the cart.
     */
    function getSize() { return isset($this->cart_) ? count($this->cart_->get_products()) : 0; }

    /**
     * Get the carts weight.
     *
     * @return float The weight if the shopping cart.
     */
    function getWeight() { return $this->cart_->show_weight(); }

    /**
     * Checks if there are only gift vouchers in the cart.
     *
     * @return bool <code>true</code> if only vouchers are in the cart.
     */
    function isGVOnly() { return $this->cart_->gv_only(); }

    /**
     * Checks for free products in the cart.
     *
     * @return int The number of free products in the cart.
     */
    function freeProductsCount() { return $this->cart_->in_cart_check('product_is_free','1'); }

    /**
     * Checks for virtual products in the cart.
     *
     * @return int The number of virtual products in the cart.
     */
    function virtualProductsCount() { return $this->cart_->in_cart_check('products_virtual','1'); }

    /**
     * Checks for free shipping.
     *
     * @return bool <code>true</code> if the cart is free of shipping.
     */
    function freeShippingCount() { return $this->cart_->in_cart_check('product_is_always_free_shipping','1'); }

    /**
     * Check for virtual cart.
     * 
     * @return bool <code>true</code> if the cart is purely virtual.
     */
    function isVirtual() { return $_SESSION['sendto'] == false; }

    /**
     * Get the items in the cart.
     *
     * @return array List of <code>ZMShoppingCartItem</code>s.
     */
    function getItems() {
        $zenItems = $this->cart_->get_products();
        $items = array();
        foreach ($zenItems as $zenItem) {
            $item =& $this->create("ShoppingCartItem", $this, $zenItem);
            array_push($items, $item);
        }
        return $items;
    }

    /**
     * Get the cart total.
     *
     * @return float The cart total.
     */
    function getTotal() { return $this->cart_->show_total(); }

    /**
     * Get product attributes for the given item.
     *
     * @param ZmShoppingCartItem item The item.
     * @return array List of product attributes.
     */
    function _getItemAttributes($item) {
    global $zm_runtime;

        // collect attribute values for same attribute
        $attributesLookup = array();

        if (!isset($item->zenItem_['attributes']) || !is_array($item->zenItem_['attributes']))
            return $attributesLookup;

        // load attributes
        foreach ($item->zenItem_['attributes'] as $option => $type) {
            $sql = "select popt.products_options_name, poval.products_options_values_name,
                        pa.options_values_price, pa.price_prefix
                    from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval,
                       " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                    where pa.products_id = :productId
                    and pa.options_id = :option
                    and pa.options_id = popt.products_options_id
                    and pa.options_values_id = :type
                    and pa.options_values_id = poval.products_options_values_id
                    and popt.language_id = :languageId
                    and poval.language_id = :languageId";
            $sql = $this->db_->bindVars($sql, ":type", $type, "integer");
            $sql = $this->db_->bindVars($sql, ":productId", $item->getId(), "integer");
            $sql = $this->db_->bindVars($sql, ":option", $option, "integer");
            $sql = $this->db_->bindVars($sql, ":languageId", $zm_runtime->getLanguageId(), "integer");

            $results = $this->db_->Execute($sql);

            $name = $results->fields['products_options_name'];
            if (array_key_exists($name, $attributesLookup)) {
                $atname = $attributesLookup[$name];
            } else {
                $atname = str_replace(' ', '', $name);
                $$atname =& $this->create("Attribute", $option, $name, null);
                $attributesLookup[$name] = $atname;
            }

            $value = $results->fields['products_options_values_name'];
            if ($type == PRODUCTS_OPTIONS_VALUES_TEXT_ID) {
                // text is user input
                $value = $item->zenItem_['attributes_values'][$option];
            }
            $attributeValue =& $this->create("AttributeValue", $type, $value);

            $attributeValue->pricePrefix_ = $results->fields['options_values_price'];
            $attributeValue->price_ = $results->fields['options_values_price'];
            array_push($$atname->values_, $attributeValue);
        }
        $attributes = array();
        foreach ($attributesLookup as $name => $atname) {
            array_push($attributes, $$atname);
        }

        return $attributes;
    }

    /**
     * Get the customer comment.
     *
     * @return string The customer comment.
     */
    function getComment() { return isset($_SESSION['comments']) ?  $_SESSION['comments'] : ''; }

    /**
     * Get the selected shipping method id.
     *
     * @return int The shipping method id.
     */
    function getShippingMethodId() { return (isset($_SESSION['shipping']) && isset($_SESSION['shipping']['id'])) ? $_SESSION['shipping']['id'] : null; }

    /**
     * Get the id of the selected payment method.
     *
     * @return int The payment method id.
     */
    function getPaymentMethodId() { return isset($_SESSION['payment']) ? $_SESSION['payment'] : null; }

    /**
     * Get the selected shipping method.
     *
     * @return mixed The zen-cart shipping method.
     */
    function getShippingMethod() {
        $order =& new order();
        return array_key_exists('shipping_method', $order->info) ? $order->info['shipping_method'] : null;
    }

    /**
     * Get the selected payment type.
     *
     * @return ZMPaymentType The payment type.
     */
    function getPaymentType() {
        $payments =& $this->create("Payments");
        return $payments->getSelectedPaymentType();
    }

    /**
     * Checks if the cart has a shipping address.
     *
     * @return bool <code>true</code> if there is a shipping address, <code>false</code> if not.
     */
    function hasShippingAddress() { return !zm_is_empty($_SESSION['sendto']); }

    /**
     * Checks if the cart has a billing address.
     *
     * @return bool <code>true</code> if there is a billing address, <code>false</code> if not.
     */
    function hasBillingAddress() { return !zm_is_empty($_SESSION['billto']); }

    /**
     * Get the current shipping address.
     *
     * @return ZMAddress The shipping address.
     */
    function getShippingAddress() {
    global $zm_addresses;

        return $zm_addresses->getAddressForId($_SESSION['sendto']);
    }

    /**
     * Get the selected billing address.
     *
     * @return ZMAddress The billing address.
     */
    function getBillingAddress() {
    global $zm_addresses;

        return $zm_addresses->getAddressForId($_SESSION['billto']);
    }

    /**
     * Returns the URL for the actual order form.
     *
     * <p>An example for the actual order form might look similar to this:</p>
     * <pre>
     *   &lt;?php zm_secure_form($zm_cart-&gt;getOrderFormURL()) ?&gt;
     *     &lt;?php $zm_cart-&gt;getOrderFormContent() ?&gt;
     *     &lt;div class="btn"&gt;&lt;input type="submit" class="btn" value="&lt;?php zm_l10n("Confirm to order") ?&gt;" /&gt;&lt;/div&gt;
     *   &lt;/form&gt;
     * </pre>
     *
     * @return string The URL to be used for the actual order form.
     */
    function getOrderFormURL() {
    global $$_SESSION['payment'];
        $url = zm_secure_href(FILENAME_CHECKOUT_PROCESS, '', false);
        if (isset($$_SESSION['payment']->form_action_url)) {
            $url = $$_SESSION['payment']->form_action_url;
        }
        return $url;
    }

    /**
     * Returns the order form elements.
     *
     * @param bool echo If <code>true</code>, echo the code.
     * @return mixed The form content for the actual order process.
     * @see net.radebatz.zenmagick.ZMShoppingCart#getOrderFormURL
     */
    function getOrderFormContent($echo=true) {
        $payments = $this->_getPayments();
        $zenModules = $payments->getZenModules();
        $content = $zenModules->process_button();

        if ($echo) echo $content;
        return $content;
    }
    
    /**
     * Get zen-cart order totals.
     */
    function _getZenTotals() {
    global $order_total_modules;
        if (null == $this->zenTotals_) {
            $this->zenTotals_ = $order_total_modules;
            if (!isset($order_total_modules)) {
                zm_resolve_zc_class('order_total');
                $zenTotals =& new order_total();
            }
            zm_resolve_zc_class('order');
            $GLOBALS['order'] =& new order;
            $this->zenTotals_->process();
        }

        return $this->zenTotals_;
    }


    /**
     * Get the order totals.
     *
     * @return array List of <code>ZMOrderTotal</code> instances.
     */
    function getTotals() {
        $zenTotals = $this->_getZenTotals();
        $totals = array();
        foreach ($zenTotals->modules as $module) {
            $class = str_replace('.php', '', $module);
            $output = $GLOBALS[$class]->output;
            $type = substr($class, 3);
            //$size = sizeof($output);
            //echo "m:".$module." ".$size." ".$type."<br>";

            foreach ($output as $zenTotal) {
                //print_r($zenTotal);
                //echo "t:".$zenTotal."<br>";
                array_push($totals, $this->create("OrderTotal", $zenTotal['title'], $zenTotal['text'], $type));
            }
        }
        return $totals;
    }

    /**
     * Get payments.
     */
    function _getPayments() {
        if (null == $this->payments_) {
            $this->payments_ =& $this->create("Payments");
        }
        return $this->payments_;
    }

    /**
     * Generate the JavaScript for the payment form validation.
     *
     * @param bool echo If <code>true</code>, echo the code.
     * @return string Fully formatted JavaScript incl. of wrapping &lt;script&gt; tag.
     */
    function getPaymentsJavaScript($echo=true) {
        $payments = $this->_getPayments();
        $js = $payments->getPaymentsJavaScript(false);

        //XXX strip invalid script attribute
        $js = str_replace(' language="javascript"', '', $js);

        //XXX XHMTL does not know name attributes on form elements
        $js = str_replace('document.checkout_payment', 'document.forms.checkout_payment', $js);

        if ($echo) echo $js;
        return $js;
    }

    /**
     * Get a list of the available payment types.
     *
     * @return array List of <code>ZMPaymentType</code> instances.
     */
    function getPaymentTypes() {
        $payments = $this->_getPayments();
        return $payments->getPaymentTypes();
    }

    /**
     * Get list of available credit types; eg promo codes, etc.
     *
     * <p><strong>NOTE:</strong> This is also using <code>ZMPaymentType</code> as class
     * to handle the data. Only difference is that the payment type is used to reduce
     * the cart total.</p>
     *
     * @return array List of <code>ZMPaymentType</code> instances.
     */
    function getCreditTypes() {
        // looks suspiciously like getPaymentTypes in ZMPayments...
        $zenTotals = $this->_getZenTotals();
        $zenTypes = $zenTotals->credit_selection();
        $creditTypes = array();
        foreach ($zenTypes as $zenType) {
            $creditType =& $this->create("PaymentType", $zenType['id'], $zenType['module'], $zenType['redeem_instructions']);
            if (isset($zenType['credit_class_error'])) {
                $creditType->error_ = $zenType['credit_class_error'];
            }
            if (isset($zenType['fields'])) {
                foreach ($zenType['fields'] as $zenField) {
                    //XXX fix HTML
                    $field = str_replace('textfield', 'text', $zenField['field']);
                    $creditType->addField($this->create("PaymentField", $zenField['title'], $field));
                }
            }
            if (isset($zenType['checkbox'])) {
                //XXX fix HTML
                $checkbox = str_replace('textfield', 'text', $zenType['checkbox']);
                $pos = strpos( $checkbox, '<input');
                $title = trim(substr($checkbox, 0, $pos));
                $field = trim(substr($checkbox, $pos));
                //XXX fix submitFunction functionallity
                $field = str_replace('submitFunction()', "submitFunction(this, ".$this->getTotal().")", $field);
                $creditType->addField($this->create("PaymentField", $title, $field));
            }
            array_push($creditTypes, $creditType);
        }

        return $creditTypes;
    }
    
}

?>
