<?php

/**
 * Test currency.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMCurrency extends ZMTestCase {

    /**
     * Test currency parsing.
     */
    public function testParse() {
        $currency = ZMCurrencies::instance()->getCurrencyForCode('USD');
        if ($this->assertNotNull($currency)) {
            $this->assertEqual(3.15, $currency->parse('$3.15'));
        }
    }

    /**
     * Test currency formatting.
     */
    public function testFormat() {
        $currency = ZMCurrencies::instance()->getCurrencyForCode('USD');
        if ($this->assertNotNull($currency)) {
            $this->assertEqual('$3.15', $currency->format(3.15));
        }
    }

}

?>
