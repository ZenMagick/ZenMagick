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
namespace ZenMagick\plugins\vBulletin\tests;

use ZenMagick\Base\Beans;
use ZenMagick\plugins\unitTests\simpletest\TestCase;

/**
 * ZMVBulletin adapter test class.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestVBulletinAdapter extends TestCase
{
    private $adapter_ = null;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->getAdapter()->removeAccount('root@localhost');
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->getAdapter()->removeAccount('root@localhost');
    }

    /**
     * Get the vBulletin adapter.
     */
    protected function getAdapter()
    {
        if (null == $this->adapter_) {
            $this->adapter_ = Beans::getBean('ZenMagick\plugins\vBulletin\VBulletinAdapter');
        }

        return $this->adapter_;
    }

    /**
     * Test duplicate nickname validation.
     */
    public function testVDuplicateNickname()
    {
        $this->assertTrue($this->getAdapter()->vDuplicateNickname(array('nickName' => 'foobarxy')));
        $this->assertFalse($this->getAdapter()->vDuplicateNickname(array('nickName' => 'admin')));
    }

    /**
     * Test duplicate email validation.
     */
    public function testVDuplicateEmail()
    {
        $this->assertTrue($this->getAdapter()->vDuplicateEmail(array('email' => 'foobardeng@bar.com')));
        $this->testCreateAccount();
        $this->assertFalse($this->getAdapter()->vDuplicateEmail(array('email' => 'root@localhost')));
    }

    /**
     * Test create account.
     */
    public function testCreateAccount()
    {
        $result = $this->getAdapter()->createAccount($this->container->get('accountService')->getAccountForId(1), 'foobardeng');
        $this->assertTrue($result);
    }

    /**
     * Test update account.
     */
    public function testUpdateAccount()
    {
        $this->testCreateAccount();
        $result = $this->getAdapter()->updateAccount('DerManoMann', 'foobardeng', 'root@localhost');
        $this->assertTrue($result);
        $result = $this->getAdapter()->updateAccount('DerManoMann', null, 'root@localhost');
        $this->assertTrue($result);
    }

}
