<?php

/**
 * Test manufacturres service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMManufacturers extends UnitTestCase {

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

}

?>
