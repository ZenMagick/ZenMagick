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

use ZenMagick\ZenMagickBundle\Test\BaseTestCase;

/**
 * Test image info.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMImageInfoTest extends BaseTestCase
{
    /**
     * Test split image name.
     */
    public function testSplitImagename()
    {
        $info = ZMImageInfo::splitImageName('/foo/bar/image.png');
        if ($this->assertTrue(is_array($info))) {
            if ($this->assertEquals(3, count($info))) {
                $this->assertEquals('/foo/bar/', $info[0]);
                $this->assertEquals('.png', $info[1]);
                $this->assertEquals('/foo/bar/image', $info[2]);
            }
        }
    }

}
