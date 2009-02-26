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
     * {@inheritDoc}
     */
    public function tearDown() {
        parent::tearDown();
        $this->getAdapter()->removeAccount('martin@mixedmatter.co.nz');
    }

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
        $this->assertTrue($this->getAdapter()->vDuplicateNickname(array('nick' => 'foobarx')));
        $this->assertFalse($this->getAdapter()->vDuplicateNickname(array('nick' => 'Anonymous')));
    }

    /**
     * Test duplicate email validation.
     */
    public function testVDuplicateEmail() {
        $this->assertTrue($this->getAdapter()->vDuplicateEmail(array('email_address' => 'foo@bar.com')));
        $this->testCreateAccount();
        $this->assertFalse($this->getAdapter()->vDuplicateEmail(array('email_address' => 'martin@mixedmatter.co.nz')));
    }

    /**
     * Test create account.
     */
    public function testCreateAccount() {
        $result = $this->getAdapter()->createAccount('DerManoMann', 'mano11', 'martin@mixedmatter.co.nz');
        $this->assertTrue($result);
    }

    /**
     * Test update account.
     */
    public function testUpdateAccount() {
        $this->testCreateAccount();
        $result = $this->getAdapter()->updateAccount('DerManoMann', 'mano12', 'martin@mixedmatter.co.nz');
        $this->assertTrue($result);
    }

}

?>
