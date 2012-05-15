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

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\apps\store\utils\CheckoutHelper;

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
    private $session;
    private $zenTotals_;
    private $items_;
    private $checkoutHelper;
    private $comments_;
    private $accountId_;
    private $selectedPaymentType_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->session = Runtime::getContainer()->get('session');
        $cart = $this->session->getValue('cart');
        // TODO: quick fix using 'new' until we drop zencart's shopping cart class altogether
        $this->cart_ = (null != $cart) ? $cart : new shoppingCart;
        // TODO: remove
        $comments = $this->session->getValue('comments');
        $this->setComments(null !== $comments ? $comments : '');
        $accountId = $this->session->getValue('customer_id');
        $this->setAccountId(null !== $accountId ? $accountId : 0);
        $this->zenTotals_ = null;
        $this->items_ = null;
        $this->selectedPaymentType_ = null;
    }


    /**
     * Set the checkout helper for this cart.
     *
     * @param CheckoutHelper checkoutHelper The checkout helper.
     */
    public function setCheckoutHelper(CheckoutHelper $checkoutHelper) {
        $checkoutHelper->setShoppingCart($this);
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * Get the checkout helper for this cart.
     *
     * @return CheckoutHelper The checkout helper.
     */
    public function getCheckoutHelper() {
        return $this->checkoutHelper;
    }

    /**
     * Set the session for this cart.
     *
     * @param Session session The session.
     */
    public function setSession($session) {
        $this->session = $session;
    }

    /**
     * Get the session for this cart.
     *
     * @return Session The session.
     */
    public function getSession() {
        return $this->session;
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
     * Get the cart type.
     *
     * @return string The cart type; one of <em>physical</em>, <em>mixed</em>, <em>virtual</em>.
     */
    public function getType() {
        return $this->checkoutHelper->getType();
    }

    /**
     * Check for virtual cart.
     *
     * @return boolean <code>true</code> if the cart is purely virtual.
     */
    public function isVirtual() {
        return $this->checkoutHelper->isVirtual();
    }

    /**
     * Set the cart items.
     *
     * @param array items List of <code>ZMShoppingCartItem</code>s.
     */
    public function setItems($items) {
        // invalidate totals
        $this->zenTotals_ = null;

        $this->items_ = $items;
        foreach ($this->items_ as $item) {
            $item->setShoppingCart($this);
        }
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
if (false) {
                // 1) iterate and populate items array
                $cartContents = (array) $this->cart_->contents;
                foreach ($cartContents as $id => $itemData) {
                    if (empty($id)) { continue; }
                    $item = new ZMShoppingCartItem($this);
                    $item->setContainer($this->container);
                    $item->setId($id);
                    $item->populateAttributes($itemData);
                    $item->setQuantity($this->adjustQty($itemData['qty']));
                    $this->items_[$item->getId()] = $item;
                }

                // 2) iterate again - now we can calculate quantities properly...
                foreach ($this->items_ as $id => $item) {
                    $product = $item->getProduct();
                    $offers = $product->getOffers();

                    // todo: cleanup item methods a bit to make more sense for this
//echo '<hr>ZM: '.$item->getId(); var_dump($item->getAttributesOneTimePrice(false)); echo '<hr>';
                    $item->setOneTimeCharge($product->getOneTimeCharge() + $item->getAttributesOneTimePrice(false));
                    $price = $product->getPrice(false);
//echo '<hr>ZM i: '.$item->getId(); echo ' '.$price.' + '.$item->getAttributesPrice(false); echo '<hr>';

                    // qty depends om qtyMixed option on base product
                    $cartQty = $item->getQuantity();//$this->getItemQuantityFor($id, $product->isQtyMixedDiscount());
//var_dump($item->getId()); var_dump($cartQty); var_dump($this->getItemQuantityFor($id, $item->getQuantity()));
                    $cartQty= $this->getItemQuantityFor($id, $item->getQuantity());
                    if (null != ($quantityDiscount = $offers->getQuantityDiscountFor($cartQty, false))) {
                        $price = $quantityDiscount->getPrice();
                    }
//echo '<hr>ZM i: '.$item->getId(); echo ' '.$price.' + '.$item->getAttributesPrice(false); echo '<hr>';
                    $item->setItemPrice($price + $item->getAttributesPrice(false));

                    $this->items_[$item->getId()] = $item;
                }
} else {
                $zenItems = $this->cart_->get_products();
                foreach ($zenItems as $zenItem) {
                    $item = new ZMShoppingCartItem($this, $zenItem);
                    $item->setContainer($this->container);
                    $this->items_[$item->getId()] = $item;
                }
            }
}

            if ($this->container->get('settingsService')->get('apps.store.assertZencart', false)) {
                $zenItems = $this->cart_->get_products();
                foreach ($this->items_ as $item) {
                    $itemId = $item->getId();
                    $product = $item->getProduct();
                    if ($this->cart_->get_quantity($itemId) != $this->getItemQuantityFor($itemId, $product->isQtyMixed())) {
                        echo 'cart: get_quantity diff! cart: ';var_dump($this->cart_->get_quantity($itemId));echo 'my: ';var_dump($this->getItemQuantityFor($itemId, $product->isQtyMixed()));echo '<br>';
                    }
                    if ($this->cart_->in_cart_mixed($itemId) != $this->getItemQuantityFor($itemId, $product->isQtyMixed())) {
                        echo 'cart: in_cart_mixed diff! cart: ';var_dump($this->cart_->in_cart_mixed($itemId));echo 'my: ';var_dump($this->getItemQuantityFor($itemId, $product->isQtyMixed()));echo '<br>';
                    }
                    // prices
                    foreach ($zenItems as $zi) {
                        if ($zi['id'] == $item->getId()) {
                            if (round($item->getItemPrice(),2) != round($zi['final_price'],2)) {
                                echo 'cart: item price differ: '.$zi['id'].': ZM: '.$item->getItemPrice().', zc: '.$zi['final_price'].'<br>';
                            }
                            if (round($item->getOneTimeCharge(),2) != round($zi['onetime_charges'],2)) {
                                echo 'cart: onetime charge differ: '.$zi['id'].': ZM: '.$item->getOneTimeCharge().', zc: '.$zi['onetime_charges'].'<br>';
                            }
                        }
                    }
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
        $subtotal = 0;
        foreach ($this->getItems() as $item) {
            $itemTotal = $item->getItemTotal(false) + $item->getOneTimeCharge(false);
            $subtotal += $item->getTaxRate()->addTax($itemTotal);
        }
        return $subtotal;
    }

    /**
     * Get the cart total.
     *
     * @return float The cart total.
     */
    public function getTotal() {
        foreach ($this->getTotals() as $orderTotal) {
            if ('total' == $orderTotal->getType()) {
                return $orderTotal->getAmount();
            }
        }
        return 0;
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
    public function getComments() {
        return $this->comments_;
    }

    /**
     * Set the customer comment.
     *
     * @param string comments The customer comment.
     */
    public function setComments($comments) {
        $this->comments_ = $comments;
        //TODO: remove
        $this->session->setValue('comments', $comments);
    }

    /**
     * Get a list of all available shipping providers.
     *
     * @return array List of <code>ZMShippingProvider</code> instances.
     */
    public function getShippingProviders() {
        return $this->container->get('shippingProviderService')->getShippingProviders();
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
     * @return int The shipping method id or <code>null</code>.
     */
    public function getSelectedShippingMethodId() {
        if (null !== ($shipping = $this->session->getValue('shipping')) && is_array($shipping)) {
            return $shipping['id'];
        }
        return null;
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
        if (null == ($shippingProvider = $this->container->get('shippingProviderService')->getShippingProviderForId($token[0], true))) {
            return null;
        }
        return $shippingProvider->getShippingMethodForId($token[1], $this);
    }

    /**
     * Set the selected shipping method.
     *
     * @param ZMShippingMethod method The shipping method to use.
     */
    public function setSelectedShippingMethod($method) {
        // invalidate totals
        $this->zenTotals_ = null;

        $this->session->setValue('shipping', array(
            'id' => $method->getShippingId(),
            'title' => $method->getName(),
            'cost' => $method->getCost()
        ));
    }

    /**
     * Get a list of the available payment types.
     *
     * @return array List of <code>ZMPaymentType</code> instances.
     */
    public function getPaymentTypes() {
        return $this->checkoutHelper->getPaymentTypes();
    }

    /**
     * Get the id of the selected payment type.
     *
     * @return int The payment type id.
     */
    public function getSelectedPaymentTypeId() {
        return $this->session->getValue('payment');
    }

    /**
     * Get the selected payment type.
     *
     * @return ZMPaymentType The payment type or <code>null</code>.
     */
    public function getSelectedPaymentType() {
        if (null == $this->selectedPaymentType_) {
            $this->selectedPaymentType_ = $this->container->get('paymentTypeService')->getPaymentTypeForId($this->getSelectedPaymentTypeId());
            if (null != $this->selectedPaymentType_) {
                $this->selectedPaymentType_->prepare();
            }
        }
        return $this->selectedPaymentType_;
    }

    /**
     * Set the selected payment type.
     *
     * @param ZMPaymentType paymentType The payment type.
     */
    public function setSelectedPaymentType($paymentType) {
        // invalidate totals
        $this->zenTotals_ = null;

        $this->selectedPaymentType_ = $paymentType;
        $this->selectedPaymentType_->prepare();
        $this->session->setValue('payment', $paymentType->getId());
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
        return $this->getSelectedPaymentType()->getOrderFormUrl($request);
    }

    /**
     * Returns the order form elements.
     *
     * @param ZMRequest request The current request.
     * @return mixed The form content for the actual order process form.
     */
    public function getOrderFormContent($request) {
        return $this->getSelectedPaymentType()->getOrderFormContent($request);
    }

    /**
     * Checks if the cart has a shipping address.
     *
     * @return boolean <code>true</code> if there is a shipping address, <code>false</code> if not.
     */
    public function hasShippingAddress() { return null !== $this->session->getValue('sendto'); }

    /**
     * Checks if the cart has a billing address.
     *
     * @return boolean <code>true</code> if there is a billing address, <code>false</code> if not.
     */
    public function hasBillingAddress() { return null !== $this->session->getValue('billto'); }

    /**
     * Get the current shipping address.
     *
     * @return ZMAddress The shipping address.
     */
    public function getShippingAddress() {
        return $this->container->get('addressService')->getAddressForId($this->session->getValue('sendto'));
    }

    /**
     * Set the current shipping address id.
     *
     * @param int addressId The new shipping address id.
     */
    public function setShippingAddressId($addressId) {
        // invalidate totals
        $this->zenTotals_ = null;

        $this->session->setValue('sendto', $addressId);
        $this->session->setValue('shipping', '');
    }

    /**
     * Get the selected billing address.
     *
     * @return ZMAddress The billing address.
     */
    public function getBillingAddress() {
        return $this->container->get('addressService')->getAddressForId($this->session->getValue('billto'));
    }

    /**
     * Set the selected billing address.
     *
     * @param int addressId The billing address id.
     */
    public function setBillingAddressId($addressId) {
        $billto = $this->session->getValue('billto');
        if (null !== $billto && $billto != $addressId) {
            $this->session->setValue('payment', '');
        }
        $this->session->setValue('billto', $addressId);
    }

    /**
     * Get zen-cart order totals.
     */
    protected function _getZenTotals() {
    global $order, $order_total_modules, $shipping_modules;

        if (null === $this->zenTotals_) {
            $order = new order();

            if (!isset($shipping_modules)) {
                $shipping_modules = new shipping($_SESSION['shipping']);
            }
            $this->zenTotals_ = $order_total_modules;
            if (!isset($order_total_modules)) {
                $this->zenTotals_ = new order_total();
                $this->zenTotals_->collect_posts();
                $this->zenTotals_->pre_confirmation_check();
            }
            $this->zenTotals_->process();
        }

        return $this->zenTotals_;
    }


    /**
     * Get the order totals.
     *
     * @return array List of <code>ZMOrderTotalLine</code> instances.
     */
    public function getTotals() {
        $totals = array();
        if (null != ($zenTotals = $this->_getZenTotals())) {
            foreach ($zenTotals->modules as $module) {
                $class = str_replace('.php', '', $module);
                $output = $GLOBALS[$class]->output;
                $type = substr($class, 3);
                foreach ($output as $zenTotal) {
                    $totals[] = new ZMOrderTotalLine($zenTotal['title'], $zenTotal['text'], $zenTotal['value'], $type);
                }
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
        return $this->container->get('paymentTypeService')->getPaymentFormValidationJS($request);
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
        $creditTypes = array();
        if (null != ($zenTotals = $this->_getZenTotals())) {
            $zenTypes = $zenTotals->credit_selection();
            foreach ($zenTypes as $zenType) {
                $creditType = new ZMCreditTypeWrapper($zenType['id'], $zenType['module'], $zenType['redeem_instructions']);
                if (isset($zenType['credit_class_error'])) {
                    $creditType->error_ = $zenType['credit_class_error'];
                }
                if (isset($zenType['fields'])) {
                    foreach ($zenType['fields'] as $zenField) {
                        //XXX fix HTML
                        $field = str_replace('textfield', 'text', $zenField['field']);
                        $creditType->addField(new ZMPaymentField($zenField['title'], $field));
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
                    $creditType->addField(new ZMPaymentField($title, $field));
                }
                array_push($creditTypes, $creditType);
            }
        }

        return $creditTypes;
    }


    /**
     * Check whether the cart is ready for checkout or not.
     *
     * @return boolean <code>true</code> if the cart is ready or checkout, <code>false</code> if not.
     */
    public function readyForCheckout() {
        return $this->checkoutHelper->readyForCheckout();
    }

    /**
     * Adjust the quantity based on the quantity settings.
     *
     * @param mixed quantity The quantity.
     * @return The adjusted quantity.
     */
    protected function adjustQty($quantity) {
        $digits = $this->container->get('settingsService')->get('qtyDecimals');
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
     * Get the cart quantity for the given product id/sku.
     *
     * @param string itemId The cart item/sku id.
     * @param boolean qtyMixed Indicates whether to return just the specified product variation quantity, or
     *  the quantity across all variations.
     * @return int The number of products in the cart.
     */
    public function getItemQuantityFor($itemId, $isQtyMixed) {
        if ($this->isEmpty()) {
            return 0;
        }

        // if mixed attributes is disabled, do not mix
        $baseItemId = self::getBaseProductIdFor($itemId);

        // method to get id from item
        $method = $isQtyMixed ? 'getProductId' : 'getId';
        $itemId = $isQtyMixed ? $baseItemId : $itemId;

        $quantity = 0;
        foreach ($this->getItems() as $item) {
            if ($item->$method() == $itemId) {
                $quantity += $item->getQuantity();
            }
        }

        return $quantity;
    }

    /**
     * Add a product in the given quantity.
     *
     * <p><strong>Doesn't support uploads (yet)</strong>.</p>
     *
     * <p>If <em>apps.store.isSanitizeAttributes</em> is set to <code>true</code>, missing
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
        $product = $this->container->get('productService')->getProductForId($productId);
        if (null == $product) {
            Runtime::getLogging()->error('failed to add product to cart; productId='.$productId);
            return false;
        }
        $attributes = $this->sanitizeAttributes($product, $attributes);
        $attributes = $this->prepare_uploads($product, $attributes);

        //TODO: zc: comp
        $attributes = (0 < count($attributes) ? $attributes : '');
        $sku = self::mkItemId($productId, $attributes);

        $maxOrderQty = $product->getMaxOrderQty();
        $cartQty = $this->getItemQuantityFor($sku, $product->isQtyMixed());
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

        // use adjusted qty on explicit sku qty
        $this->cart_->add_cart($productId, $this->getItemQuantityFor($sku, false) + $adjustedQty, $attributes, $notify);
        $this->items_ = null;

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
            $this->items_ = null;
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

            $productId = self::getBaseProductIdFor($sku);
            $product = $this->container->get('productService')->getProductForId($productId);

            $maxOrderQty = $product->getMaxOrderQty();
            $adjustedQty = $this->adjustQty($quantity);

            if (0 != $maxOrderQty && $adjustedQty >= $maxOrderQty) {
                // TODO: error message/status
                return false;
            }

            $this->items_ = null;

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
        switch (Runtime::getSettings()->get('productTaxBase')) {
            case ZMTaxRates::TAX_BASE_SHIPPING:
                return $this->isVirtual() ? $this->getBillingAddress() : $this->getShippingAddress();
            case ZMTaxRates::TAX_BASE_BILLING:
                return $this->getBillingAddress();
            case ZMTaxRates::TAX_BASE_STORE:
                if ($address->getZoneId() == Runtime::getSettings()->get('storeZone')) {
                    $address = $this->getBillingAddress();
                } else {
                    $address = $this->isVirtual() ? $this->getBillingAddress() : $this->getShippingAddress();
                }
                return $address;
        }

        Runtime::getLogging()->error('invalid productTaxBase!');
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
            if (0 === strpos($name, Runtime::getSettings()->get('uploadOptionPrefix'))) {
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
    protected function sanitizeAttributes($product, $attributes=array()) {
        //TODO: where should this actually be? attributes, rules, cart, products?
        if (!Runtime::getSettings()->get('apps.store.isSanitizeAttributes', false)) {
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
                $attributeId = Runtime::getSettings()->get('textOptionPrefix') . $attributeId;
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
     * Get the base product id from a given cart item id.
     *
     * @param string itemId The full shopping cart item id incl. attribute suffix.
     * @return int The product id.
     */
    public static function getBaseProductIdFor($productId) {
        $arr = explode(':', $productId);
        return (int) $arr[0];
    }


    /**
     * Create unique cart item/sku id, based on the base product id and attribute information.
     *
     * <p>This is the reverse of <code>getBaseProductIdFor</code>.</p>
     *
     * <p>Attributes are sorted using <code>krsort(..)</code> so to be compatible
     * for different attribute orders.</p>
     *
     * @param string productId The full product id incl. attribute suffix.
     * @param array attrbutes Additional product attributes.
     * @return string The product id.
     * @todo enable ksorts on attribute arrays to avoid different results for unsorted attributes
     */
    public static function mkItemId($productId, $attributes=array()) {
        $fullProductId = $productId;

        if (is_array($attributes) && 0 < count($attributes) && !strstr($productId, ':')) {
            //krsort($attributes);
            $s = $productId;
            foreach ($attributes as $id => $value) {
	              if (is_array($value)) {
                    //krsort($value);
                    foreach ($value as $vid => $vval) {
                        $s .= '{' . $id . '}' . trim($vid);
                    }
                } else {
                    $s .= '{' . $id . '}' . trim($value);
                }
            }
            $fullProductId .= ':' . md5($s);
        }

        if (Runtime::getContainer()->get('settingsService')->get('apps.store.assertZencart', false)) {
            $uprid = zen_get_uprid($productId, $attributes);
            if ($uprid != $fullProductId) {
                echo sprintf('mkItemId differs! uprid=%s, mkItemId=%s', $uprid, $fullProductId).'<br>';
            }
        }

        return $fullProductId;
    }

}
