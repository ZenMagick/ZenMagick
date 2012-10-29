<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test database mappings.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestMappings extends TestCase
{
    /**
     * {@inheritDoc}
     */
    public function skip()
    {
        $this->skipIf(true, 'Skip until database API stable');
    }

    /**
     * Test duplicate fields.
     */
    public function testDuplicates()
    {
        $excludes = 'languageId,categoryId,productId,orderId,accountId,countryId,zoneId,attributeId,attributeValueId,siteId,orderStatusId,orderProductId,couponId';
        $mapper = ZMRuntime::getDatabase()->getMapper();
        $tables = $mapper->getTableNames();
        $allFields = array();
        foreach ($tables as $table) {
            $tableFields = $mapper->getMapping($table);
            foreach (array_keys($tableFields) as $property) {
                if (in_array($property, $excludes)) {
                    $this->assertFalse(array_key_exists($property, $allFields), '%s duplicate property: '.$property.' in table: '.$table);
                    $allFields[$property] = $property;
                }
            }
        }
    }

}
