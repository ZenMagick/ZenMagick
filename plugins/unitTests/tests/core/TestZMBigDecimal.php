<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * Test ZMBigDecimal.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMBigDecimal extends ZMTestCase {

    /**
     * Test add.
     */
    public function testAdd() {
        $d1 = new ZMBigDecimal(123.33);
        $this->assertEqual(133.33, $d1->add(10)->asFloat());
        $this->assertEqual(246.66, $d1->add($d1)->asFloat());
    }

    /**
     * Test subtract.
     */
    public function testSubtract() {
        $d1 = new ZMBigDecimal(98.66);
        $this->assertEqual(88.66, $d1->subtract(10)->asFloat());
        $this->assertEqual(0, $d1->subtract($d1)->asFloat(6));
    }

    /**
     * Test multiply.
     */
    public function testMultiply() {
        $d1 = new ZMBigDecimal(3.33);
        $result = $d1->multiply(3);
        $this->assertEqual(9.99, $result->asFloat(12));
        $this->assertEqual(array(2997, 300), $result->getNDPair());
    }

    /**
     * Test divide.
     */
    public function testDivide() {
        $d1 = new ZMBigDecimal(3.33);
        $result = $d1->divide(3);
        $this->assertEqual(1.11, $result->asFloat(12));
        $this->assertEqual(array(999, 900), $result->getNDPair());
    }

}
