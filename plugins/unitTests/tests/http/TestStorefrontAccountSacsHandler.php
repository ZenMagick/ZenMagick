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

use zenmagick\http\sacs\SacsManager;
use zenmagick\http\sacs\handler\UserRoleSacsHandler;
use zenmagick\http\sacs\handler\UserRoleCredentials;
use zenmagick\apps\store\model\mock\MockAccount;
use zenmagick\plugins\unitTests\simpletest\TestCase;
use zenmagick\apps\storefront\http\sacs\StorefrontAccountSacsHandler;

/**
 * Test StorefrontAccountSacsHandler
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestStorefrontAccountSacsHandler extends TestCase {

    /**
     * Get a sacs manager.
     */
    protected function getSacsManager() {
        $sacsManager = new SacsManager();
        $sacsManager->load($this->getTestsBaseDirectory().'/http/config/level_sacs_mappings.yaml');
        return $sacsManager;
    }

    /**
     * Get an account
     *
     * @param string level The authorization level.
     * @return ZMAccount An account.
     */
    protected function getAccount($level) {
        $account = new MockAccount();
        $account->setType($level);
        return $account;
    }

    /**
     * Test anonymous
     */
    public function testAnonymous() {
        $sacsManager = $this->getSacsManager();
        $handler = new StorefrontAccountSacsHandler();
        $handler->setContainer($this->container);
        $this->assertTrue($handler->evaluate('index', $this->getAccount(ZMAccount::ANONYMOUS), $sacsManager));
        $this->assertFalse($handler->evaluate('account', $this->getAccount(ZMAccount::ANONYMOUS), $sacsManager));
    }

    /**
     * Test registered
     */
    public function testRegistered() {
        $sacsManager = $this->getSacsManager();
        $handler = new StorefrontAccountSacsHandler();
        $handler->setContainer($this->container);
        $this->assertTrue($handler->evaluate('index', $this->getAccount(ZMAccount::REGISTERED), $sacsManager));
        $this->assertTrue($handler->evaluate('account', $this->getAccount(ZMAccount::REGISTERED), $sacsManager));
    }

    /**
     * Test guest
     */
    public function testGuest() {
        $sacsManager = $this->getSacsManager();
        $handler = new StorefrontAccountSacsHandler();
        $handler->setContainer($this->container);
        $this->assertTrue($handler->evaluate('index', $this->getAccount(ZMAccount::GUEST), $sacsManager));
        $this->assertFalse($handler->evaluate('account', $this->getAccount(ZMAccount::GUEST), $sacsManager));
        $this->assertTrue($handler->evaluate('checkout_shipping', $this->getAccount(ZMAccount::GUEST), $sacsManager));
    }

}
