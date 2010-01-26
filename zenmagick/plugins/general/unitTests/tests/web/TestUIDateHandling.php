<?php

/**
 * Test UI date handling.
 *
 * @package org.zenmagick.plugins.unitTests.tests
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
        $map = array_merge(array('dob' => '09/08/1966'), $this->getRequest()->getParameterMap());
        $account = ZMLoader::make('Account');
        $account = ZMBeanUtils::setAll($account, $map);
        $this->assertEqual('09/08/1966', $this->getRequest()->getToolbox()->locale->shortDate($account->getDob()));

    }

}

?>
