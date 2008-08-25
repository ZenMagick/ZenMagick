<?php

/**
 * Test database mappings.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestDBMappings extends UnitTestCase {

    /**
     * Test duplicate fields.
     */
    public function testDuplicates() {
        $excludes = array('languageId'=>true, 'categoryId'=>true, 'productId'=>true, 'orderId'=>true, 'accountId'=>true, 'name'=>true, 'sortOrder'=>true);
        $mapper = ZMDbTableMapper::instance();
        $tables = $mapper->getTableNames();
        $allFields = array();
        foreach ($tables as $table) {
            $tableFields = $mapper->getMapping($table);
            foreach (array_keys($tableFields) as $property) {
                $this->assertFalse((array_key_exists($property, $allFields) && !array_key_exists($property, $excludes)), '%s duplicate property: '.$property.' in table: '.$table);
                $allFields[$property] = $property;
            }
        }
    }

}

?>
