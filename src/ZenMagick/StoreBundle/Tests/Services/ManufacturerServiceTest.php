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
namespace ZenMagick\StoreBundle\Tests\Services;

use DateTime;
use ZMRuntime;
use ZenMagick\Base\Beans;
use ZenMagick\ZenMagickBundle\Tests\ZenMagickTestCase;

/**
 * Test manufacturer service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ManufacturerServiceTest extends ZenMagickTestCase
{
    /**
     * Test get for name.
     */
    public function testGetForName()
    {
        $manufacturer = $this->get('manufacturerService')->getManufacturerForName('Matrox', 1);
        $this->assertNotNull($manufacturer);
        $this->assertEquals(1, $manufacturer->getId());
    }

    /**
     * Test update manufacturer.
     */
    public function testUpdateManufacturer()
    {
        $manufacturer = $this->get('manufacturerService')->getManufacturerForId(3, 1);
        $this->assertNotNull($manufacturer);
        $orgUrl = $manufacturer->getUrl();
        $manufacturer->setUrl('http://www.foo.com');
        $this->get('manufacturerService')->updateManufacturer($manufacturer);

        $updated = $this->get('manufacturerService')->getManufacturerForId(3, 1);
        $this->assertNotNull($manufacturer);
        $this->assertEquals('http://www.foo.com', $updated->getUrl());

        // revert change
        $manufacturer->setUrl($orgUrl);
        $this->get('manufacturerService')->updateManufacturer($manufacturer);
    }

    /**
     * Test update click count.
     */
    public function testUpdateClickCount()
    {
        $manufacturer = $this->get('manufacturerService')->getManufacturerForId(3, 1);
        $this->assertNotNull($manufacturer);

        $oldClickCount = $manufacturer->getClickCount();
        $this->get('manufacturerService')->updateManufacturerClickCount(3, 1);

        $manufacturer = $this->get('manufacturerService')->getManufacturerForId(3, 1);
        $this->assertNotNull($manufacturer);
        $this->assertEquals(($oldClickCount+1), $manufacturer->getClickCount());
    }

    /**
     * Test manufacturer without info record.
     */
    public function testNoInfo()
    {
        // create new manufacturer without info record
        $newManufacturer = Beans::getBean('ZenMagick\StoreBundle\Entity\Catalog\Manufacturer');
        $newManufacturer->setName('Foo');
        $newManufacturer->setDateAdded(new DateTime());
        $newManufacturer->setLastModified(new DateTime());
        $newManufacturer = ZMRuntime::getDatabase()->createModel('manufacturers', $newManufacturer);

        $manufacturer = $this->get('manufacturerService')->getManufacturerForId($newManufacturer->getId(), 1);
        if ($this->assertNotNull($manufacturer)) {
            $this->assertEquals($newManufacturer->getId(), $manufacturer->getId());
            $this->assertEquals('Foo', $manufacturer->getName());
        }

        // remove again
        ZMRuntime::getDatabase()->removeModel('manufacturers', $newManufacturer);
    }

}
