<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\apps\storefront\controller;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;

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
     * @param ZMRequest request The current request.
     * @deprecated Use ZMAjaxCheckoutController instead
     */
    public function estimateShippingJSON($request) {
        $shoppingCart = $request->getShoppingCart();
        $shippingEstimator = Beans::getBean("ZMShippingEstimator");
        $shippingEstimator->prepare();
        $response = array();

        $address = $shippingEstimator->getAddress();
        if (null == $address) {
            $address = $shoppingCart->getShippingAddress();
        }
        if (null != $address) {
            $response['address'] = \ZMAjaxUtils::flattenObject($address, $this->get('ajaxAddressMap'));
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

        $flatObj = \ZMAjaxUtils::flattenObject($response);
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
        $cartDetails  = array();
        $items = array();
        $formatter = create_function('$obj,$name,$value', 'return $name=="itemTotal" ? zenmagick\base\Runtime::getContainer()->get(\'request\')->getToolbox()->utils->formatMoney($value) : $value;');
        foreach ($shoppingCart->getItems() as $item) {
            array_push($items, \ZMAjaxUtils::flattenObject($item, $this->get('ajaxCartItemMap'), $formatter));
        }
        $cartDetails ['items'] = $items;
        $cartDetails ['total'] = $request->getToolbox()->utils->formatMoney($shoppingCart->getTotal());

        $flatObj = \ZMAjaxUtils::flattenObject($cartDetails );
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
        $productId = $request->getProductId();
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
        $productId = $request->getProductId();

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
        $productId = $request->getProductId();
        $quantity = $request->getParameter('quantity', 0);

        if (null !== $productId && 0 != $quantity) {
            $shoppingCart = $request->getShoppingCart();
            $shoppingCart->updateProduct($productId, $quantity);
        }

        $this->getContentsJSON($request);
    }

}
