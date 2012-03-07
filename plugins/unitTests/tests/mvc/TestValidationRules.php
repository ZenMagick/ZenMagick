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
?>
<?php

/**
 * Test validation rules.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestValidationRules extends ZMTestCase {

    /**
     * Test regexp.
     */
    public function testRegExp() {
        $request = new ZMRequest();
        $request->setContainer($this->container);
        $rule = new ZMRegexpRule('host', '/yahoo.com|localhost/i', 'no match');
        $this->assertNotNull($rule);
        $this->assertTrue($rule->validate($request, array('host' => 'localhost')));
        $this->assertTrue($rule->validate($request, array('host' => 'yahoo.COM')));

        $rule = new ZMRegexpRule('host', '/yahoo.com|localhost/', 'no match');
        $this->assertNotNull($rule);
        $this->assertTrue($rule->validate($request, array('host' => 'localhost')));
        $this->assertFalse($rule->validate($request, array('host' => 'yahoo.COM')));
    }

    /**
     * Test email validation.
     */
    public function testEmail() {
        $request = new ZMRequest();
        $request->setContainer($this->container);
        $rule = new ZMEmailRule('email', 'not valid');
        $this->assertNotNull($rule);
        $this->assertTrue($rule->validate($request, array('email' => 'mano@zenmagick.org')));
        $this->assertTrue($rule->validate($request, array('email' => 'foo@bar.net')));
        $this->assertTrue($rule->validate($request, array('email' => 'FOO@baR.net')));

        $this->assertFalse($rule->validate($request, array('email' => 'foobar.net')));
    }

}
