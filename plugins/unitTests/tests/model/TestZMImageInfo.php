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

use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test image info.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMImageInfo extends TestCase
{
    /**
     * Test split image name.
     */
    public function testSplitImagename()
    {
        $info = ZMImageInfo::splitImageName('/foo/bar/image.png');
        if ($this->assertTrue(is_array($info))) {
            if ($this->assertEqual(3, count($info))) {
                $this->assertEqual('/foo/bar/', $info[0]);
                $this->assertEqual('.png', $info[1]);
                $this->assertEqual('/foo/bar/image', $info[2]);
            }
        }
    }

}
