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

use zenmagick\base\Beans;

/**
 * Test UI date handling.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
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
        $account = Beans::getBean('ZMAccount');
        $account = Beans::setAll($account, $map);
        $this->assertEqual('09/08/1966', $this->getRequest()->getToolbox()->locale->shortDate($account->getDob()));

    }

}
