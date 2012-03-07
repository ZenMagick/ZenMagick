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

/**
 * Test template manager service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestTemplateManager extends ZMTestCase {

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
            $this->assertEqual($field['expected'], $this->container->get('templateManager')->getFieldLength($field['table'], $field['column']));
        }
    }

}
