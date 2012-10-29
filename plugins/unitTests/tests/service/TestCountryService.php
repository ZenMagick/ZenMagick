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
 * Test country service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestCountryService extends TestCase
{
    /**
     * Test load country.
     */
    public function testLoadCountry()
    {
        $country = $this->container->get('countryService')->getCountryForId(14);
        $this->assertNotNull($country);
        $this->assertEqual(14, $country->getId());
        $this->assertEqual('Austria', $country->getName());
    }

    /**
     * Test get zones.
     */
    public function testGetZones()
    {
        $zones = $this->container->get('countryService')->getZonesForCountryId(14);
        $this->assertNotNull($zones);
        $this->assertEqual(9, count($zones));
    }

    /**
     * Test get zone.
     */
    public function testGetZoneCode()
    {
        $this->assertEqual('BL', $this->container->get('countryService')->getZoneCode(14, 102));
    }

}
