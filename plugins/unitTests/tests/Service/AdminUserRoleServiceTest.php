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

use ZenMagick\ZenMagickBundle\Test\BaseTestCase;

/**
 * Test <code>AdminUserRoleService</code>.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AdminUserRoleServiceTest extends BaseTestCase
{
    /**
     * Set up.
     */
    public function setUp()
    {
        parent::setUp();
        $adminUserRoleService = $this->get('adminUserRoleService');
        ZMRuntime::getDatabase()->executeUpdate('TRUNCATE TABLE %table.admin_roles%');
        ZMRuntime::getDatabase()->executeUpdate('TRUNCATE TABLE %table.admins_to_roles%');
        $adminUserRoleService->addRole('admin');
        $adminUserRoleService->addRole('helpdesk');
        ZMRuntime::getDatabase()->executeUpdate('INSERT INTO %table.admins_to_roles% VALUES(1, 1)');
        ZMRuntime::getDatabase()->executeUpdate('INSERT INTO %table.admins_to_roles% VALUES(1, 2)');
    }

    /**
     * Tear down.
     */
    public function tearDown()
    {
        parent::tearDown();
        $adminUserRoleService = $this->get('adminUserRoleService');
        ZMRuntime::getDatabase()->executeUpdate('TRUNCATE TABLE %table.admin_roles%');
        ZMRuntime::getDatabase()->executeUpdate('TRUNCATE TABLE %table.admins_to_roles%');
        $adminUserRoleService->addRole('admin');
        $adminUserRoleService->addRole('demo');
        ZMRuntime::getDatabase()->executeUpdate('INSERT INTO %table.admins_to_roles% VALUES(1, 1)');
    }

    /**
     * Test get all roles.
     */
    public function testGetAllRoles()
    {
        $expected = array(1 => 'admin', 2 => 'helpdesk');
        $roles = $this->get('adminUserRoleService')->getAllRoles();
        $this->assertEquals($expected, $roles);
    }

    /**
     * Test get roles for id.
     */
    public function testGetRolesForId()
    {
        $expected = array(1 => 'admin', 2 => 'helpdesk');
        $roles = $this->get('adminUserRoleService')->getRolesForId(1);
        $this->assertEquals($expected, $roles);
    }

    /**
     * Test add role.
     */
    public function testAddRole()
    {
        $id = $this->get('adminUserRoleService')->addRole('customerservice');
        $this->assertTrue(0 < $id);
    }

    /**
     * Test delete role.
     */
    public function testDeleteRole()
    {
        $adminUserRoleService = $this->get('adminUserRoleService');
        $adminUserRoleService->addRole('customerservice');
        $expected = array(1 => 'admin', 2 => 'helpdesk', 3 => 'customerservice');
        $roles = $adminUserRoleService->getAllRoles();
        $this->assertEquals($expected, $roles);
        $adminUserRoleService->deleteRole('customerservice');
        $expected = array(1 => 'admin', 2 => 'helpdesk');
        $roles = $adminUserRoleService->getAllRoles();
        $this->assertEquals($expected, $roles);
    }

    /**
     * Test set roles for id.
     */
    public function testSetRolesForId()
    {
        $adminUserRoleService = $this->get('adminUserRoleService');
        $adminUserRoleService->setRolesForId(1, array('admin'));
        $expected = array(1 => 'admin');
        $roles = $adminUserRoleService->getRolesForId(1);
        $this->assertEquals($expected, $roles);

        $adminUserRoleService->setRolesForId(1, array('helpdesk'));
        $expected = array(2 => 'helpdesk');
        $roles = $adminUserRoleService->getRolesForId(1);
        $this->assertEquals($expected, $roles);

        $adminUserRoleService->setRolesForId(1, array('admin', 'helpdesk'));
        $expected = array(1 => 'admin', 2 => 'helpdesk');
        $roles = $adminUserRoleService->getRolesForId(1);
        $this->assertEquals($expected, $roles);

        $adminUserRoleService->setRolesForId(1, array('admin'));
        $expected = array(1 => 'admin');
        $roles = $adminUserRoleService->getRolesForId(1);
        $this->assertEquals($expected, $roles);
    }

}
