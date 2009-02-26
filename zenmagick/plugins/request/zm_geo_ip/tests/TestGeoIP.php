<?php

/**
 * Test Geo IP plugin.
 *
 * @package org.zenmagick.plugins.zm_geo_ip.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestGeoIP extends ZMTestCase {

    /**
     * Get plugin.
     */
    public function getPlugin() {
        return ZMPlugins::getPluginForId('zm_geo_ip');
    }


    /**
     * Test simple GeoIP.
     */
    public function testGeoIPSimple() {
        // test valid
        $result = $this->getPlugin()->lookup("24.24.24.24");
        $this->assertNotNull($result);
        $this->assertEqual('US', $result->getCountryCode());
        $this->assertEqual('United States', $result->getCountry());

        // test invalid
        $result = $this->getPlugin()->lookup("324.24.24.24");
        $this->assertNotNull($result);
        $this->assertEqual('', $result->getCountryCode());
        $this->assertEqual('', $result->getCountry());
    }

}

?>
