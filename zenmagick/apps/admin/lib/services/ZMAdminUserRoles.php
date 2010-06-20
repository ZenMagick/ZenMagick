<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Admin user roles service.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.services
 */
class ZMAdminUserRoles extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('AdminUserRoles');
    }


    /**
     * Get a list of all roles.
     *
     * @return array List of roles with the role id as key.
     */
    public function getAllRoles() {
        $sql = "SELECT admin_role_id, name from " . ZM_TABLE_ADMIN_ROLES;
        $roles = array();
        foreach (ZMRuntime::getDatabase()->query($sql, array(), ZM_TABLE_ADMIN_ROLES) as $result) {
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
        $sql = "INSERT INTO " . ZM_TABLE_ADMIN_ROLES . " (name) VALUES(:name)";
        $result = ZMRuntime::getDatabase()->createModel(ZM_TABLE_ADMIN_ROLES, array('name' => $name));
        return $result['admin_role_id'];
    }

    /**
     * Delete a role.
     */
    public function deleteRole($name) {
        $allRolesLookup = array_flip($this->getAllRoles());
        $roleId = $allRolesLookup[$name];
        // 1) delete mappings
        $sql = "DELETE FROM " . ZM_TABLE_ADMINS_TO_ROLES . "
                WHERE admin_role_id = :admin_role_id";
        ZMRuntime::getDatabase()->update($sql, array('admin_role_id' => $roleId), ZM_TABLE_ADMINS_TO_ROLES);
        ZMRuntime::getDatabase()->removeModel(ZM_TABLE_ADMIN_ROLES, array('admin_role_id' => $roleId));
    }

    /**
     * Get roles for a given user id.
     *
     * @param int id The user id.
     * @return array List of roles with the role id as key.
     */
    public function getRolesForId($id) {
        $sql = "SELECT DISTINCT ar.admin_role_id, ar.name from " . ZM_TABLE_ADMIN_ROLES . " AS ar, " . ZM_TABLE_ADMINS_TO_ROLES . " AS atr
                WHERE atr.admin_role_id = ar.admin_role_id
                  AND atr.admin_id = :admin_id";
        $roles = array();
        $args = array('admin_id' => $id);
        foreach (ZMRuntime::getDatabase()->query($sql, $args, array(ZM_TABLE_ADMIN_ROLES, ZM_TABLE_ADMINS_TO_ROLES)) as $result) {
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
            $sql = "DELETE FROM " . ZM_TABLE_ADMINS_TO_ROLES . "
                    WHERE  admin_id = :admin_id
                      AND admin_role_id in (:admin_role_id)";
            ZMRuntime::getDatabase()->update($sql, array('admin_id' => $id, 'admin_role_id' => $remove), ZM_TABLE_ADMINS_TO_ROLES);
        }

        if (0 < count($add)) {
            $sql = "INSERT INTO " . ZM_TABLE_ADMINS_TO_ROLES . "
                    (admin_id, admin_role_id) VALUES (:admin_id, :admin_role_id)";
            foreach ($add as $addId) {
                ZMRuntime::getDatabase()->update($sql, array('admin_id' => $id, 'admin_role_id' => $addId), ZM_TABLE_ADMINS_TO_ROLES);
            }
        }

        return true;
    }

}
