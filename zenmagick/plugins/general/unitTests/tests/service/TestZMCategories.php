<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 * Test category service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
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
