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

use zenmagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test category.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMCategory extends TestCase {

    /**
     * Test childIds
     */
    public function testChildIds() {
        $expect = array(3, 10, 13, 12, 15, 11, 14);
        $category = $this->container->get('categoryService')->getCategoryForId(3, 1);
        $ids = $category->getDecendantIds();
        $this->assertEqual(count($expect), count($ids));
        foreach ($expect as $id) {
            $this->assertTrue(in_array($id, $ids));
        }
    }

    /**
     * Test childIds excluding category.
     */
    public function testChildIdsExclude() {
        $expect = array(10, 13, 12, 15, 11, 14);
        $category = $this->container->get('categoryService')->getCategoryForId(3, 1);
        $ids = $category->getDecendantIds(false);
        $this->assertEqual(count($expect), count($ids));
        foreach ($expect as $id) {
            $this->assertTrue(in_array($id, $ids));
        }
    }

    /**
     * Test getProductTypeIds.
     */
    public function testGetProductTypeIds() {
        $tests = array(
            array('categoryId' => 63, 'expected' => array(3, 4)),
            array('categoryId' => 62, 'expected' => array(2)),
            array('categoryId' => 1, 'expected' => array())
        );

        foreach ($tests as $test) {
            $category = $this->container->get('categoryService')->getCategoryForId($test['categoryId'], 1);
            if ($this->assertNotNull($category, '%s; categoryId '.$test['categoryId'])) {
                $this->assertEqual($test['expected'], $category->getProductTypeIds());
            }
        }
    }

}
