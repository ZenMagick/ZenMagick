<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Test TestZMPhpBB3 adapter class.
 *
 * @package org.zenmagick.plugins.phpbb3
 * @author DerManoMann
 * @version $Id$
 */
class TestZMPhpBB3Adapter extends ZMTestCase {
    private $adapter_ = null;


    /**
     * {@inheritDoc}
     */
    public function setUp() {
        parent::setUp();
        $this->getAdapter()->removeAccount('martin@mixedmatter.co.nz');
        $account = new ZMAccount();
        $account->setEmail('martin@mixedmatter.co.nz');
        $account->setGender('m');
        $account->setFirstName('mano');
        $account->setLastName('mann');
        $account->setNickName('DerManoMann');
        $account->setPhone('03 333 3333');
        $account->setPassword('secret');
        ZMAccounts::instance()->createAccount($account);
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown() {
        parent::tearDown();
        $this->getAdapter()->removeAccount('martin@mixedmatter.co.nz');
        $account = ZMAccounts::instance()->getAccountForEmailAddress('martin@mixedmatter.co.nz');
        ZMRuntime::getDatabase()->removeModel(TABLE_CUSTOMERS, $account);
    }

    /**
     * Get the phpBB3 adapter.
     */
    protected function getAdapter() {
        if (null == $this->adapter_) {
            $this->adapter_ = new ZMPhpBB3Adapter();
        }

        return $this->adapter_;
    }

    /**
     * Test duplicate nickname validation.
     */
    public function testVDuplicateNickname() {
        $this->assertTrue($this->getAdapter()->vDuplicateNickname(array('nickName' => 'foobarxxx')));
        $this->assertFalse($this->getAdapter()->vDuplicateNickname(array('nickName' => 'Anonymous')));
    }

    /**
     * Test duplicate email validation.
     */
    public function testVDuplicateEmail() {
        $this->assertTrue($this->getAdapter()->vDuplicateEmail(array('email' => 'foo@bar.com')));
        $this->testCreateAccount();
        $this->assertFalse($this->getAdapter()->vDuplicateEmail(array('email' => 'martin@mixedmatter.co.nz')));
    }

    /**
     * Test create account.
     */
    public function testCreateAccount() {
        $account = ZMAccounts::instance()->getAccountForEmailAddress('martin@mixedmatter.co.nz');
        $result = $this->getAdapter()->createAccount($account, 'foob123', 'martin@mixedmatter.co.nz');
        $this->assertTrue($result);
    }

    /**
     * Test update account.
     */
    public function testUpdateAccount() {
        $this->testCreateAccount();
        $result = $this->getAdapter()->updateAccount('DerManoMann', 'foob1234', 'martin@mixedmatter.co.nz');
        $this->assertTrue($result);
        $result = $this->getAdapter()->updateAccount('DerManoMann', null, 'martin@mixedmatter.co.nz');
        $this->assertTrue($result);
    }

}

?>
