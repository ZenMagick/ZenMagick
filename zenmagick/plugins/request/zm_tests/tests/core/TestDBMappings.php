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
        $excludes = 'languageId,categoryId,productId,orderId,accountId,countryId,zoneId,attributeId,attributeValueId,siteId,orderStatusId,orderProductId,couponId';
        $mapper = ZMDbTableMapper::instance();
        $tables = $mapper->getTableNames();
        $allFields = array();
        foreach ($tables as $table) {
            $tableFields = $mapper->getMapping($table);
            foreach (array_keys($tableFields) as $property) {
                if (!ZMTools::inArray($property, $excludes)) {
                    $this->assertFalse(array_key_exists($property, $allFields), '%s duplicate property: '.$property.' in table: '.$table);
                    $allFields[$property] = $property;
                }
            }
        }
    }

}

?>
