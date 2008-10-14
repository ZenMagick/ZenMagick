<?php

/**
 * Test layout service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMLayout extends ZMTestCase {

    /**
     * Test field length.
     */
    public function testFieldLength() {
        $fields = array(
            // table, column, expected value
            array('table' => TABLE_CUSTOMERS, 'column' => 'customers_email_address', 'expected' => 96),
            array('table' => TABLE_ADDRESS_BOOK, 'column' => 'entry_street_address', 'expected' => 64)
        );

        foreach ($fields as $field) {
            $this->assertEqual($field['expected'], ZMLayout::instance()->getFieldLength($field['table'], $field['column']));
        }
    }

}

?>
