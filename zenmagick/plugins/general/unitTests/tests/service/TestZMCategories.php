<?php

/**
 * Test category service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id: TestZMCategories.php 2610 2009-11-20 02:45:25Z dermanomann $
 */
class TestZMCategories extends ZMTestCase {

    /**
     * Test product type id loading.
     */
    public function testGetProductTypeIds() {
        $tests = array(
            array('categoryId' => 63, 'expected' => array(3, 4)),
            array('categoryId' => 62, 'expected' => array(2)),
            array('categoryId' => 1, 'expected' => array())
        );

        foreach ($tests as $test) {
            $ids = ZMCategories::instance()->getProductTypeIds($test['categoryId']);
            if ($this->assertTrue(is_array($ids), '%s; categoryId '.$test['categoryId'])) {
                $this->assertEqual($test['expected'], $ids);
            }
        }
    }

}

?>
