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
 * Ajax controller for JSON shopping cart.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller.ajax
 * @version $Id: ZMAjaxShoppingCartController.php 2153 2009-04-14 03:28:18Z dermanomann $
 */
class ZMAjaxShoppingCartController extends ZMAjaxController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ajaxShoppingCart');
        $this->set('ajaxAddressMap', array('firstName', 'lastName', 'address', 'suburb', 'postcode', 'city', 'state', 'country'));
        $this->set('ajaxCartItemMap', array('id', 'name', 'qty', 'itemTotal'));
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Estimate shipping.
     *
     * @param ZMRequest request The current request.
     */
    public function estimateShippingJSON($request) {
        $shippingEstimator = ZMLoader::make("ShippingEstimator");
        $shippingEstimator->prepare();
        $response = array();

        $address = $shippingEstimator->getAddress();
        if (null != $address) {
            $response['address'] = $this->flattenObject($address, $this->get('ajaxAddressMap'));
        }

        $methods = array();
        if (!$shippingEstimator->isCartEmpty()) {
            $shipping = ZMLoader::make("Shipping");
            if (!$shipping->isFreeShipping()) {
                foreach ($shipping->getShippingProvider() as $provider) {
                    if ($provider->hasError()) 
                        continue;

                    foreach ($provider->getShippingMethods() as $method) {
                        $id = 'ship_'.$method->getId();
                        $ma = array();
                        $ma['id'] = $id;
                        $ma['name'] = $provider->getName() . " " . $method->getName();
                        $ma['cost'] = ZMToolbox::instance()->utils->formatMoney($method->getCost(), true, false);
                        array_push($methods, $ma);
                    }
                }
            }
        }
        $response['methods'] = $methods;

        $flatObj = $this->flattenObject($response);
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Get cart content.
     *
     * @param ZMRequest request The current request.
     */
    public function getContentsJSON($request) {
        $shoppingCart = $request->getShoppingCart();
        $cart = array();
        $items = array();
        $formatter = create_function('$obj,$name,$value', 'return $name=="itemTotal" ? ZMToolbox::instance()->utils->formatMoney($value, true, false) : $value;');
        foreach ($shoppingCart->getItems() as $item) {
            array_push($items, $this->flattenObject($item, $this->get('ajaxCartItemMap'), $formatter));
        }
        $cart['items'] = $items;
        $cart['total'] = ZMToolbox::instance()->utils->formatMoney($shoppingCart->getTotal(), true, false);

        $flatObj = $this->flattenObject($cart);
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Add product to cart.
     *
     * <p><strong>Doesn't support attributes yet</strong>.</p>
     *
     * <p>URL parameter:</p>
     * <dl>
     *  <dt>productId</dt><dd>The product id</dd>
     *  <dt>quantity</dt><dd>The product quantity</dd>
     *  <dt>id</dt><dd>Attribute details</dd>
     * </dl>
     *
     * <p>Will return the new cart contents.</p>
     *
     * @param ZMRequest request The current request.
     */
    public function addProductJSON($request) {
        $shoppingCart = $request->getShoppingCart();
        $productId = $request->getParameter('productId', null);
        $quantity = $request->getParameter('quantity', 0);
        $id = $request->getParameter('id', array());

        if (null !== $productId && 0 != $quantity) {
            $shoppingCart->addProduct($productId, $quantity, $id);
        }

        $this->getContentsJSON($request);
    }

    /**
     * Remove from cart.
     *
     * <p>Will return the new cart contents.</p>
     *
     * <p>URL parameter:</p>
     * <dl>
     *  <dt>productId</dt><dd>The product id</dd>
     * </dl>
     *
     * @param ZMRequest request The current request.
     */
    public function removeProductJSON($request) {
        $productId = $request->getParameter('productId', null);

        if (null !== $productId) {
            $shoppingCart = $request->getShoppingCart();
            $shoppingCart->removeProduct($productId);
        }

        $this->getContentsJSON($request);
    }

    /**
     * Update cart product.
     *
     * <p><strong>Doesn't support attributes yet</strong>.</p>
     *
     * <p>URL parameter:</p>
     * <dl>
     *  <dt>productId</dt><dd>The product id</dd>
     *  <dt>quantity</dt><dd>The product quantity</dd>
     * </dl>
     *
     * <p>Will return the new cart contents.</p>
     *
     * @param ZMRequest request The current request.
     */
    public function updateProductJSON($request) {
        $productId = $request->getParameter('productId', null);
        $quantity = $request->getParameter('quantity', 0);

        if (null !== $productId && 0 != $quantity) {
            $shoppingCart = $request->getShoppingCart();
            $shoppingCart->updateProduct($productId, $quantity);
        }

        $this->getContentsJSON($request);
    }

}

?>
