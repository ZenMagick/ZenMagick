<?php

/**
 * Test manufacturres service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMManufacturers extends ZMTestCase {

    /**
     * Test update manufacturer.
     */
    public function testUpdateManufacturer() {
        $manufacturer = ZMManufacturers::instance()->getManufacturerForId(3);
        $this->assertNotNull($manufacturer);
        $orgUrl = $manufacturer->getUrl();
        $manufacturer->setUrl('http://www.foo.com');
        ZMManufacturers::instance()->updateManufacturer($manufacturer);

        $updated = ZMManufacturers::instance()->getManufacturerForId(3);
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
        $manufacturer = ZMManufacturers::instance()->getManufacturerForId(3);
        $this->assertNotNull($manufacturer);

        $oldClickCount = $manufacturer->getClickCount();
        ZMManufacturers::instance()->updateManufacturerClickCount(3);

        $manufacturer = ZMManufacturers::instance()->getManufacturerForId(3);
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

        $manufacturer = ZMManufacturers::instance()->getManufacturerForId($newManufacturer->getId());
        if ($this->assertNotNull($manufacturer)) {
            $this->assertEqual($newManufacturer->getId(), $manufacturer->getId());
            $this->assertEqual('Foo', $manufacturer->getName());
        }

        // remove again
        ZMRuntime::getDatabase()->removeModel(TABLE_MANUFACTURERS, $newManufacturer);
    }

}

?>
