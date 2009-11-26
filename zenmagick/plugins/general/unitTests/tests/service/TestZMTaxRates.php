<?php

/**
 * Test layout service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id: TestZMTaxRates.php 2610 2009-11-20 02:45:25Z dermanomann $
 */
class TestZMTaxRates extends ZMTestCase {

    /**
     * Test get tax rate for class id.
     */
    public function testGetRateForClassId() {
        $taxRate = ZMTaxRates::instance()->getTaxRateForClassId(1, 223, 18);
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
        $taxRate = ZMTaxRates::instance()->getTaxRateForClassId(2);
        $this->assertTrue($taxRate instanceof ZMTaxRate);
        if ($this->assertNotNull($taxRate)) {
            $this->assertEqual(2, $taxRate->getClassId());
            $this->assertEqual(ZMSettings::get('storeCountry'), $taxRate->getCountryId());
            $this->assertEqual(ZMSettings::get('storeZone'), $taxRate->getZoneId());
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
        $this->assertEqual('FL TAX 7.0%', ZMTaxRates::instance()->getTaxDescription(1, 223, 18));
        $this->assertNull(ZMTaxRates::instance()->getTaxDescription(1, 1, 8));
    }

    /**
     * Test rate for description.
     */
    public function testGetTaxForDescription() {
        $this->assertEqual(7.0, ZMTaxRates::instance()->getTaxRateForDescription('FL TAX 7.0%'));
        $this->assertEqual(0, ZMTaxRates::instance()->getTaxRateForDescription('foo bar'));
    }

}

?>
