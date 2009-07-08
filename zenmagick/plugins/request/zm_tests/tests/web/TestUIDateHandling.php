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
     * {@inheritDoc}
     */
    public function skip() {
        $this->skipIf(true, 'obsolete in this form due to form bean changes');
    }

    /**
     * Test date handling.
     */
    public function testDates() {
        //XXX: executing tests relies on some values - proving that a singleton request object is **bad**
        $map = array_merge(array('dob' => '09/08/1966'), ZMRequest::instance()->getParameterMap());
        $account = ZMLoader::make('Account');
        ZMBeanUtils::setAll($account, $map);
        $this->assertEqual('09/08/1966', ZMToolbox::instance()->locale->shortDate($account->getDob(), false));

    }

}

?>
