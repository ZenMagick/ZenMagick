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
namespace ZenMagick\AdminBundle\Services;

use ZenMagick\Base\ZMObject;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Admin user service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AdminUserService extends ZMObject implements UserProviderInterface
{
    /**
     * Add a few things.
     *
     * @param UserInterface user The user to refresh.
     * @return UserInterface Refresh user.
     */
    public function refreshUser($user)
    {
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
     * @return UserInterface A <code>UserInterface</code> instance or <code>null</code>.
     */
    public function getUserForId($id)
    {
        $sql = "SELECT *
                FROM %table.admin%
                WHERE admin_id = :id";
        $args = array('id' => $id);

        return $this->refreshUser(\ZMRuntime::getDatabase()->querySingle($sql, $args, 'admin', 'ZenMagick\AdminBundle\Entity\AdminUser'));
    }

    /**
     * Get user for the given user name.
     *
     * @param string name The user name.
     * @return UserInterface A <code>UserInterface</code> instance or <code>null</code>.
     */
    public function loadUserByUsername($name)
    {
        $sql = "SELECT *
                FROM %table.admin%
                WHERE admin_name = :username";
        $args = array('username' => $name);

        return $this->refreshUser(\ZMRuntime::getDatabase()->querySingle($sql, $args, 'admin', 'ZenMagick\AdminBundle\Entity\AdminUser'));
    }

    /**
     * Get all users.
     *
     * @param boolean demoOnly Optional flag to load demo users only; default is <code>false</code>.
     * @return array List of <code>UserInterface</code> instances.
     */
    public function getAllUsers($demoOnly=false)
    {
        $sql = "SELECT *
                FROM %table.admin%";
        if ($demoOnly) {
            $sql .= " WHERE admin_level = :live";
        }
        $users = array();
        foreach (\ZMRuntime::getDatabase()->fetchAll($sql, array('live' => false), 'admin', 'ZenMagick\AdminBundle\Entity\AdminUser') as $adminUser) {
            $users[] = $this->refreshUser($adminUser);
        }

        return $users;
    }

    /**
     * Get user for the given email.
     *
     * @param string email The user email.
     * @return UserInterface A <code>UserInterface</code> instance or <code>null</code>.
     */
    public function getUserForEmail($email)
    {
        $sql = "SELECT *
                FROM %table.admin%
                WHERE admin_email = :email";
        $args = array('email' => $email);

        return $this->refreshUser(\ZMRuntime::getDatabase()->querySingle($sql, $args, 'admin', 'ZenMagick\AdminBundle\Entity\AdminUser'));
    }

    /**
     * Create user.
     *
     * @param ZMUser user The user.
     * @return UserInterface The updated <code>UserInterface</code> instance.
     */
    public function createUser($user)
    {
        $user = \ZMRuntime::getDatabase()->createModel('admin', $user);
        $this->container->get('adminUserRoleService')->setRolesForId($user->getId(), $user->getRoles());

        return true;
    }

    /**
     * Update user.
     *
     * @param ZMUser user The user.
     * @return UserInterface The updated <code>UserInterface</code> instance.
     */
    public function updateUser($user)
    {
        \ZMRuntime::getDatabase()->updateModel('admin', $user);
        $this->container->get('adminUserRoleService')->setRolesForId($user->getId(), $user->getRoles());

        return true;
    }

    /**
     * Delete user.
     *
     * @param int id The user id.
     */
    public function deleteUserForId($id)
    {
        $adminUserRoleService = $this->container->get('adminUserRoleService');
        // remove roles
        $roles = $adminUserRoleService->getRolesForId($id);
        $adminUserRoleService->setRolesForId($id, $roles);
        $sql = "DELETE FROM %table.admin%
                WHERE admin_id = :id";
        // delete user
        \ZMRuntime::getDatabase()->updateObj($sql, array('id' => $id), 'admin');

        return true;
    }

    /**
     * @{inheritDoc}
     * @todo actually implement :)
     */
    public function supportsClass($class)
    {
        return true;
    }
}
