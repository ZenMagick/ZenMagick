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
 * Shopping cart.
 * <p>This is assuming a properly configured zen cart.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.model.checkout
 * @version $Id$
 */
class ZMShoppingCart extends ZMObject {
    private $cart_;
    private $zenTotals_;
    private $payments_;
    private $items_;
    private $helper_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->cart_ = $_SESSION['cart'];
        $this->zenTotals_ = null;
        $this->payments_ = null;
        $this->items_ = null;
        $this->helper_ = new ZMCheckoutHelper($this);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Check if the cart is empty.
     *
     * @return boolean <code>true</code> if the cart is empty, <code>false</code> if the cart is not empty.
     */
    public function isEmpty() { return 0 == $this->getSize(); }

    /**
     * Get the size of the cart.
     *
     * <p><strong>NOTE:</strong> This is the number of line items in the cart, not the total number of products.</p>
     *
     * @return int The number of different products in the cart.
     */
    public function getSize() { return count($this->getItems()); }

    /**
     * Get the carts weight.
     *
     * @return float The weight if the shopping cart.
     */
    public function getWeight() { return $this->cart_->show_weight(); }

    /**
     * Check for out of stock items.
     *
     * @return boolean <code>true</code> if the cart contains items that are out of stock,
     *  <code>false</code> if not.
     */
    public function hasOutOfStockItems() {
        foreach ($this->getItems() as $item) {
            if (!$item->isStockAvailable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for virtual cart.
     * 
     * @return boolean <code>true</code> if the cart is purely virtual.
     */
    public function isVirtual() {
        return $this->helper_->isVirtual();
    }

    /**
     * Set the cart items.
     *
     * @param array items List of <code>ZMShoppingCartItem</code>s.
     */
    public function setItems($items) {
        $this->items_ = $items;
    }

    /**
     * Get the items in the cart.
     *
     * @return array List of <code>ZMShoppingCartItem</code>s.
     */
    public function getItems() {
        if (null === $this->items_) {
            $this->items_ = array();
            if (null != $this->cart_) {
                $zenItems = $this->cart_->get_products();
                foreach ($zenItems as $zenItem) {
                    $item = ZMLoader::make("ShoppingCartItem", $zenItem);
                    $this->items_[$item->getId()] = $item;
                }
            }
        }
        return $this->items_;
    }

    /**
     * Get the cart subtotal.
     *
     * @return float The cart subtotal.
     */
    public function getSubTotal() { 
        $order = new order();
        return $order->info['subtotal'];
    }

    /**
     * Get the cart total.
     *
     * @return float The cart total.
     */
    public function getTotal() { return $this->cart_->show_total(); }

    /**
     * Get the owner's account id.
     *
     * @return int The account id.
     */
    public function getAccountId() {
        // TODO: ugh!
        return $_SESSION['customer_id'];
    }

    /**
     * Get the customer comment.
     *
     * @return string The customer comment.
     */
    public function getComment() { return isset($_SESSION['comments']) ?  $_SESSION['comments'] : ''; }

    /**
     * Get the selected shipping method id.
     *
     * @return int The shipping method id.
     */
    public function getShippingMethodId() { 
        return (isset($_SESSION['shipping']) && isset($_SESSION['shipping']['id'])) ? $_SESSION['shipping']['id'] : null;
    }

    /**
     * Get the id of the selected payment method.
     *
     * @return int The payment method id.
     */
    public function getPaymentMethodId() { 
        return isset($_SESSION['payment']) ? $_SESSION['payment'] : null;
    }

    /**
     * Get the selected shipping method.
     *
     * @return mixed The zen-cart shipping method.
     */
    public function getShippingMethod() {
    global $order;

        if (!isset($order)) {
            $order = new order();
        }
        return array_key_exists('shipping_method', $order->info) ? $order->info['shipping_method'] : null;
    }

    /**
     * Get the selected payment type.
     *
     * @return ZMPaymentType The payment type.
     */
    public function getPaymentType() {
        $payments = ZMLoader::make("Payments");
        return $payments->getSelectedPaymentType();
    }

    /**
     * Checks if the cart has a shipping address.
     *
     * @return boolean <code>true</code> if there is a shipping address, <code>false</code> if not.
     */
    public function hasShippingAddress() { return !empty($_SESSION['sendto']); }

    /**
     * Checks if the cart has a billing address.
     *
     * @return boolean <code>true</code> if there is a billing address, <code>false</code> if not.
     */
    public function hasBillingAddress() { return !empty($_SESSION['billto']); }

    /**
     * Get the current shipping address.
     *
     * @return ZMAddress The shipping address.
     */
    public function getShippingAddress() {
        return ZMAddresses::instance()->getAddressForId($_SESSION['sendto']);
    }

    /**
     * Set the current shipping address id.
     *
     * @param int addressId The new shipping address id.
     */
    public function setShippingAddressId($addressId) {
        $_SESSION['sendto'] = $addressId;
        $_SESSION['shipping'] = '';
    }

    /**
     * Get the selected billing address.
     *
     * @return ZMAddress The billing address.
     */
    public function getBillingAddress() {
        return ZMAddresses::instance()->getAddressForId($_SESSION['billto']);
    }

    /**
     * Set the selected billing address.
     *
     * @param int addressId The billing address id.
     */
    public function setBillingAddressId($addressId) {
        if (isset($_SESSION['billto']) && $_SESSION['billto'] != $addressId) {
            $_SESSION['payment'] = '';
        }
        $_SESSION['billto'] = $addressId;
    }

    /**
     * Returns the URL for the actual order form.
     *
     * <p>An example for the actual order form might look similar to this:</p>
     * <pre>
     *   &lt;?php $form->open($zm_cart-&gt;getOrderFormURL(), '', true) ?&gt;
     *     &lt;?php $shoppingCart-&gt;getOrderFormContent() ?&gt;
     *     &lt;div class="btn"&gt;&lt;input type="submit" class="btn" value="&lt;?php zm_l10n("Confirm to order") ?&gt;" /&gt;&lt;/div&gt;
     *   &lt;/form&gt;
     * </pre>
     *
     * @return string The URL to be used for the actual order form.
     */
    function getOrderFormURL() {
    global $$_SESSION['payment'];
        $url = ZMToolbox::instance()->net->url(FILENAME_CHECKOUT_PROCESS, '', true, false);
        if (isset($$_SESSION['payment']->form_action_url)) {
            $url = $$_SESSION['payment']->form_action_url;
        }
        return $url;
    }

    /**
     * Returns the order form elements.
     *
     * @param boolean echo If <code>true</code>, echo the code.
     * @return mixed The form content for the actual order process.
     * @see org.zenmagick.ZMShoppingCart#getOrderFormURL
     */
    function getOrderFormContent($echo=ZM_ECHO_DEFAULT) {
        $payments = $this->_getPayments();
        $zenModules = $payments->getZenModules();
        $content = $zenModules->process_button();

        if ($echo) echo $content;
        return $content;
    }
    
    /**
     * Get zen-cart order totals.
     */
    protected function _getZenTotals() {
    global $order_total_modules;

        if (null == $this->zenTotals_) {
            $this->zenTotals_ = $order_total_modules;
            if (!isset($order_total_modules)) {
                ZMTools::resolveZCClass('order_total');
                //TODO:?????
                $zenTotals = new order_total();
            }
            if (!isset($GLOBALS['order']) || !is_object($GLOBALS['order'])) {
                ZMTools::resolveZCClass('order');
                $GLOBALS['order'] = new order();
            }
            $this->zenTotals_->process();
        }

        return $this->zenTotals_;
    }


    /**
     * Get the order totals.
     *
     * @return array List of <code>ZMOrderTotal</code> instances.
     */
    public function getTotals() {
        $zenTotals = $this->_getZenTotals();
        $totals = array();
        foreach ($zenTotals->modules as $module) {
            $class = str_replace('.php', '', $module);
            $output = $GLOBALS[$class]->output;
            $type = substr($class, 3);
            foreach ($output as $zenTotal) {
                $totals[] = ZMLoader::make("OrderTotal", $zenTotal['title'], $zenTotal['text'], $zenTotal['value'], $type);
            }
        }
        return $totals;
    }

    /**
     * Get payments.
     */
    protected function _getPayments() {
        if (null == $this->payments_) {
            $this->payments_ = ZMLoader::make("Payments");
        }
        return $this->payments_;
    }

    /**
     * Generate the JavaScript for the payment form validation.
     *
     * @param boolean echo If <code>true</code>, echo the code.
     * @return string Fully formatted JavaScript incl. of wrapping &lt;script&gt; tag.
     */
    function getPaymentsJavaScript($echo=ZM_ECHO_DEFAULT) {
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
            $creditType = ZMLoader::make("PaymentType", $zenType['id'], $zenType['module'], $zenType['redeem_instructions']);
            if (isset($zenType['credit_class_error'])) {
                $creditType->error_ = $zenType['credit_class_error'];
            }
            if (isset($zenType['fields'])) {
                foreach ($zenType['fields'] as $zenField) {
                    //XXX fix HTML
                    $field = str_replace('textfield', 'text', $zenField['field']);
                    $creditType->addField(ZMLoader::make("PaymentField", $zenField['title'], $field));
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
                $creditType->addField(ZMLoader::make("PaymentField", $title, $field));
            }
            array_push($creditTypes, $creditType);
        }

        return $creditTypes;
    }
    

    /**
     * Check whether the cart is ready for checkout or not.
     *
     * @return boolean <code>true</code> if the cart is ready or checkout, <code>false</code> if not.
     */
    public function readyForCheckout() {
        return $this->helper_->readyForCheckout();
    }

    /**
     * Adjust the quantity based on the quantity settings.
     *
     * @param mixed quantity The quantity.
     * @return The adjusted quantity.
     */
    function adjustQty($quantity) {
        $digits = ZMSettings::get('qtyDecimals');
        if (0 != $digits) {
            if (strstr($quantity, '.')) {
                // remove leading '0'
                $quantity = preg_replace('/[0]+$/','', $quantity);
            }
        } else {
            if ($quantity != round($quantity, 0)) {
                $quantity = round($quantity, 0);
            }
        }

        return $quantity;
    }

    /**
     * Get the cart quantity for the given product.
     *
     * @param string productId The product id.
     * @param boolean qtyMixed Indicates whether to return just the specified product variation quantity, or
     *  the quantity across all variations; default is <code>false</code>.
     * @return int The number of products in the cart.
     */
    function getQty($productId, $isQtyMixed=false) {
        $contents = $this->cart_->contents;

        if (!is_array($contents)) {
            return 0;
        }

        if (!$isQtyMixed) {
            return $this->cart_->get_quantity($productId);
        }

        $baseProductId = ZMShoppingCart::base_product_id($productId);

        $qty = 0;
        foreach ($contents as $pid) {
            $bpid = ZMShoppingCart::base_product_id($pid);
            if ($bpid == $baseProductId) {
                $qty += $contents[$pid]['qty'];
            }
        }

        return $qty;
    }

    /**
     * Add a product in the given quantity.
     *
     * <p><strong>Doesn't support uploads (yet)</strong>.</p>
     *
     * <p>If <em>isSanitizeAttributes</em> is set to <code>true</code>, missing
     * attributes will be added automatically and set to defaults.</p>
     *
     * @param int productId The product id.
     * @param int quantity The quantity; default is <code>1</code>.
     * @param array attributes Optional list of attributes; key is the attribute id, the value can 
     *  be either an int or <code>ZMAttributeValue</code>; default is an empty <code>array</code>.
     * @param boolean notify Flag whether to add the product to the notify list or not; default is <code>true</code>
     * @return boolean <code>true</code> if the product was added, <code>false</code> if not.
     */
    public function addProduct($productId, $quantity=1, $attributes=array(), $notify=true) {
        $product = ZMProducts::instance()->getProductForId($productId);
        if (null == $product) {
            ZMLogging::instance()->log('failed to add product to cart; productId='.$productId, ZMLogging::ERROR);
            return false;
        }
        $attributes = $this->sanitize_attributes($product, $attributes);
        $attributes = $this->prepare_uploads($product, $attributes);

        //TODO: zc: comp
        $attributes = (0 < count($attributes) ? $attributes : '');
        $sku = ZMShoppingCart::product_variation_id($productId, $attributes);

        $maxOrderQty = $product->getMaxOrderQty();
        $cartQty = $this->getQty($sku, $product->isQtyMixed());
        $adjustedQty = $this->adjustQty($quantity);

        if (0 != $maxOrderQty && $cartQty >= $maxOrderQty) {
            // TODO: error message/status
            return false;
        }

        // adjust quantity if needed
        if (($adjustedQty + $cartQty > $maxOrderQty) && 0 != $maxOrderQty) {
            // TODO: message
            $adjustedQty = $maxOrderQty - $cartQty;
        }

        $this->cart_->add_cart($productId, $cartQty + $adjustedQty, $attributes, $notify);

        return true;
    }

    /**
     * Remove item from cart.
     *
     * @param string productId The product id.
     * @return boolean <code>true</code> if the product was removed, <code>false</code> if not.
     */
    public function removeProduct($productId) {
        if (null !== $productId) {
            $this->cart_->remove($productId);
            return true;
        }

        return false;
    }

    /**
     * Update item.
     *
     * @param string sku The product sku.
     * @param int quantity The quantity.
     * @param boolean notify Flag whether to add the product to the notify list or not; default is <code>true</code>
     * @return boolean <code>true</code> if the product was updated, <code>false</code> if not.
     */
    public function updateProduct($sku, $quantity, $notify=true) {
        if (null !== $sku && null !== $quantity) {
            if (0 == $quantity) {
                return $this->removeProduct($sku);
            }


            $productId = ZMShoppingCart::base_product_id($sku);
            $product = ZMProducts::instance()->getProductForId($productId);

            $maxOrderQty = $product->getMaxOrderQty();
            $adjustedQty = $this->adjustQty($quantity);

            if (0 != $maxOrderQty && $adjustedQty >= $maxOrderQty) {
                // TODO: error message/status
                return false;
            }

            // adjust quantity if needed
            if (($adjustedQty > $maxOrderQty) && 0 != $maxOrderQty) {
                // TODO: message
                $adjustedQty = $maxOrderQty;
            }

            $this->cart_->add_cart($sku, $adjustedQty, $attributes, $notify);

            return true;
        }

        return false;
    }

    /**
     * Get the tax address for this cart.
     *
     * @return ZMAddress The tax address.
     */
    public function getTaxAddress() {
        switch (ZMSettings::get('productTaxBase')) {
            case ZMTaxRates::TAX_BASE_SHIPPING:
                return $this->getShippingAddress();
            case ZMTaxRates::TAX_BASE_BILLING:
                return $this->getBillingAddress();
            case ZMTaxRates::TAX_BASE_STORE:
                $address = $this->getBillingAddress();
                if ($address->getZoneId() != ZMSettings::get('storeZone')) {
                    return $this->getShippingAddress();
                }
                return $address;
        }

        ZMLogging::instance()->log('invalid productTaxBase!', ZMLogging::ERROR);
        return null;
    }

    /**
     * Prepare file uploads.
     *
     * <p>Check for uploaded files and prepare attributes accordingly.</p>
     *
     * @param ZMProduct product The product.
     * @param array attributes The given attributes.
     * @return array A set of valid attribute values for the given product.
     * @todo IMPLEMENT!
     */
    function prepare_uploads($product, $attributes=array()) {
        $uploads = 0;
        foreach ($attributes as $name => $value) {
            if (ZMTools::startsWith($name, ZMSettings::get('uploadOptionPrefix'))) {
                ++$uploads;
            }
        }

        if (0 < $uploads) {
            //TODO: handle file uploads
        }

        return $attributes;
    }

    /**
     * Sanitize the given attributes and add default values if attributes/values invalid/missing.
     *
     * @param ZMProduct product The product.
     * @param array attributes The given attributes.
     * @return array A set of valid attribute values for the given product.
     * @todo return note of changes made
     */
    function sanitize_attributes($product, $attributes=array()) {
        //TODO: where should this actually be? attributes, rules, cart, products?
        if (!ZMSettings::get('isSanitizeAttributes')) {
            return $attributes;
        }

        if (!$product->hasAttributes()) {
            return array();
        }

        $defaultAttributes = $product->getAttributes();

        // check for valid values
        $validAttributeIds = array();
        foreach ($defaultAttributes as $attribute) {
            $attributeId = $attribute->getId();
            if (ZMTools::inArray($attribute->getType(), array(PRODUCTS_OPTIONS_TYPE_TEXT, PRODUCTS_OPTIONS_TYPE_FILE))) {
                $attributeId = ZMSettings::get('textOptionPrefix') . $attributeId;
            }
            $validAttributeIds[$attributeId] = $attributeId;
            if (!array_key_exists($attributeId, $attributes)) {
                // missing attribute
                $defaultId = null;
                // try to find the default value
                foreach ($attribute->getValues() as $value) {
                    if (null === $defaultId) {
                        // use first as default if default is not configured
                        $defaultId = $value->getId();
                    }
                    if ($value->isDefault()) {
                        $defaultId = $value->getId();
                        break;
                    }
                }

                if (ZMTools::inArray($attribute->getType(), array(PRODUCTS_OPTIONS_TYPE_RADIO, PRODUCTS_OPTIONS_TYPE_SELECT))) {
                    // use default id for radio and select
                    $attributes[$attributeId] = $defaultId;
                } else if (ZMTools::inArray($attribute->getType(), array(PRODUCTS_OPTIONS_TYPE_TEXT, PRODUCTS_OPTIONS_TYPE_FILE))) {
                    // use emtpy string for text input attributes
                    $attributes[$attributeId] = '';
                }
            } else {
                if (ZMTools::inArray($attribute->getType(), array(PRODUCTS_OPTIONS_TYPE_RADIO, PRODUCTS_OPTIONS_TYPE_SELECT))) {
                    // validate single non input attributes
                    $defaultId = null;
                    $isValid = false;
                    foreach ($attribute->getValues() as $value) {
                        if ($value->isDefault()) {
                            $defaultId = $value->getId();
                        }
                        if ($attributes[$attributeId] == $value->getId()) {
                            $isValid = true;
                            break;
                        }
                    }
                    if (!$isValid) {
                        // use default
                        $attributes[$attributeId] = $defaultId;
                    }
                } else if (PRODUCTS_OPTIONS_TYPE_CHECKBOX == $attribute->getType()) {
                    // validate multi non input attributes
                    foreach ($attributes[$attributeId] as $avid => $attrValue) {
                        $isValid = false;
                        foreach ($attribute->getValues() as $value) {
                            if ($attrValue == $value->getId()) {
                                $isValid = true;
                                break;
                            }
                        }
                        if (!$isValid) {
                            unset($attributes[$attributeId][$avid]);
                            break;
                        }
                    }
                }
            }
        }

        // strip invalid attributes
        foreach ($attributes as $id => $value) {
            if (!array_key_exists($id, $validAttributeIds)) {
                unset($attributes[$id]);
            }
        }

        return $attributes;
    }

    /**
     * Extract the base product id from a given string.
     *
     * @param string productId The full product id incl. attribute suffix.
     * @return int The product id.
     */
    public static function base_product_id($productId) {
        $arr = explode(':', $productId);
        return (int) $arr[0];
    }


    /**
     * Reverse of <code>base_product_id</code>, ie. the <em>sku</em>.
     *
     * <p>Creates a unique id for the given product variation.</p>
     *
     * <p>Attributes are sorted using <code>krsort(..)</code> so to be compatible
     * for different attribute orders.</p>
     *
     * @param string productId The full product id incl. attribute suffix.
     * @param array attrbutes Additional product attributes.
     * @return string The product id.
     * @todo currently uses <code>zen_get_uprid(..)</code>...
     */
    public static function product_variation_id($productId, $attributes=array()) {
return zen_get_uprid($productId, $attributes);
        $fullProductId = $productId;

        if (is_array($attributes) && 0 < count($attributes) && !strstr($productId, ':')) {
            krsort($attributes);
            $s = $productId;
            foreach ($attributes as $id => $value) {
	              if (is_array($value)) {
                    foreach ($value as $vid => $vval) {
                        $s .= '{' . $id . '}' . trim($vid);
                    }
                } else {
                    $s .= '{' . $id . '}' . trim($value);
                }
            }
            $fullProductId .= ':' . md5($s);
        }

        return $fullProductId;
    }


}

?>
