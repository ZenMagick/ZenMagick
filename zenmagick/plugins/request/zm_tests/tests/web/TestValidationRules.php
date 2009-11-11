<?php

/**
 * Test validation rules.
 *
 * @package org.zenmagick.plugins.zm_tests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestValidationRules extends ZMTestCase {

    /**
     * Test regexp.
     */
    public function testRegExp() {
        $request = new ZMRequest();
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
        $rule = new ZMEmailRule('email', 'not valid');
        $this->assertNotNull($rule);
        $this->assertTrue($rule->validate($request, array('email' => 'mano@zenmagick.org')));
        $this->assertTrue($rule->validate($request, array('email' => 'foo@bar.net')));
        $this->assertTrue($rule->validate($request, array('email' => 'FOO@baR.net')));

        $this->assertFalse($rule->validate($request, array('email' => 'foobar.net')));
    }

}

?>
