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
namespace ZenMagick\StoreBundle\Tests\Services;

use ZenMagick\Base\Runtime;
use ZenMagick\ZenMagickBundle\Tests\ZenMagickTestCase;

/**
 * Test tax service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TaxServiceTest extends ZenMagickTestCase
{
    /**
     * Test get tax rate for class id.
     */
    public function testGetRateForClassId()
    {
        $taxService = $this->get('taxService');
        $taxRate = $taxService->getTaxRateForClassId(1, 223, 18);
        $this->assertNotNull($taxRate);
        $this->assertTrue($taxRate instanceof \ZenMagick\StoreBundle\Entity\TaxRate);
        if ($taxRate) {
            $this->assertEquals('1_223_18', $taxRate->getId());
            $this->assertEquals(1, $taxRate->getClassId());
            $this->assertEquals(223, $taxRate->getCountryId());
            $this->assertEquals(18, $taxRate->getZoneId());
            // check for 7 decimal digits
            $this->assertEquals(7.0, $taxRate->getRate());
        }

        // test non existing
        $taxRate = $taxService->getTaxRateForClassId(2, 223, 18);
        $this->assertNotNull($taxRate);
        $this->assertTrue($taxRate instanceof \ZenMagick\StoreBundle\Entity\TaxRate);
        if ($taxRate) {
            $this->assertEquals(2, $taxRate->getClassId());
            $this->assertEquals(Runtime::getSettings()->get('storeCountry'), $taxRate->getCountryId());
            $this->assertEquals(Runtime::getSettings()->get('storeZone'), $taxRate->getZoneId());
            // check for 6 decimal digits
            $this->assertEquals(0, $taxRate->getRate());
        } else {
            $this->fail('no default tax rate not found');
        }
    }

    /**
     * Test get description.
     */
    public function testTaxDescription()
    {
        $taxService = $this->get('taxService');
        $this->assertEquals('FL TAX 7.0%', $taxService->getTaxDescription(1, 223, 18));
        $this->assertNull($taxService->getTaxDescription(1, 1, 8));
    }

    /**
     * Test rate for description.
     */
    public function testGetTaxForDescription()
    {
        $taxService = $this->get('taxService');
        $this->assertEquals(7.0, $taxService->getTaxRateForDescription('FL TAX 7.0%'));
        $this->assertEquals(0, $taxService->getTaxRateForDescription('foo bar'));
    }

    /**
     * Test get tax class
     */
    public function testGetTaxClassForId()
    {
        $taxClass = $this->get('taxService')->getTaxClassForId(1);
        $this->assertNotNull($taxClass);
        $this->assertEquals('Taxable Goods', $taxClass->getTitle());
    }

}
