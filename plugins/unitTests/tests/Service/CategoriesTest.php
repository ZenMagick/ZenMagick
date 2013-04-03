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

use ZenMagick\Base\Beans;
use ZenMagick\ZenMagickBundle\Test\BaseTestCase;

/**
 * Test category service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CategoriesTest extends BaseTestCase
{
    /**
     * Test product type id loading.
     */
    public function testGetProductTypeIds()
    {
        $tests = array(
            array('categoryId' => 63, 'expected' => array(3, 4)),
            array('categoryId' => 62, 'expected' => array(2)),
            array('categoryId' => 1, 'expected' => array())
        );

        foreach ($tests as $test) {
            $ids = $this->get('categoryService')->getProductTypeIds($test['categoryId']);
            if ($this->assertTrue(is_array($ids), '%s; categoryId '.$test['categoryId'])) {
                $this->assertEquals($test['expected'], $ids);
            }
        }
    }

    /**
     * Test create/delete.
     */
    public function testCreateDelete()
    {
        $categoryService = $this->get('categoryService');

        $newCategory = Beans::getBean('ZenMagick\StoreBundle\Entity\Category');
        $newCategory->setLanguageId(1);
        $newCategory->setName('Foo');
        $newCategory->setDescription('A foo category');
        $newCategory->addChild(2);
        $newCategory = $categoryService->createCategory($newCategory);
        $this->assertTrue(0 != $newCategory->getId());
        $reloadedCategory = $categoryService->getCategoryForId($newCategory->getId(), 1);
        $this->assertNotNull($reloadedCategory);
        $this->assertEquals($newCategory, $reloadedCategory);
        // delete again
        $categoryService->deleteCategory($newCategory);
        $invalidCategory = $categoryService->getCategoryForId($newCategory->getId(), 1);
        $this->assertNull($invalidCategory);
    }

    /**
     * Test update
     */
    public function testUpdate()
    {
        $categoryService = $this->get('categoryService');

        $category = $categoryService->getCategoryForId(35, 1);
        $category->addChild(2);
        $categoryService->updateCategory($category);
        $reloadedCategory = $categoryService->getCategoryForId($category->getId(), 1);
        $this->assertNotNull($reloadedCategory);
        $this->assertEquals($category, $reloadedCategory);
        $category->removeChild(2);
        $categoryService->updateCategory($category);
        $reloadedCategory = $categoryService->getCategoryForId($category->getId(), 1);
        $this->assertNotNull($reloadedCategory);
        $this->assertEquals($category, $reloadedCategory);
    }

}
