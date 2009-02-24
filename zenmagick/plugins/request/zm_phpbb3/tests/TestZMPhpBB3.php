<?php

/**
 * Test TestZMPhpBB3 adapter class.
 *
 * @package org.zenmagick.plugins.zm_phpbb3
 * @author DerManoMann
 * @version $Id$
 */
class TestZMPhpBB3 extends ZMTestCase {
    private $phpBB3_ = null;


    /**
     * Get the phpBB3 adapter.
     */
    protected function getAdapter() {
        if (null == $this->phpBB3_) {
            $this->phpBB3_ = ZMLoader::make('ZMPhpBB3');
        }

        return $this->phpBB3_;
    }

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

    /**
     * Test create account.
     */
    public function testCreateAccount() {
        $this->getAdapter()->createAccount('DerManoMann', 'mano11', 'martin@mixedmatter.co.nz');
    }

}

?>
