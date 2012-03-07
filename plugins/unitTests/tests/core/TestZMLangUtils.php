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
?>
<?php

/**
 * Test ZMLangUtils.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMLangUtils extends ZMTestCase {

    /**
     * Test inArray.
     */
    public function testInArray() {
        $tests = array(
            array('value' => 3, 'array' => array(1, 2, 3), 'expected' => true),
            array('value' => 1, 'array' => '1, 2, 3', 'expected' => true),
            array('value' => 7, 'array' => array(1, 2, 3), 'expected' => false),
            array('value' => 8, 'array' => '1, 2, 3', 'expected' => false)
        );
        foreach ($tests as $test) {
            $this->assertEqual($test['expected'], ZMLangUtils::inArray($test['value'], $test['array']), '%s; '.$test['value']);
        }
    }

}
