<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 *
 * <p>This class is assuming a properly configured zen cart.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.checkout
 */
class ZMShoppingCart extends ZMObject {
    public $cart_;
    private $zenTotals_;
    private $items_;
    private $helper_;
    private $comment_;
    private $accountId_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->cart_ = $_SESSION['cart'];
        $this->setComment(isset($_SESSION['comments']) ?  $_SESSION['comments'] : '');
        $this->setAccountId(isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 0);
        $this->zenTotals_ = null;
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
     * Calculate cart hash.
     *
     * <p>This hash can be used to validate that the cart didn't change during checkout.
     *
     * @return string A hash.
     */
    public function getHash() {
        $s = '';
        foreach ($this->getItems() as $item) {
            $s .= ';'.$item->getQuantity().":".$item->getId();
        }

        return md5($s);
    }

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
        ZMTools::resolveZCClass('order');
        $order = new order();
        return $order->info['subtotal'];
    }

    /**
     * Get the cart total.
     *
     * @return float The cart total.
     */
    public function getTotal() {
        return $this->cart_->show_total();
    }

    /**
     * Get the cart owner's account id.
     *
     * @return int The account id.
     */
    public function getAccountId() {
        return $this->accountId_;
    }

    /**
     * Set the cart owner's account id.
     *
     * @param int accountId The account id.
     */
    public function setAccountId($accountId) {
        $this->accountId_ = $accountId;
    }

    /**
     * Get optional customer comment.
     *
     * @return string The customer comment.
     */
    public function getComment() {
        return $this->comment_;
    }

    /**
     * Set the customer comment.
     *
     * @param string comment The customer comment.
     */
    public function setComment($comment) {
        $this->comment_ = $comment;
    }

    /**
     * Get a list of all available shipping providers.
     *
     * @return array List of <code>ZMShippingProvider</code> instances.
     */
    public function getShippingProviders() {
        return ZMShippingProviders::instance()->getShippingProviders();
    }

    /**
     * Get all available methods of the given provider for this cart.
     *
     * @param ZMShippingProvider provider The provider; default is <code>null</code> for all.
     * @return array List of <code>ZMShippingmethod</code> instances.
     */
    public function getMethodsForProvider($provider=null) {
        if (null != $provider) {
            return $provider->getShippingMethods($this, $this->getShippingAddress());
        } else {
            $methods = array();
            foreach ($this->getShippingProviders() as $provider) {
                $methods = array_merge($methods, $provider->getShippingMethods($this, $this->getShippingAddress()));
            }

            return $methods;
        }
    }

    /**
     * Get the selected shipping method id.
     *
     * <p><strong>NOTE: If a value is returned it will be a <em>combined key</em> in the form
     * <em>providerId]_[methodId]</em>.</p>
     *
     * @return int The shipping method id.
     */
    public function getSelectedShippingMethodId() {
        return (isset($_SESSION['shipping']) && isset($_SESSION['shipping']['id'])) ? $_SESSION['shipping']['id'] : null;
    }

    /**
     * Get the selected shipping method.
     *
     * @return ZMShippingProvider The selected shipping provider or <code>null</code>.
     */
    public function getSelectedShippingMethod() {
        $shippingMethodId = $this->getSelectedShippingMethodId();
        if (null == $shippingMethodId) {
            return null;
        }
        $token = explode('_', $shippingMethodId);
        if (2 != count($token)) {
            return null;
        }
        if (null == ($shippingProvider = ZMShippingProviders::instance()->getShippingProviderForId($token[0], true))) {
            return null;
        }
        return $shippingProvider->getShippingMethodForId($token[1], $this);
    }

    /**
     * Set the selected shipping method.
     *
     * @param ZMShippingMethod method The shipping method to use.
     */
    public function setShippingMethod($method) {
        $_SESSION['shipping'] = array(
            'id' => $method->getShippingId(),
            'title' => $method->getName(),
            'cost' => $method->getCost()
        );
    }

    /**
     * Get a list of the available payment types.
     *
     * @return array List of <code>ZMPaymentType</code> instances.
     */
    public function getPaymentTypes() {
        return $this->helper_->getPaymentTypes();
    }

    /**
     * Get the id of the selected payment type.
     *
     * @return int The payment type id.
     */
    public function getPaymentTypeId() {
        return isset($_SESSION['payment']) ? $_SESSION['payment'] : null;
    }

    /**
     * Get the selected payment type.
     *
     * @return ZMPaymentType The payment type or <code>null</code>.
     */
    public function getPaymentType() {
        $paymentType = ZMPaymentTypes::instance()->getPaymentTypeForId($this->getPaymentTypeId());
        return $paymentType;
    }

    /**
     * Returns the URL for the actual order form.
     *
     * <p>An example for the actual order form might look similar to this:</p>
     * <pre>
     *   &lt;?php echo $form->open($zm_cart-&gt;getOrderFormURL(), '', true) ?&gt;
     *     &lt;?php $shoppingCart-&gt;getOrderFormContent() ?&gt;
     *     &lt;div class="btn"&gt;&lt;input type="submit" class="btn" value="&lt;?php echo _zm("Confirm to order") ?&gt;" /&gt;&lt;/div&gt;
     *   &lt;/form&gt;
     * </pre>
     *
     * @param ZMRequest request The current request.
     * @return string The URL to be used for the actual order form.
     */
    public function getOrderFormUrl($request) {
        return $this->getPaymentType()->getOrderFormUrl($request);
    }

    /**
     * Returns the order form elements.
     *
     * @param ZMRequest request The current request.
     * @return mixed The form content for the actual order process form.
     */
    public function getOrderFormContent($request) {
        return $this->getPaymentType()->getOrderFormContent($request);
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
     * Get zen-cart order totals.
     */
    protected function _getZenTotals() {
    global $order, $order_total_modules;

        if (null == $this->zenTotals_) {
            $this->zenTotals_ = $order_total_modules;
            if (!isset($order_total_modules)) {
                ZMTools::resolveZCClass('order_total');
                $this->zenTotals_ = new order_total();
            }
            if (!isset($GLOBALS['order']) || !is_object($GLOBALS['order'])) {
                ZMTools::resolveZCClass('order');
                $order = new order();
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
                $totals[] = ZMLoader::make("OrderTotalLine", $zenTotal['title'], $zenTotal['text'], $zenTotal['value'], $type);
            }
        }
        return $totals;
    }

    /**
     * Generate the JavaScript for the payment form validation.
     *
     * <p>This method is only defined in <em>storefront</em> context.</p>
     *
     * @param ZMRequest request The current request.
     * @return string Fully formatted JavaScript incl. of wrapping &lt;script&gt; tag.
     */
    public function getPaymentFormValidationJS($request) {
        //TODO: move here...
        return ZMPaymentTypes::instance()->getPaymentFormValidationJS($request);
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
        // looks suspiciously like getPaymentTypes in ZMPaymentTypes...
        $zenTotals = $this->_getZenTotals();
        $zenTypes = $zenTotals->credit_selection();
        $creditTypes = array();
        foreach ($zenTypes as $zenType) {
            $creditType = ZMLoader::make("CreditTypeWrapper", $zenType['id'], $zenType['module'], $zenType['redeem_instructions']);
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
     * @todo move to helper?
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
     * @param string itemId The cart item/sku id.
     * @param boolean qtyMixed Indicates whether to return just the specified product variation quantity, or
     *  the quantity across all variations; default is <code>false</code>.
     * @return int The number of products in the cart.
     */
    function getQty($itemId, $isQtyMixed=false) {
        $contents = $this->cart_->contents;

        if (!is_array($contents)) {
            return 0;
        }

        if (!$isQtyMixed) {
            return $this->cart_->get_quantity($itemId);
        }

        $baseProductId = self::extractBaseProductId($itemId);

        $qty = 0;
        foreach ($contents as $pid) {
            $bpid = self::extractBaseProductId($pid);
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
        $sku = self::mkItemId($productId, $attributes);

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


            $productId = self::extractBaseProductId($sku);
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
            if (ZMLangUtils::startsWith($name, ZMSettings::get('uploadOptionPrefix'))) {
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
            if (ZMLangUtils::inArray($attribute->getType(), array(PRODUCTS_OPTIONS_TYPE_TEXT, PRODUCTS_OPTIONS_TYPE_FILE))) {
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

                if (ZMLangUtils::inArray($attribute->getType(), array(PRODUCTS_OPTIONS_TYPE_RADIO, PRODUCTS_OPTIONS_TYPE_SELECT))) {
                    // use default id for radio and select
                    $attributes[$attributeId] = $defaultId;
                } else if (ZMLangUtils::inArray($attribute->getType(), array(PRODUCTS_OPTIONS_TYPE_TEXT, PRODUCTS_OPTIONS_TYPE_FILE))) {
                    // use emtpy string for text input attributes
                    $attributes[$attributeId] = '';
                }
            } else {
                if (ZMLangUtils::inArray($attribute->getType(), array(PRODUCTS_OPTIONS_TYPE_RADIO, PRODUCTS_OPTIONS_TYPE_SELECT))) {
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
     * Extract the base product id from a given cart item id.
     *
     * @param string itemId The full shopping cart item id incl. attribute suffix.
     * @return int The product id.
     */
    public static function extractBaseProductId($productId) {
        $arr = explode(':', $productId);
        return (int) $arr[0];
    }


    /**
     * Create unique cart item/sku id, based on the base product id and attribute information.
     *
     * <p>This is the reverse of <code>extractBaseProductId</code>.</p>
     *
     * <p>Attributes are sorted using <code>krsort(..)</code> so to be compatible
     * for different attribute orders.</p>
     *
     * @param string productId The full product id incl. attribute suffix.
     * @param array attrbutes Additional product attributes.
     * @return string The product id.
     * @todo currently uses <code>zen_get_uprid(..)</code>...
     */
    public static function mkItemId($productId, $attributes=array()) {
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
