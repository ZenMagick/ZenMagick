<?php

/**
 * Test database mappings.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestDBMappings extends ZMTestCase {

    /**
     * {@inheritDoc}
     */
    public function skip() {
        $this->skipIf(true, 'Skip until database API stable');
    }

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
                if (!ZMLangUtils::inArray($property, $excludes)) {
                    $this->assertFalse(array_key_exists($property, $allFields), '%s duplicate property: '.$property.' in table: '.$table);
                    $allFields[$property] = $property;
                }
            }
        }
    }

}

?>
