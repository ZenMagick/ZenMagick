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
 * Test countries service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
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
