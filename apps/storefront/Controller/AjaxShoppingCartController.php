<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\apps\storefront\Controller;

use ZenMagick\Base\Beans;

/**
 * Ajax controller for JSON shopping cart.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AjaxShoppingCartController extends \ZMAjaxController {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('ajaxShoppingCart');
        $this->set('ajaxAddressMap', array('firstName', 'lastName', 'address', 'suburb', 'postcode', 'city', 'state', 'country'));
        $this->set('ajaxCartItemMap', array('id', 'name', 'qty', 'itemTotal'));
    }


    /**
     * Estimate shipping.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @deprecated Use ZMAjaxCheckoutController instead
     */
    public function estimateShippingJSON($request) {
        $shoppingCart = $this->get('shoppingCart');
        $shippingEstimator = Beans::getBean("ZMShippingEstimator");
        $shippingEstimator->prepare();
        $response = array();

        $utilsTool = $this->container->get('utilsTool');
        $address = $shippingEstimator->getAddress();
        if (null == $address) {
            $address = $shoppingCart->getShippingAddress();
        }
        if (null != $address) {
            $response['address'] = $utilsTool->flattenObject($address, $this->get('ajaxAddressMap'));
        }

        $methods = array();
        if (null != $address && !$shoppingCart->isEmpty()) {
            foreach ($this->container->get('shippingProviderService')->getShippingProviders(true) as $provider) {
                foreach ($provider->getShippingMethods($shoppingCart, $address) as $shippingMethod) {
                    $id = 'ship_'.$shippingMethod->getId();
                    $ma = array();
                    $ma['id'] = $id;
                    $ma['name'] = $provider->getName() . " " . $shippingMethod->getName();
                    $ma['cost'] = $request->getToolbox()->utils->formatMoney($shippingMethod->getCost());
                    $methods[] = $ma;
                }
            }
        }
        $response['methods'] = $methods;

        $flatObj = $utilsTool->flattenObject($response);
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Get cart content.
     *
     * @param ZenMagick\Http\Request request The current request.
     */
    public function getContentsJSON($request) {
        $shoppingCart = $this->get('shoppingCart');
        $cartDetails  = array();
        $items = array();
        $formatter = create_function('$obj,$name,$value', 'return $name=="itemTotal" ? $this->container->get(\'request\')->getToolbox()->utils->formatMoney($value) : $value;');
        $utilsTool = $this->container->get('utilsTool');
        foreach ($shoppingCart->getItems() as $item) {
            array_push($items, $utilsTool->flattenObject($item, $this->get('ajaxCartItemMap'), $formatter));
        }
        $cartDetails ['items'] = $items;
        $cartDetails ['total'] = $utilsTool->formatMoney($shoppingCart->getTotal());

        $flatObj = $utilsTool->flattenObject($cartDetails );
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
     * @param ZenMagick\Http\Request request The current request.
     */
    public function addProductJSON($request) {
        $shoppingCart = $this->get('shoppingCart');
        $productId = $request->get('productId');
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
     * @param ZenMagick\Http\Request request The current request.
     */
    public function removeProductJSON($request) {
        $productId = $request->query->get('productId');

        if (null !== $productId) {
            $shoppingCart = $this->get('shoppingCart');
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
     * @param ZenMagick\Http\Request request The current request.
     */
    public function updateProductJSON($request) {
        $productId = $request->query->get('productId');
        $quantity = $request->getParameter('quantity', 0);

        if (null !== $productId && 0 != $quantity) {
            $shoppingCart = $this->get('shoppingCart');
            $shoppingCart->updateProduct($productId, $quantity);
        }

        $this->getContentsJSON($request);
    }

}
