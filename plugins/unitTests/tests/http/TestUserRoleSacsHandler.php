<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\http\sacs\SacsManager;
use zenmagick\http\sacs\handler\UserRoleSacsHandler;
use zenmagick\http\sacs\handler\UserRoleCredentials;

/**
 * Test SACS manager
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestUserRoleSacsHandler extends ZMTestCase {

    /**
     * Get a sacs manager.
     */
    protected function getSacsManager() {
        $sacsManager = new SacsManager();
        $sacsManager->load($this->getTestsBaseDirectory().'/http/config/user_role_sacs_mappings.yaml');
        return $sacsManager;
    }

    /**
     * Test stars.
     */
    public function testStar() {
        $sacsManager = $this->getSacsManager();
        $handler = new UserRoleSacsHandler();
        // no default
        $this->assertFalse($handler->evaluate('foo', null, $sacsManager));
        // * user
        $this->assertTrue($handler->evaluate('login', null, $sacsManager));
        $this->assertFalse($handler->evaluate('index', null, $sacsManager));
        // * role
        $this->assertTrue($handler->evaluate('index', new DummyUserRoleCredentials(), $sacsManager));
        // need valid role or user
        $this->assertFalse($handler->evaluate('plugins', new DummyUserRoleCredentials(), $sacsManager));
    }

    /**
     * Test users
     */
    public function testUsers() {
        $sacsManager = $this->getSacsManager();
        $handler = new UserRoleSacsHandler();
        $this->assertTrue($handler->evaluate('plugins', new DummyUserRoleCredentials('dilbert'), $sacsManager));
        $this->assertFalse($handler->evaluate('plugins', new DummyUserRoleCredentials('dogbert'), $sacsManager));
    }

    /**
     * Test roles
     */
    public function testRoles() {
        $sacsManager = $this->getSacsManager();
        $handler = new UserRoleSacsHandler();
        $this->assertTrue($handler->evaluate('plugins', new DummyUserRoleCredentials(null, array('foo')), $sacsManager));
        $this->assertFalse($handler->evaluate('plugins', new DummyUserRoleCredentials('ratbert', array('xx')), $sacsManager));
    }

}

/**
 * Test UserRoleCredentials
 */
class DummyUserRoleCredentials implements UserRoleCredentials {
    public $name;
    public $roles;

    /**
     * Create
     */
    public function __construct($name=null, $roles=array()) {
        $this->name = $name;
        $this->roles = $roles;
    }

    /**
     * {@inheritDoc}
     */
    public function getName() {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function hasRole($role) {
        return in_array($role, $this->roles);
    }

}
