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
        $rule = new ZMRegexpRule('host', '/yahoo.com|localhost/i', 'no match');
        $this->assertNotNull($rule);
        $this->assertTrue($rule->validate(array('host' => 'localhost')));
        $this->assertTrue($rule->validate(array('host' => 'yahoo.COM')));

        $rule = new ZMRegexpRule('host', '/yahoo.com|localhost/', 'no match');
        $this->assertNotNull($rule);
        $this->assertTrue($rule->validate(array('host' => 'localhost')));
        $this->assertFalse($rule->validate(array('host' => 'yahoo.COM')));
    }

    /**
     * Test email validation.
     */
    public function testEmail() {
        $rule = new ZMEmailRule('email', 'not valid');
        $this->assertNotNull($rule);
        $this->assertTrue($rule->validate(array('email' => 'mano@zenmagick.org')));
        $this->assertTrue($rule->validate(array('email' => 'foo@bar.net')));
        $this->assertTrue($rule->validate(array('email' => 'FOO@baR.net')));

        $this->assertFalse($rule->validate(array('email' => 'foobar.net')));
    }

}

?>
