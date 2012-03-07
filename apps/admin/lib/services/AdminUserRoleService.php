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
?>
<?php
namespace zenmagick\apps\store\admin\services;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Admin user roles service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AdminUserRoleService extends ZMObject {

    /**
     * Get a list of all roles.
     *
     * @return array List of roles with the role id as key.
     */
    public function getAllRoles() {
        $sql = "SELECT admin_role_id, name from " . DB_PREFIX.'admin_roles';
        $roles = array();
        foreach (\ZMRuntime::getDatabase()->fetchAll($sql, array(), DB_PREFIX.'admin_roles') as $result) {
            $roles[$result['admin_role_id']] = $result['name'];
        }

        return $roles;
    }

    /**
     * Add a role.
     *
     * @return int The new role id.
     */
    public function addRole($name) {
        $sql = "INSERT INTO " . DB_PREFIX.'admin_roles' . " (name) VALUES(:name)";
        $result = \ZMRuntime::getDatabase()->createModel(DB_PREFIX.'admin_roles', array('name' => $name));
        return $result['admin_role_id'];
    }

    /**
     * Delete a role.
     *
     * @param string name The role to delete.
     * @return boolean <code>true</code> on success.
     */
    public function deleteRole($name) {
        $allRolesLookup = array_flip($this->getAllRoles());
        $roleId = $allRolesLookup[$name];
        // 1) delete mappings
        $sql = "DELETE FROM " . DB_PREFIX.'admins_to_roles' . "
                WHERE admin_role_id = :admin_role_id";
        \ZMRuntime::getDatabase()->updateObj($sql, array('admin_role_id' => $roleId), DB_PREFIX.'admins_to_roles');
        \ZMRuntime::getDatabase()->removeModel(DB_PREFIX.'admins_to_roles', array('admin_role_id' => $roleId));
        return true;
    }

    /**
     * Get roles for a given user id.
     *
     * @param int id The user id.
     * @return array List of roles with the role id as key.
     */
    public function getRolesForId($id) {
        $sql = "SELECT DISTINCT ar.admin_role_id, ar.name from " . DB_PREFIX.'admin_roles' . " AS ar, " . DB_PREFIX.'admins_to_roles' . " AS atr
                WHERE atr.admin_role_id = ar.admin_role_id
                  AND atr.admin_id = :admin_id";
        $roles = array();
        $args = array('admin_id' => $id);
        foreach (\ZMRuntime::getDatabase()->fetchAll($sql, $args, array(DB_PREFIX.'admin_roles', DB_PREFIX.'admins_to_roles')) as $result) {
            $roles[$result['admin_role_id']] = $result['name'];
        }

        return $roles;
    }

    /**
     * Set the given roles.
     *
     * @param int id The user id.
     * @param array List of roles.
     */
    public function setRolesForId($id, $roles) {
        $allRolesLookup = array_flip($this->getAllRoles());
        $currentRoles = $this->getRolesForId($id);
        $remove = array();
        $add = array();
        // which to add?
        foreach ($roles as $role) {
            if (!in_array($role, $currentRoles)) {
                $add[] = $allRolesLookup[$role];
            }
        }
        // which to remove?
        foreach ($currentRoles as $roleId => $role) {
            if (!in_array($role, $roles)) {
                $remove[] = $roleId;
            }
        }

        if (0 < count($remove)) {
            $sql = "DELETE FROM " . DB_PREFIX.'admins_to_roles' . "
                    WHERE  admin_id = :admin_id
                      AND admin_role_id in (:admin_role_id)";
            \ZMRuntime::getDatabase()->updateObj($sql, array('admin_id' => $id, 'admin_role_id' => $remove), DB_PREFIX.'admins_to_roles');
        }

        if (0 < count($add)) {
            $sql = "INSERT INTO " . DB_PREFIX.'admins_to_roles' . "
                    (admin_id, admin_role_id) VALUES (:admin_id, :admin_role_id)";
            foreach ($add as $addId) {
                \ZMRuntime::getDatabase()->updateObj($sql, array('admin_id' => $id, 'admin_role_id' => $addId), DB_PREFIX.'admins_to_roles');
            }
        }

        return true;
    }

}
