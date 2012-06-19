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
namespace zenmagick\apps\store\storefront\controller;

use ZMRequest;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\base\events\Event;
use zenmagick\http\view\ModelAndView;
use zenmagick\apps\store\utils\CheckoutHelper;

/**
 * Request controller for shopping cart.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ShoppingCartController extends ZMObject {

    /**
     * Show cart.
     *
     * @param ZMRequest request The current request.
     */
    public function show(ZMRequest $request) {
        $shoppingCart = $request->getShoppingCart();
        $checkoutHelper = $shoppingCart->getCheckoutHelper();

        $checkoutHelper->checkStock();

        $statusMap = $checkoutHelper->checkCartStatus();
        foreach ($statusMap as $status => $items) {
            foreach ($items as $item) {
                $product = $item->getProduct();
                switch ($status) {
                case CheckoutHelper::CART_PRODUCT_STATUS:
                    $this->messageService->warn(sprintf('%s: We are sorry but this product has been removed from our inventory at this time.', $product->getName()));
                    break;
                case CheckoutHelper::CART_PRODUCT_QUANTITY:
                    $this->messageService->warn(sprintf('%s: has a minimum quantity restriction; minimum order quantity is: %s', $product->getName(), $product->getMinOrderQty()));
                    break;
                case CheckoutHelper::CART_PRODUCT_UNITS:
                    $this->messageService->warn(sprintf('%s: has a quantity units restriction; minimum order units: %s', $product->getName(), $product->getQtyOrderUnits()));
                    break;
                }
            }
        }

        return new ModelAndView(null, array('shoppingCart' => $shoppingCart));
    }

    /**
     * Process optional uploads.
     *
     * @return array Attribute id map for uploads.
     * @todo do not use _FILES directly
     */
    protected function getAttributeUploads($shoppingCart, array $attributes) {
        $settingsService = $this->container->get('settingsService');
        $destination = $settingsService->get('apps.store.cart.uploads');
        $textOptionPrefix = $settingsService->get('textOptionPrefix');

        if (array_key_exists('id', $_FILES)) {
            foreach ($_FILES['id']['name'] as $id => $file) {
                $failed = null;
                if (0 === strpos($id, $textOptionPrefix) && !empty($file)) {
                    $size = $_FILES['id']['size'][$id];
                    $tmp = $_FILES['id']['tmp_name'][$id];
                    // todo: do we need/want to enfore any restrictions about size, etc?
                    if (0 != $size && is_uploaded_file($tmp)) {
                        // process
                        $ext = substr($file, strrpos($file, '.'));
                        $fileId = $this->container->get('shoppingCartService')->registerUpload(session_id(), $shoppingCart->getAccountId(), $file);
                        $attributes[$id] = $fileId.'. '.$file;
                        if (!move_uploaded_file($tmp, $destination.'/'.$fileId.$ext)) {
                            $failed = $file;
                        }
                    } else {
                        $failed = $file;
                    }
                }
                if ($failed) {
                    // todo: message
                }
            }
        }

        return $attributes;
    }

    /**
     * Add product.
     */
    public function addProduct(ZMRequest $request) {
        $shoppingCart = $request->getShoppingCart();
        $id = $this->getAttributeUploads($shoppingCart, $request->getParameter('id', array()));
        if ($shoppingCart->addProduct($request->getProductId(), $request->getParameter('cart_quantity'), $id)) {
            $productId = $request->getProductId();
            $shoppingCart->getCheckoutHelper()->saveHash($request);
            $this->container->get('eventDispatcher')->dispatch('cart_add', new Event($this, array('request' => $request, 'shoppingCart' => $shoppingCart, 'productId' => $productId)));
            $product = $this->container->get('productService')->getProductForId($productId);
            $this->container->get('messageService')->success(sprintf(_zm("Product '%s' added to cart"), $product->getName()));
        } else {
            $this->container->get('messageService')->error(_zm('Add to cart failed'));
        }

        // TODO: add support for redirect back to origin
        return new ModelAndView('success', array('shoppingCart' => $shoppingCart));
    }

    /**
     * Buy now product.
     */
    public function buyNow(ZMRequest $request) {
        $shoppingCart = $request->getShoppingCart();
        if (0 < ($productId = $request->getProductId())) {
            $productService = $this->container->get('productService');
            if (null != ($product = $productService->getProductForId(ShoppingCart::getBaseProductIdFor($productId)))) {
                if (!$product->hasAttributes()) {
                    $qtyOrderMax = $product->getQtyOrderMax();
                    $cartQty = $shoppingCart->getItemQuantityFor($productId, $product->isQtyMixed());
                    if ($qtyOrderMax > $cartQty) {
                        // FTW!
                        $buyNowQty = 1;
                        $qtyOrderMin = $product->getQtyOrderMin();
                        $qtyOrderUnits = $product->getQtyOrderUnits();
                        if (0 == $cartQty) {
                            $buyNowQty = max($qtyOrderMin, $qtyOrderUnits);
                        } else if ($cartQty < $qtyOrderMin) {
                            $buyNowQty = $qtyOrderMin - $cartQty;
                        } else if ($cartQty > $qtyOrderMin) {
                            $adjQtyOrderUnits = $qtyOrderUnits - ZMTools::fmod_round($cartQty, $qtyOrderUnits);
                            $buyNowQty = 0 < $adjQtyOrderUnits ? $adjQtyOrderUnits : $qtyOrderUnits;
                        } else {
                            $buyNowQty = $qtyOrderUnits;
                        }

                        $buyNowQty = 1 > $buyNowQty ? 1 : $buyNowQty;
                        // limit
                        $buyNowQty = min($qtyOrderMax, $cartQty + $buyNowQty);
                        $shoppingCart->addProduct($productId, $buyNowQty);
                        $shoppingCart->getCheckoutHelper()->saveHash($request);
                        $this->container->get('eventDispatcher')->dispatch('cart_add', new Event($this, array('request' => $request, 'shoppingCart' => $shoppingCart, 'productId' => $productId)));
                        $this->container->get('messageService')->success(sprintf(_zm("Product '%s' added to cart"), $product->getName()));
                    }
                } else {
                    $this->container->get('messageService')->error(_zm('Add to cart failed'));
                }
            }
        }

        // TODO: add support for redirect back to origin
        return new ModelAndView('success', array('shoppingCart' => $shoppingCart));
    }

    /**
     * Remove product.
     */
    public function removeProduct(ZMRequest $request) {
        $shoppingCart = $request->getShoppingCart();
        $productId = $request->getParameter('product_id');
        $shoppingCart->removeProduct($productId);
        $shoppingCart->getCheckoutHelper()->saveHash($request);
        $this->container->get('eventDispatcher')->dispatch('cart_remove', new Event($this, array('request' => $request, 'shoppingCart' => $shoppingCart, 'productId' => $productId)));
        $this->container->get('messageService')->success(_zm('Product removed from cart'));

        // TODO: add support for redirect back to origin
        return new ModelAndView('success', array('shoppingCart' => $shoppingCart));
    }

    /**
     * Update cart.
     * @todo: edit cart attributes
     */
    public function update(ZMRequest $request) {
        $shoppingCart = $request->getShoppingCart();
        $productIds = (array) $request->getParameter('products_id');
        $quantities = (array) $request->getParameter('cart_quantity');
        foreach ($productIds as $ii => $productId) {
            $shoppingCart->updateProduct($productId, $quantities[$ii]);
        }
        $this->container->get('eventDispatcher')->dispatch('cart_update', new Event($this, array('request' => $request, 'shoppingCart' => $shoppingCart, 'productIds' => $productIds)));
        $this->container->get('messageService')->success(_zm('Product(s) added to cart'));

        // TODO: add support for redirect back to origin
        return new ModelAndView('success', array('shoppingCart' => $shoppingCart));
    }

}
