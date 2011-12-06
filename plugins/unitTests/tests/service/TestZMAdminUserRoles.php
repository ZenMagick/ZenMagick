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

/**
 * Test <code>ZMAdminUserRoles</code>.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMAdminUserRoles extends ZMTestCase {

    /**
     * Set up.
     */
    public function setUp() {
        parent::setUp();
        ZMRuntime::getDatabase()->update('TRUNCATE TABLE ' . DB_PREFIX.'admin_roles');
        ZMRuntime::getDatabase()->update('TRUNCATE TABLE ' . DB_PREFIX.'admins_to_roles');
        ZMAdminUserRoles::instance()->addRole('admin');
        ZMAdminUserRoles::instance()->addRole('helpdesk');
        ZMRuntime::getDatabase()->update('INSERT INTO ' . DB_PREFIX.'admins_to_roles' . ' VALUES(1, 1)');
        ZMRuntime::getDatabase()->update('INSERT INTO ' . DB_PREFIX.'admins_to_roles' . ' VALUES(1, 2)');
    }

    /**
     * Tear down.
     */
    public function tearDown() {
        parent::tearDown();
        ZMRuntime::getDatabase()->update('TRUNCATE TABLE ' . DB_PREFIX.'admin_roles');
        ZMRuntime::getDatabase()->update('TRUNCATE TABLE ' . DB_PREFIX.'admins_to_roles');
        ZMAdminUserRoles::instance()->addRole('admin');
        ZMAdminUserRoles::instance()->addRole('demo');
        ZMRuntime::getDatabase()->update('INSERT INTO ' . DB_PREFIX.'admins_to_roles' . ' VALUES(1, 1)');
    }

    /**
     * Test get all roles.
     */
    public function testGetAllRoles() {
        $expected = array(1 => 'admin', 2 => 'helpdesk');
        $roles = ZMAdminUserRoles::instance()->getAllRoles();
        $this->assertEqual($expected, $roles);
    }

    /**
     * Test get roles for id.
     */
    public function testGetRolesForId() {
        $expected = array(1 => 'admin', 2 => 'helpdesk');
        $roles = ZMAdminUserRoles::instance()->getRolesForId(1);
        $this->assertEqual($expected, $roles);
    }

    /**
     * Test add role.
     */
    public function testAddRole() {
        $id = ZMAdminUserRoles::instance()->addRole('customerservice');
        $this->assertTrue(0 < $id);
    }

    /**
     * Test delete role.
     */
    public function testDeleteRole() {
        ZMAdminUserRoles::instance()->addRole('customerservice');
        $expected = array(1 => 'admin', 2 => 'helpdesk', 3 => 'customerservice');
        $roles = ZMAdminUserRoles::instance()->getAllRoles();
        $this->assertEqual($expected, $roles);
        ZMAdminUserRoles::instance()->deleteRole('customerservice');
        $expected = array(1 => 'admin', 2 => 'helpdesk');
        $roles = ZMAdminUserRoles::instance()->getAllRoles();
        $this->assertEqual($expected, $roles);
    }

    /**
     * Test set roles for id.
     */
    public function testSetRolesForId() {
        ZMAdminUserRoles::instance()->setRolesForId(1, array('admin'));
        $expected = array(1 => 'admin');
        $roles = ZMAdminUserRoles::instance()->getRolesForId(1);
        $this->assertEqual($expected, $roles);

        ZMAdminUserRoles::instance()->setRolesForId(1, array('helpdesk'));
        $expected = array(2 => 'helpdesk');
        $roles = ZMAdminUserRoles::instance()->getRolesForId(1);
        $this->assertEqual($expected, $roles);

        ZMAdminUserRoles::instance()->setRolesForId(1, array('admin', 'helpdesk'));
        $expected = array(1 => 'admin', 2 => 'helpdesk');
        $roles = ZMAdminUserRoles::instance()->getRolesForId(1);
        $this->assertEqual($expected, $roles);

        ZMAdminUserRoles::instance()->setRolesForId(1, array('admin'));
        $expected = array(1 => 'admin');
        $roles = ZMAdminUserRoles::instance()->getRolesForId(1);
        $this->assertEqual($expected, $roles);
    }

}
