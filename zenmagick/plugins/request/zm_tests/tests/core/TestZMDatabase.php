<?php

/**
 * Test database implementations.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMDatabase extends ZMTestCase {
    static $PROVIDERS = array('ZMCreoleDatabase', 'ZMZenCartDatabase', 'ZMPdoDatabase');

    /**
     * Test table meta data.
     */
    public function runTestTableMetaData($database, $provider) {
        $tableMeta = $database->getMetaData(TABLE_PRODUCTS_DESCRIPTION);
        $this->assertEqual(6, count($tableMeta), '%s: '.$provider);
        $this->assertTrue(array_key_exists('products_name', $tableMeta), '%s: '.$provider);
        $this->assertTrue(is_array($tableMeta['products_name']), '%s: '.$provider);
        $this->assertEqual('string', $tableMeta['products_name']['type'], '%s: '.$provider);
        $this->assertEqual(64, $tableMeta['products_name']['maxLen'], '%s: '.$provider);
    }

    /**
     * Table meta data test runner.
     */
    public function testTableMetaData() {
        foreach (self::$PROVIDERS as $provider) {
            $database = ZMRuntime::getDatabase(array('provider' => $provider));
            $this->runTestTableMetaData($database, $provider);
        }
    }

    /**
     * Test auto mapping.
     */
    public function testAutoMapping() {
        static $create_table = "CREATE TABLE zm_db_test ( id int(11) NOT NULL auto_increment, name varchar(32) NOT NULL, PRIMARY KEY (id)) TYPE=MyISAM;";
        static $drop_table = "DROP TABLE IF EXISTS zm_db_test;";

        static $expectedMapping = array(
            'id' => 'column=id;type=integer;key=true;auto=true',
            'name' => 'column=name;type=string' 
        );
        static $expectedOutput = "'zm_db_test' => array(\n    'id' => 'column=id;type=integer;key=true;auto=true',\n    'name' => 'column=name;type=string'\n),\n";

        // create test tabe
        ZMRuntime::getDatabase()->update($drop_table);
        ZMRuntime::getDatabase()->update($create_table);

        ob_start();
        $mapping = ZMDbUtils::buildTableMapping('zm_db_test', null, true);
        $output = ob_get_clean();
        if ($this->assertTrue(is_array($mapping))) {
            $this->assertEqual($expectedMapping, $mapping);
        }
        $this->assertEqual($expectedOutput, $output);

        //XXX: insert,update,delete using model

        // drop again
        ZMRuntime::getDatabase()->update($drop_table);
    }

    /**
     * Test indexed field names.
     */
    public function testIndexedFields() {
        // use simple country query to compare results
        $sql1 = "SELECT * FROM " . TABLE_COUNTRIES . " WHERE countries_id = :countryId";
        $results1 = ZMRuntime::getDatabase()->query($sql1, array('countryId' => 153), TABLE_COUNTRIES);

        $sql2 = "SELECT * FROM " . TABLE_COUNTRIES . " WHERE countries_id = :1#countryId";
        $results2 = ZMRuntime::getDatabase()->query($sql2, array('1#countryId' => 153), TABLE_COUNTRIES);

        $this->assertEqual($results1, $results2);
    }

}

?>
