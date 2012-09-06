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

use ZenMagick\Base\Runtime;
use ZenMagick\plugins\unitTests\simpletest\TestCase;
use ZenMagick\apps\store\Entity\TaxRate;

/**
 * Test layout service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestTaxService extends TestCase {

    /**
     * Test get tax rate for class id.
     */
    public function testGetRateForClassId() {
        $taxService = $this->container->get('taxService');
        $taxRate = $taxService->getTaxRateForClassId(1, 223, 18);
        $this->assertTrue($taxRate instanceof TaxRate);
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
        $taxRate = $taxService->getTaxRateForClassId(2);
        $this->assertTrue($taxRate instanceof TaxRate);
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
        $taxService = $this->container->get('taxService');
        $this->assertEqual('FL TAX 7.0%', $taxService->getTaxDescription(1, 223, 18));
        $this->assertNull($taxService->getTaxDescription(1, 1, 8));
    }

    /**
     * Test rate for description.
     */
    public function testGetTaxForDescription() {
        $taxService = $this->container->get('taxService');
        $this->assertEqual(7.0, $taxService->getTaxRateForDescription('FL TAX 7.0%'));
        $this->assertEqual(0, $taxService->getTaxRateForDescription('foo bar'));
    }

    /**
     * Test get tax class
     */
    public function testGetTaxClassForId() {
        $taxClass = $this->container->get('taxService')->getTaxClassForId(1);
        $this->assertNotNull($taxClass);
        $this->assertEqual('Taxable Goods', $taxClass->getTitle());
    }

}
