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
 * Test <code>AdminUserRoleService</code>.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestAdminUserRoleService extends ZMTestCase {

    /**
     * Set up.
     */
    public function setUp() {
        parent::setUp();
        $adminUserRoleService = $this->container->get('adminUserRoleService');
        ZMRuntime::getDatabase()->executeUpdate('TRUNCATE TABLE ' . DB_PREFIX.'admin_roles');
        ZMRuntime::getDatabase()->executeUpdate('TRUNCATE TABLE ' . DB_PREFIX.'admins_to_roles');
        $adminUserRoleService->addRole('admin');
        $adminUserRoleService->addRole('helpdesk');
        ZMRuntime::getDatabase()->executeUpdate('INSERT INTO ' . DB_PREFIX.'admins_to_roles' . ' VALUES(1, 1)');
        ZMRuntime::getDatabase()->executeUpdate('INSERT INTO ' . DB_PREFIX.'admins_to_roles' . ' VALUES(1, 2)');
    }

    /**
     * Tear down.
     */
    public function tearDown() {
        parent::tearDown();
        $adminUserRoleService = $this->container->get('adminUserRoleService');
        ZMRuntime::getDatabase()->executeUpdate('TRUNCATE TABLE ' . DB_PREFIX.'admin_roles');
        ZMRuntime::getDatabase()->executeUpdate('TRUNCATE TABLE ' . DB_PREFIX.'admins_to_roles');
        $adminUserRoleService->addRole('admin');
        $adminUserRoleService->addRole('demo');
        ZMRuntime::getDatabase()->executeUpdate('INSERT INTO ' . DB_PREFIX.'admins_to_roles' . ' VALUES(1, 1)');
    }

    /**
     * Test get all roles.
     */
    public function testGetAllRoles() {
        $expected = array(1 => 'admin', 2 => 'helpdesk');
        $roles = $this->container->get('adminUserRoleService')->getAllRoles();
        $this->assertEqual($expected, $roles);
    }

    /**
     * Test get roles for id.
     */
    public function testGetRolesForId() {
        $expected = array(1 => 'admin', 2 => 'helpdesk');
        $roles = $this->container->get('adminUserRoleService')->getRolesForId(1);
        $this->assertEqual($expected, $roles);
    }

    /**
     * Test add role.
     */
    public function testAddRole() {
        $id = $this->container->get('adminUserRoleService')->addRole('customerservice');
        $this->assertTrue(0 < $id);
    }

    /**
     * Test delete role.
     */
    public function testDeleteRole() {
        $adminUserRoleService = $this->container->get('adminUserRoleService');
        $adminUserRoleService->addRole('customerservice');
        $expected = array(1 => 'admin', 2 => 'helpdesk', 3 => 'customerservice');
        $roles = $adminUserRoleService->getAllRoles();
        $this->assertEqual($expected, $roles);
        $adminUserRoleService->deleteRole('customerservice');
        $expected = array(1 => 'admin', 2 => 'helpdesk');
        $roles = $adminUserRoleService->getAllRoles();
        $this->assertEqual($expected, $roles);
    }

    /**
     * Test set roles for id.
     */
    public function testSetRolesForId() {
        $adminUserRoleService = $this->container->get('adminUserRoleService');
        $adminUserRoleService->setRolesForId(1, array('admin'));
        $expected = array(1 => 'admin');
        $roles = $adminUserRoleService->getRolesForId(1);
        $this->assertEqual($expected, $roles);

        $adminUserRoleService->setRolesForId(1, array('helpdesk'));
        $expected = array(2 => 'helpdesk');
        $roles = $adminUserRoleService->getRolesForId(1);
        $this->assertEqual($expected, $roles);

        $adminUserRoleService->setRolesForId(1, array('admin', 'helpdesk'));
        $expected = array(1 => 'admin', 2 => 'helpdesk');
        $roles = $adminUserRoleService->getRolesForId(1);
        $this->assertEqual($expected, $roles);

        $adminUserRoleService->setRolesForId(1, array('admin'));
        $expected = array(1 => 'admin');
        $roles = $adminUserRoleService->getRolesForId(1);
        $this->assertEqual($expected, $roles);
    }

}
