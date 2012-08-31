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
namespace ZenMagick\apps\storefront\controller;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use ZenMagick\base\Runtime;
use ZenMagick\base\ZMObject;
use ZenMagick\base\events\Event;
use ZenMagick\http\Request;
use ZenMagick\http\view\ModelAndView;
use ZenMagick\apps\store\utils\CheckoutHelper;

/**
 * Request controller for shopping cart.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ShoppingCartController extends ZMObject {

    /**
     * Show cart.
     *
     * @param ZenMagick\http\Request request The current request.
     */
    public function show(Request $request) {
        $flashBag = $request->getSession()->getFlashBag();
        $shoppingCart = $request->getShoppingCart();
        $checkoutHelper = $shoppingCart->getCheckoutHelper();

        $checkoutHelper->checkStock();

        $statusMap = $checkoutHelper->checkCartStatus();
        foreach ($statusMap as $status => $items) {
            foreach ($items as $item) {
                $product = $item->getProduct();
                switch ($status) {
                case CheckoutHelper::CART_PRODUCT_STATUS:
                    $flashBag->warn(sprintf('%s: We are sorry but this product has been removed from our inventory at this time.', $product->getName()));
                    break;
                case CheckoutHelper::CART_PRODUCT_QUANTITY:
                    $flashBag->warn(sprintf('%s: has a minimum quantity restriction; minimum order quantity is: %s', $product->getName(), $product->getMinOrderQty()));
                    break;
                case CheckoutHelper::CART_PRODUCT_UNITS:
                    $flashBag->warn(sprintf('%s: has a quantity units restriction; minimum order units: %s', $product->getName(), $product->getQtyOrderUnits()));
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
     */
    protected function getAttributeUploads(Request $request, $shoppingCart, array $attributes) {
        $settingsService = $this->container->get('settingsService');
        $destination = $settingsService->get('apps.store.cart.uploads');
        $textOptionPrefix = $settingsService->get('textOptionPrefix');

        try {
            if ($request->files->has('id')) {
                foreach ($request->files->get('id') as $id => $file) {
                    $failed = null;
                    if ($file && 0 === strpos($id, $textOptionPrefix) && $file->isValid()) {
                        // todo: do we need/want to enfore any restrictions about size, etc?
                        if ($file->getSize()) {
                            // process
                            $filename = $file->getClientOriginalName();
                            $fileId = $this->container->get('shoppingCartService')->registerUpload(session_id(), $shoppingCart->getAccountId(), $filename);
                            // save indexed name for display!
                            $attributes[$id] = $fileId.'. '.$filename;
                            // move
                            $ext = substr($filename, strrpos($filename, '.')+1);
                            $name = sprintf('%s.%s', $fileId, $ext);
                            $file->move($destination, $name);
                        }
                    }
                }
            }
        } catch (FileException $e) {
            // todo
            die($e->getMessage());
        }

        return $attributes;
    }

    /**
     * Add product.
     */
    public function addProduct(Request $request) {
        $shoppingCart = $request->getShoppingCart();
        $productId = $request->request->get('products_id');
        $productId = is_array($productId) ? $productId[0] : $productId;
        $flashBag = $request->getSession()->getFlashBag();
        $id = $this->getAttributeUploads($request, $shoppingCart, (array) $request->request->get('id'));
        if ($shoppingCart->addProduct($productId, $request->request->get('cart_quantity'), $id)) {
            $shoppingCart->getCheckoutHelper()->saveHash($request);
            $this->container->get('event_dispatcher')->dispatch('cart_add', new Event($this, array('request' => $request, 'shoppingCart' => $shoppingCart, 'productId' => $productId)));
            $product = $this->container->get('productService')->getProductForId($productId);
            $flashBag->success(sprintf(_zm("Product '%s' added to cart"), $product->getName()));
        } else {
            $flashBag->error(_zm('Add to cart failed'));
        }

        // TODO: add support for redirect back to origin
        return new ModelAndView('success', array('shoppingCart' => $shoppingCart));
    }

    /**
     * Buy now product.
     */
    public function buyNow(Request $request) {
        $shoppingCart = $request->getShoppingCart();
        $productId = $request->query->get('products_id');
        $productId = is_array($productId) ? $productId[0] : $productId;
        $flashBag = $request->getSession()->getFlashBag();
        if (0 < $productId) {
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
                        $this->container->get('event_dispatcher')->dispatch('cart_add', new Event($this, array('request' => $request, 'shoppingCart' => $shoppingCart, 'productId' => $productId)));
                        $flashBag->success(sprintf(_zm("Product '%s' added to cart"), $product->getName()));
                    }
                } else {
                    $flashBag->error(_zm('Add to cart failed'));
                }
            }
        }

        // TODO: add support for redirect back to origin
        return new ModelAndView('success', array('shoppingCart' => $shoppingCart));
    }

    /**
     * Remove product.
     */
    public function removeProduct(Request $request) {
        $shoppingCart = $request->getShoppingCart();
        $flashBag = $request->getSession()->getFlashBag();
        $productId = $request->query->get('productId');
        $productId = is_array($productId) ? $productId[0] : $productId;
        $shoppingCart->removeProduct($productId);
        $shoppingCart->getCheckoutHelper()->saveHash($request);
        $this->container->get('event_dispatcher')->dispatch('cart_remove', new Event($this, array('request' => $request, 'shoppingCart' => $shoppingCart, 'productId' => $productId)));
        $flashBag->success(_zm('Product removed from cart'));

        // TODO: add support for redirect back to origin
        return new ModelAndView('success', array('shoppingCart' => $shoppingCart));
    }

    /**
     * Update cart.
     * @todo: edit cart attributes
     */
    public function update(Request $request) {
        $flashBag = $request->getSession()->getFlashBag();
        $shoppingCart = $request->getShoppingCart();
        $productIds = (array) $request->request->get('products_id');
        $quantities = (array) $request->request->get('cart_quantity');
        foreach ($productIds as $ii => $productId) {
            $shoppingCart->updateProduct($productId, $quantities[$ii]);
        }
        $this->container->get('event_dispatcher')->dispatch('cart_update', new Event($this, array('request' => $request, 'shoppingCart' => $shoppingCart, 'productIds' => $productIds)));
        $flashBag->success(_zm('Product(s) added to cart'));

        // TODO: add support for redirect back to origin
        return new ModelAndView('success', array('shoppingCart' => $shoppingCart));
    }

}
