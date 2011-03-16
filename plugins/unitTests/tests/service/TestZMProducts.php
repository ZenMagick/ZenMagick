<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Test product service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 */
class TestZMProducts extends ZMTestCase {

    /**
     * Test create product.
     */
    public function testCreateProduct() {
        //TODO
    }

    /**
     * Test update product.
     */
    public function testUpdateProduct() {
        $product = ZMProducts::instance()->getProductForId(2, 1);
        $this->assertNotNull($product);
        $product->setName($product->getName().'@@@');
        ZMProducts::instance()->updateProduct($product);
        $reloaded = ZMProducts::instance()->getProductForId($product->getId(), 1);
        foreach (array_keys(ZMDbTableMapper::instance()->getMapping(array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION), ZMRuntime::getDatabase())) as $key) {
            $prefixList = array('get', 'is', 'has');
            $done = false;
            foreach ($prefixList as $prefix) {
                $getter = $prefix . ucwords($key);
                if (method_exists($product, $getter)) {
                    $this->assertEqual($product->$getter(), $reloaded->$getter(), '%s getter='.$getter);
                    $done = true;
                    break;
                }
            }
            if (!$done) {
                $this->assertEqual($product->get($key), $reloaded->get($key), '%s key='.$key);
            }
        }
        // revert name change
        $product->setName(str_replace('@@@', '', $product->getName()));
        ZMProducts::instance()->updateProduct($product);
    }

    /**
     * Test product type settings.
     */
    public function testProductTypeSettings() {
        $fieldData = array(
            'starting_at' => true,
            'reviews' => true,
            'tell_a_friend' => true
        );
        $product = ZMProducts::instance()->getProductForId(2, 1);
        foreach ($fieldData as $field => $value) {
            $this->assertEqual($value, $product->getTypeSetting($field), '%s field='.$field);
        }
    }

    /**
     * Test featured products on homepage.
     */
    public function testFeaturedProductsHome() {
        $featuredIds = array(34, 40, 12, 27, 26, 168, 169, 171, 172);
        $products = ZMProducts::instance()->getFeaturedProducts();
        $this->assertEqual(9, count($products));
        foreach ($products as $product) {
            $this->assertTrue(in_array($product->getId(), $featuredIds));
        }
    }

    /**
     * Test featured products on category page.
     */
    public function testFeaturedProductsCategory() {
        $featuredIds = array(12);
        $products = ZMProducts::instance()->getFeaturedProducts(3, 4, true);
        $this->assertEqual(1, count($products));
        foreach ($products as $product) {
            $this->assertTrue(in_array($product->getId(), $featuredIds));
        }
    }

    /**
     * Test new products on home page.
     */
    public function testNewProductsHome() {
        $sql = "UPDATE " . TABLE_PRODUCTS . " SET products_date_added = :dateAdded";
        ZMRuntime::getDatabase()->update($sql, array('dateAdded' => date(ZMDatabase::DATETIME_FORMAT)), TABLE_PRODUCTS);

        $products = ZMProducts::instance()->getNewProducts();
        $this->assertEqual(50, count($products));

        ZMRuntime::getDatabase()->update($sql, array('dateAdded' => '2003-11-03 12:32:17'), TABLE_PRODUCTS);
    }

    /**
     * Test new products on category page.
     */
    public function testNewProductsCategory() {
        $featuredIds = array(1, 2);
        $products = ZMProducts::instance()->getNewProducts(4, 0, '0');
        $this->assertEqual(2, count($products));
        foreach ($products as $product) {
            $this->assertTrue(in_array($product->getId(), $featuredIds));
        }
    }

    /**
     * Test bestseller products on home page.
     */
    public function testBestsellerProductsHome() {
        $products = ZMProducts::instance()->getBestSellers(null, 999);
        $this->assertEqual(51, count($products));
    }

    /**
     * Test bestseller products on category page.
     */
    public function testBestsellerProductsCategory() {
        $featuredIds = array(1, 2);
        $products = ZMProducts::instance()->getBestSellers(4, 999);
        $this->assertEqual(2, count($products));
        foreach ($products as $product) {
            $this->assertTrue(in_array($product->getId(), $featuredIds));
        }
    }

    /**
     * Test getProductForModel.
     */
    public function testGetProductForModel() {
        $product = ZMProducts::instance()->getProductForModel('MG200MMS', 1);
        if ($this->assertNotNull($product)) {
            $this->assertTrue($product instanceof ZMProduct);
            $this->assertEqual(1, $product->getId());
        }
    }

    /**
     * Test getProductIdsForCategoryId.
     */
    public function testGetProductIdsForCategoryId() {
        $productIdList = ZMProducts::instance()->getProductIdsForCategoryId(10, 1, true, false);
        $this->assertNotNull($productIdList);
        $expect = array(12, 11, 13, 18, 17, 6, 4, 10, 9);
        $this->assertEqual($expect, $productIdList);
    }

    /**
     * Test getProductsForCategoryId.
     */
    public function testGetProductsForCategoryId() {
        $productList = ZMProducts::instance()->getProductsForCategoryId(10, true, 1);
        $this->assertNotNull($productList);
        $this->assertEqual(9, count($productList));
    }

}
