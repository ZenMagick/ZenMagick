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

use ZenMagick\ZenMagickBundle\Test\BaseTestCase;

/**
 * Test template manager service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TemplateManagerTest extends BaseTestCase
{
    /**
     * Test field length.
     */
    public function testFieldLength()
    {
        $fields = array(
            // table, column, expected value
            array('table' => 'customers', 'column' => 'customers_email_address', 'expected' => 96),
            array('table' => 'address_book', 'column' => 'entry_street_address', 'expected' => 64)
        );

        foreach ($fields as $field) {
            $this->assertEquals($field['expected'], $this->get('templateManager')->getFieldLength($field['table'], $field['column']));
        }
    }

}
