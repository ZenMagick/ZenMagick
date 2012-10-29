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

use ZenMagick\Base\ZMObject;
use ZenMagick\StoreBundle\Model\Checkout\ShoppingCart;
use ZenMagick\StoreBundle\Utils\CheckoutHelper;
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test shopping cart base class.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class ShoppingCartTestCaseBase extends TestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        // some vales zencart's order class wants...
        $_SESSION['sendto'] = '1';
        $_SESSION['billto'] = '1';
        $_SESSION['payment'] = 'moneyorder';
        $_SESSION['shipping'] = 'flat';
        $GLOBALS['moneyorder'] = new ZMObject();
        $GLOBALS['moneyorder']->title = 'moneyorder';
        $GLOBALS['moneyorder']->code = 'moneyorder';

        // cart checks for user...
        $account = $this->container->get('accountService')->getAccountForId(1);
        $this->getRequest()->getSession()->setAccount($account);

        // clear session and database
        $_SESSION['cart']->reset(true);
        $_SESSION['cart']->restore_contents();
        $this->container->get('settingsService')->set('apps.store.assertZencart', false);
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        parent::tearDown();
        // clear session and database
        $_SESSION['cart']->reset(true);
        $_SESSION['cart']->restore_contents();
    }


    /**
     * Get a shopping cart instance.
     */
    protected function getShoppingCart()
    {
        $shoppingCart = new ShoppingCart();
        $shoppingCart->setContainer($this->container);
        $shoppingCart->setCheckoutHelper(new CheckoutHelper());

        return $shoppingCart;
    }

    /**
     * Test a range of product ids.
     *
     * @param int from The from value.
     * @param int to The to value.
     * @param sting method The compare method.
     */
    protected function compareRange($from, $to, $method)
    {
        $range = array();
        for (; $from <= $to; ++$from) {
            $range[] = $from;
        }
        $this->$method($range);
    }

    /**
     * Populate the reference cart.
     *
     * @param array ids List of product ids to put into cart.
     * @return ShoppingCart The reference cart.
     */
    protected function getReferenceCart($ids)
    {
        // use to add products
        $referenceCart = $this->getShoppingCart();
        $textOptionPrefix = $this->container->get('settingsService')->get('textOptionPrefix');
        $productService = $this->container->get('productService');
        $qty = 5;
        for ($ii=0; $ii<2; ++$ii) {
            foreach ($ids as $id) {
                $attr = array();
                if (null != ($product = $productService->getProductForId($id, 1))) {
                    foreach ($product->getAttributes() as $attribute) {
                        switch ($attribute->getType()) {
                        case PRODUCTS_OPTIONS_TYPE_TEXT:
                            $attr[$textOptionPrefix.$attribute->getId()] = 5 == $qty ? 'abcde   foooo  ' : 'ab   cd ef gh';
                            break;
                        case PRODUCTS_OPTIONS_TYPE_RADIO:
                        //case PRODUCTS_OPTIONS_TYPE_CHECKBOX:
                        case PRODUCTS_OPTIONS_TYPE_SELECT:
                            $values = $attribute->getValues();
                            $ii = rand(0, count($values)-1);
                            $attr[$attribute->getId()] = $values[$ii]->getId();
                            break;
                        }
                    }
                }
                $referenceCart->addProduct($id, $qty, $attr);
                $qty = 5 == $qty ? 13: 5;
            }
        }

        return $referenceCart;
    }

}
