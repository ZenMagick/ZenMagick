<?php

/**
 * Test UI date handling.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestUIDateHandling extends UnitTestCase {

    /**
     * Test date handling.
     */
    public function testDates() {
        ZMRequest::setParameterMap(array('dob' => '09/08/1966'));
        $account = ZMLoader::make('Account');
        $account->populate();
        echo $account->getDob()."<BR>";
        echo ZM_DATETIME_FORMAT .'-> '. UI_DATE_FORMAT."<BR>";
        echo ZMToolbox::instance()->locale->shortDate($account->getDob(), false)."<BR>";

    }

}

?>
