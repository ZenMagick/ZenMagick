<?php
/*
 * ZenMagick - Smart e-commerce
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
 * Test manufacturres service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 */
class TestZMManufacturers extends ZMTestCase {

    /**
     * Test update manufacturer.
     */
    public function testUpdateManufacturer() {
        $manufacturer = ZMManufacturers::instance()->getManufacturerForId(3, 1);
        $this->assertNotNull($manufacturer);
        $orgUrl = $manufacturer->getUrl();
        $manufacturer->setUrl('http://www.foo.com');
        ZMManufacturers::instance()->updateManufacturer($manufacturer);

        $updated = ZMManufacturers::instance()->getManufacturerForId(3, 1);
        $this->assertNotNull($manufacturer);
        $this->assertEqual('http://www.foo.com', $updated->getUrl());

        // revert change
        $manufacturer->setUrl($orgUrl);
        ZMManufacturers::instance()->updateManufacturer($manufacturer);
    }

    /**
     * Test update click count.
     */
    public function testUpdateClickCount() {
        $manufacturer = ZMManufacturers::instance()->getManufacturerForId(3, 1);
        $this->assertNotNull($manufacturer);

        $oldClickCount = $manufacturer->getClickCount();
        ZMManufacturers::instance()->updateManufacturerClickCount(3, 1);

        $manufacturer = ZMManufacturers::instance()->getManufacturerForId(3, 1);
        $this->assertNotNull($manufacturer);
        $this->assertEqual(($oldClickCount+1), $manufacturer->getClickCount());
    }

    /**
     * Test manufacturer without info record.
     */
    public function testNoInfo() {
        // create new manufacturer without info record
        $newManufacturer = ZMLoader::make('Manufacturer');
        $newManufacturer->setName('Foo');
        $newManufacturer->setDateAdded(ZMDatabase::NULL_DATETIME);
        $newManufacturer->setLastModified(ZMDatabase::NULL_DATETIME);
        $newManufacturer = ZMRuntime::getDatabase()->createModel(TABLE_MANUFACTURERS, $newManufacturer);

        $manufacturer = ZMManufacturers::instance()->getManufacturerForId($newManufacturer->getId(), 1);
        if ($this->assertNotNull($manufacturer)) {
            $this->assertEqual($newManufacturer->getId(), $manufacturer->getId());
            $this->assertEqual('Foo', $manufacturer->getName());
        }

        // remove again
        ZMRuntime::getDatabase()->removeModel(TABLE_MANUFACTURERS, $newManufacturer);
    }

}
