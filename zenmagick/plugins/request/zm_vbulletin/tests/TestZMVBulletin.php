<?php

/**
 * ZMVBulletin adapter test class.
 *
 * @package org.zenmagick.plugins.zm_vbulletin
 * @author DerManoMann
 * @version $Id$
 */
class TestZMVBulletin extends ZMTestCase {
    private $vBulletin_ = null;


    /**
     * {@inheritDoc}
     */
    public function setUp() {
        parent::setUp();
        $this->getAdapter()->removeAccount('root@localhost');
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown() {
        parent::tearDown();
        $this->getAdapter()->removeAccount('root@localhost');
    }

    /**
     * Get the vBulletin adapter.
     */
    protected function getAdapter() {
        if (null == $this->vBulletin_) {
            $this->vBulletin_ = ZMLoader::make('VBulletinAdapter');
        }

        return $this->vBulletin_;
    }

    /**
     * Test duplicate nickname validation.
     */
    public function testVDuplicateNickname() {
        $this->assertTrue($this->getAdapter()->vDuplicateNickname(array('nickName' => 'foobarxy')));
        $this->assertFalse($this->getAdapter()->vDuplicateNickname(array('nickName' => 'admin')));
    }

    /**
     * Test duplicate email validation.
     */
    public function testVDuplicateEmail() {
        $this->assertTrue($this->getAdapter()->vDuplicateEmail(array('email' => 'foobardeng@bar.com')));
        $this->testCreateAccount();
        $this->assertFalse($this->getAdapter()->vDuplicateEmail(array('email' => 'root@localhost')));
    }

    /**
     * Test create account.
     */
    public function testCreateAccount() {
        $result = $this->getAdapter()->createAccount(ZMAccounts::instance()->getAccountForId(1), 'foobardeng');
        $this->assertTrue($result);
    }

    /**
     * Test update account.
     */
    public function testUpdateAccount() {
        $this->testCreateAccount();
        $result = $this->getAdapter()->updateAccount('DerManoMann', 'foobardeng', 'root@localhost');
        $this->assertTrue($result);
        $result = $this->getAdapter()->updateAccount('DerManoMann', null, 'root@localhost');
        $this->assertTrue($result);
    }

}

?>
