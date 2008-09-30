<?php

/**
 * Test database implementations.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMDatabase extends ZMTestCase {
    static $PROVIDERS = array('ZMCreoleDatabase', 'ZMZenCartDatabase');

    /**
     * Test table meta data.
     */
    public function runTestTableMetaData($database) {
        $tableMeta = $database->getMetaData(TABLE_PRODUCTS_DESCRIPTION);
        $this->assertEqual(6, count($tableMeta));
        $this->assertTrue(array_key_exists('products_name', $tableMeta));
        $this->assertTrue(is_array($tableMeta['products_name']));
        $this->assertEqual('string', $tableMeta['products_name']['type']);
        $this->assertEqual(64, $tableMeta['products_name']['maxLen']);
    }

    /**
     * Table meta data test runner.
     */
    public function testTableMetaData() {
        foreach (self::$PROVIDERS as $provider) {
            $database = ZMRuntime::getDatabase(array('provider' => $provider));
            $this->runTestTableMetaData($database);
        }
    }

}

?>
