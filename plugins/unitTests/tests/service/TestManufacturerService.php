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
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test manufacturer service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestManufacturerService extends TestCase {

    /**
     * Test get for name.
     */
    public function testGetForName() {
        $manufacturer = $this->container->get('manufacturerService')->getManufacturerForName('Matrox', 1);
        $this->assertNotNull($manufacturer);
        $this->assertEqual(1, $manufacturer->getId());
    }

    /**
     * Test update manufacturer.
     */
    public function testUpdateManufacturer() {
        $manufacturer = $this->container->get('manufacturerService')->getManufacturerForId(3, 1);
        $this->assertNotNull($manufacturer);
        $orgUrl = $manufacturer->getUrl();
        $manufacturer->setUrl('http://www.foo.com');
        $this->container->get('manufacturerService')->updateManufacturer($manufacturer);

        $updated = $this->container->get('manufacturerService')->getManufacturerForId(3, 1);
        $this->assertNotNull($manufacturer);
        $this->assertEqual('http://www.foo.com', $updated->getUrl());

        // revert change
        $manufacturer->setUrl($orgUrl);
        $this->container->get('manufacturerService')->updateManufacturer($manufacturer);
    }

    /**
     * Test update click count.
     */
    public function testUpdateClickCount() {
        $manufacturer = $this->container->get('manufacturerService')->getManufacturerForId(3, 1);
        $this->assertNotNull($manufacturer);

        $oldClickCount = $manufacturer->getClickCount();
        $this->container->get('manufacturerService')->updateManufacturerClickCount(3, 1);

        $manufacturer = $this->container->get('manufacturerService')->getManufacturerForId(3, 1);
        $this->assertNotNull($manufacturer);
        $this->assertEqual(($oldClickCount+1), $manufacturer->getClickCount());
    }

    /**
     * Test manufacturer without info record.
     */
    public function testNoInfo() {
        // create new manufacturer without info record
        $newManufacturer = Beans::getBean('ZenMagick\apps\store\model\catalog\Manufacturer');
        $newManufacturer->setName('Foo');
        $newManufacturer->setDateAdded(new \DateTime());
        $newManufacturer->setLastModified(new \DateTime());
        $newManufacturer = ZMRuntime::getDatabase()->createModel('manufacturers', $newManufacturer);

        $manufacturer = $this->container->get('manufacturerService')->getManufacturerForId($newManufacturer->getId(), 1);
        if ($this->assertNotNull($manufacturer)) {
            $this->assertEqual($newManufacturer->getId(), $manufacturer->getId());
            $this->assertEqual('Foo', $manufacturer->getName());
        }

        // remove again
        ZMRuntime::getDatabase()->removeModel('manufacturers', $newManufacturer);
    }

}
