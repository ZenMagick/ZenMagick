<?php

/**
 * Test countries service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id: TestZMCountries.php 1600 2008-10-03 01:02:19Z dermanomann $
 */
class TestZMCountries extends ZMTestCase {

    /**
     * Test load country.
     */
    public function testLoadCountry() {
        $country = ZMCountries::instance()->getCountryForId(14);
        $this->assertNotNull($country);
        $this->assertEqual(14, $country->getId());
        $this->assertEqual('Austria', $country->getName());
    }

    /**
     * Test get zones.
     */
    public function testGetZones() {
        $zones = ZMCountries::instance()->getZonesForCountryId(14);
        $this->assertNotNull($zones);
        $this->assertEqual(9, count($zones));
    }

    /**
     * Test get zone.
     */
    public function testGetZoneCode() {
        $this->assertEqual('BL', ZMCountries::instance()->getZoneCode(14, 102));
    }

}

?>
