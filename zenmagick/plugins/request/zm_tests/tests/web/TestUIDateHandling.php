<?php

/**
 * Test UI date handling.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestUIDateHandling extends ZMTestCase {

    /**
     * Test date handling.
     */
    public function testDates() {
        ZMRequest::setParameterMap(array('dob' => '09/08/1966'));
        $account = ZMLoader::make('Account');
        $account->populate();
        $this->assertEqual('09/08/1966', ZMToolbox::instance()->locale->shortDate($account->getDob(), false));

    }

}

?>
