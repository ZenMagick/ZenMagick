<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;

/**
 * Test layout service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 */
class TestZMTaxRates extends ZMTestCase {

    /**
     * Test get tax rate for class id.
     */
    public function testGetRateForClassId() {
        $taxRateService = $this->container->get('taxRateService');
        $taxRate = $taxRateService->getTaxRateForClassId(1, 223, 18);
        $this->assertTrue($taxRate instanceof ZMTaxRate);
        if ($this->assertNotNull($taxRate)) {
            $this->assertEqual('1_223_18', $taxRate->getId());
            $this->assertEqual(1, $taxRate->getClassId());
            $this->assertEqual(223, $taxRate->getCountryId());
            $this->assertEqual(18, $taxRate->getZoneId());
            // check for 6 decimal digits
            $this->assertEqual(7.0, $taxRate->getRate());
        } else {
            $this->fail('no default tax rate not found');
        }

        // test non existing
        $taxRate = $taxRateService->getTaxRateForClassId(2);
        $this->assertTrue($taxRate instanceof ZMTaxRate);
        if ($this->assertNotNull($taxRate)) {
            $this->assertEqual(2, $taxRate->getClassId());
            $this->assertEqual(Runtime::getSettings()->get('storeCountry'), $taxRate->getCountryId());
            $this->assertEqual(Runtime::getSettings()->get('storeZone'), $taxRate->getZoneId());
            // check for 6 decimal digits
            $this->assertEqual(0, $taxRate->getRate());
        } else {
            $this->fail('no default tax rate not found');
        }
    }

    /**
     * Test get description.
     */
    public function testTaxDescription() {
        $taxRateService = $this->container->get('taxRateService');
        $this->assertEqual('FL TAX 7.0%', $taxRateService->getTaxDescription(1, 223, 18));
        $this->assertNull($taxRateService->getTaxDescription(1, 1, 8));
    }

    /**
     * Test rate for description.
     */
    public function testGetTaxForDescription() {
        $taxRateService = $this->container->get('taxRateService');
        $this->assertEqual(7.0, $taxRateService->getTaxRateForDescription('FL TAX 7.0%'));
        $this->assertEqual(0, $taxRateService->getTaxRateForDescription('foo bar'));
    }

    /**
     * Test get tax class
     */
    public function testGetTaxClassForId() {
        $taxClass = $this->container->get('taxRateService')->getTaxClassForId(1);
        $this->assertNotNull($taxClass);
        $this->assertEqual('Taxable Goods', $taxClass->getTitle());
    }

}
