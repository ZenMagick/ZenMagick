<?php

/**
 * Test category.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id: TestZMCategory.php 1889 2009-01-22 01:40:14Z dermanomann $
 */
class TestZMCategory extends ZMTestCase {

    /**
     * Test childIds
     */
    public function testChildIds() {
        $expect = array(3, 10, 13, 12, 15, 11, 14);
        $category = ZMCategories::instance()->getCategoryForId(3);
        $ids = $category->getChildIds();
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
        $category = ZMCategories::instance()->getCategoryForId(3);
        $ids = $category->getChildIds(false);
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
            $category = ZMCategories::instance()->getCategoryForId($test['categoryId']);
            if ($this->assertNotNull($category, '%s; categoryId '.$test['categoryId'])) {
                $this->assertEqual($test['expected'], $category->getProductTypeIds());
            }
        }
    }

}

?>
