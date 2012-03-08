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
namespace zenmagick\apps\store\admin\services;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Admin user service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AdminUserService extends ZMObject {

    /**
     * Add a few things.
     *
     * @param AdminUser user The user to finalize.
     * @return AdminUser Finalized user.
     */
    protected function finalizeUser($user) {
        if (null == $user) {
            return null;
        }

        // set roles
        foreach ($this->container->get('adminUserRoleService')->getRolesForId($user->getId()) as $role) {
            $user->addRole($role);
        }

        return $user;
    }

    /**
     * Get user for the given id.
     *
     * @param int id The user id.
     * @return AdminUser A <code>AdminUser</code> instance or <code>null</code>.
     */
    public function getUserForId($id) {
        $sql = "SELECT *
                FROM " . TABLE_ADMIN . "
                WHERE admin_id = :id";
        $args = array('id' => $id);
        return $this->finalizeUser(\ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_ADMIN, 'zenmagick\apps\store\admin\entities\AdminUser'));
    }

    /**
     * Get user for the given user name.
     *
     * @param string name The user name.
     * @return AdminUser A <code>AdminUser</code> instance or <code>null</code>.
     */
    public function getUserForName($name) {
        $sql = "SELECT *
                FROM " . TABLE_ADMIN . "
                WHERE admin_name = :name";
        $args = array('name' => $name);
        return $this->finalizeUser(\ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_ADMIN, 'zenmagick\apps\store\admin\entities\AdminUser'));
    }

    /**
     * Get all users.
     *
     * @param boolean demoOnly Optional flag to load demo users only; default is <code>false</code>.
     * @return array List of <code>AdminUser</code> instances.
     */
    public function getAllUsers($demoOnly=false) {
        $sql = "SELECT *
                FROM " . TABLE_ADMIN;
        if ($demoOnly) {
            $sql .= " WHERE admin_level = :live";
        }
        $users = array();
        foreach (\ZMRuntime::getDatabase()->fetchAll($sql, array('live' => false), TABLE_ADMIN, 'zenmagick\apps\store\admin\entities\AdminUser') as $adminUser) {
            $users[] = $this->finalizeUser($adminUser);
        }

        return $users;
    }

    /**
     * Get user for the given email.
     *
     * @param string email The user email.
     * @return AdminUser A <code>AdminUser</code> instance or <code>null</code>.
     */
    public function getUserForEmail($email) {
        $sql = "SELECT *
                FROM " . TABLE_ADMIN . "
                WHERE admin_email = :email";
        $args = array('email' => $email);
        return $this->finalizeUser(\ZMRuntime::getDatabase()->querySingle($sql, $args, TABLE_ADMIN, 'zenmagick\apps\store\admin\entities\AdminUser'));
    }

    /**
     * Create user.
     *
     * @param ZMUser user The user.
     * @return AdminUser The updated <code>AdminUser</code> instance.
     */
    public function createUser($user) {
        $user = \ZMRuntime::getDatabase()->createModel(TABLE_ADMIN, $user);
        $this->container->get('adminUserRoleService')->setRolesForId($user->getId(), $user->getRoles());
        return true;
    }

    /**
     * Update user.
     *
     * @param ZMUser user The user.
     * @return AdminUser The updated <code>AdminUser</code> instance.
     */
    public function updateUser($user) {
        \ZMRuntime::getDatabase()->updateModel(TABLE_ADMIN, $user);
        $this->container->get('adminUserRoleService')->setRolesForId($user->getId(), $user->getRoles());
        return true;
    }

    /**
     * Delete user.
     *
     * @param int id The user id.
     */
    public function deleteUserForId($id) {
        $adminUserRoleService = $this->container->get('adminUserRoleService');
        // remove roles
        $roles = $adminUserRoleService->getRolesForId($id);
        $adminUserRoleService->setRolesForId($id, $roles);
        $sql = "DELETE FROM " . TABLE_ADMIN . "
                WHERE admin_id = :id";
        // delete user
        \ZMRuntime::getDatabase()->updateObj($sql, array('id' => $id), TABLE_ADMIN);
        return true;
    }

}
