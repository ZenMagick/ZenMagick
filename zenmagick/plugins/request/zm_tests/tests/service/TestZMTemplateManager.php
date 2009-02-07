<?php

/**
 * Test template manager service.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMTemplateManager extends ZMTestCase {

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
            $this->assertEqual($field['expected'], ZMTemplateManager::instance()->getFieldLength($field['table'], $field['column']));
        }
    }

}

?>
