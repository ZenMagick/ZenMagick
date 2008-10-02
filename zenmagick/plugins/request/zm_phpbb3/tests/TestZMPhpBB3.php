<?php

/**
 * Test TestZMPhpBB3 adapter class.
 *
 * @package org.zenmagick.plugins.zm_phpbb3
 * @author DerManoMann
 * @version $Id$
 */
class TestZMPhpBB3 extends ZMTestCase {

    /**
     * Test duplicate nickname validation.
     */
    public function testVDuplicateNickname() {
        $phpBB3 = ZMLoader::make('ZMPhpBB3');
        $this->assertTrue($phpBB3->vDuplicateNickname(array('nick' => 'foobarx')));
        $this->assertFalse($phpBB3->vDuplicateNickname(array('nick' => 'Anonymous')));
    }

    /**
     * Test duplicate email validation.
     */
    public function testVDuplicateEmail() {
        $phpBB3 = ZMLoader::make('ZMPhpBB3');
        $this->assertTrue($phpBB3->vDuplicateEmail(array('email_address' => 'foo@bar.com')));
        $this->assertFalse($phpBB3->vDuplicateEmail(array('email_address' => 'mano@zenmagick.org')));
    }

}

?>
