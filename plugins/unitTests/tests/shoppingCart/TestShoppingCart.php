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

/**
 * Test shopping cart.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestShoppingCart extends ShoppingCartTestCaseBase {

    /**
     * Test change quantity.
     */
    public function testChangeQty() {
        $shoppingCart = $this->getShoppingCart();
        $shoppingCart->addProduct(12, 3);

        $items = $shoppingCart->getItems();
        $this->assertEqual(1, count($items));

        $item = array_pop($items);
        $this->assertEqual(3, $item->getQuantity());

        // add again
        $shoppingCart->addProduct(12, 1);

        $items = $shoppingCart->getItems();
        $this->assertEqual(1, count($items));

        $item = array_pop($items);
        $this->assertEqual(4, $item->getQuantity());
    }

    /**
     * Test mkItemId.
     */
    public function testMkItemId() {
        $products = array(
            5 => array(),
            '5:abc' => array(),
            6 => array('foo' => 'bar'),
            '6:abc' => array('foo' => 'bar'),
            7 => array('foo' => 'bar', 'x' => 'y'),
            8 => array('z' => 3, 'foo' => 'bar', 'x' => 'y'),
            11 => array('foo' => 'bar', 'arr' => array('a', 'b', 'c')),
            12 => array('foo' => 'bar', 'arr' => array('c', 'b', 'a'))
        );

        $shoppingCart = $this->getShoppingCart();

        foreach ($products as $productId => $attributes) {
            $this->assertEqual(zen_get_uprid($productId, $attributes), $shoppingCart::mkItemId($productId, $attributes), sprintf('Failed for productId: %s', $productId));
        }
    }

}
