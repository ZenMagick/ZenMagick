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
 * Sacs permissions service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SacsPermissionService extends ZMObject {

    /**
     * Get all available permissions.
     *
     * @return array List of permission details.
     */
    public function getAll() {
        return \ZMRuntime::getDatabase()->fetchAll('SELECT * FROM %table.sacs_permissions%');
    }

    /**
     * Get permissons for a role.
     *
     * @param string role The role.
     * @return array List of permission details.
     */
    public function getPermissionsForRole($role) {
        return \ZMRuntime::getDatabase()->fetchAll('SELECT * FROM %table.sacs_permissions% where type = "role" AND name = :name', array('name' => $role), 'sacs_permissions');
    }

    /**
     * Set permissons for a role.
     *
     * @param string role The role.
     * @param array pages List of allowed request ids.
     */
    public function setPermissionsForRole($role, $pages) {
        $currentPages = array();
        foreach ($this->getPermissionsForRole($role) as $info) {
            $currentPages[] = $info['rid'];
        }

        // removed?
        $remove = array();
        foreach ($currentPages as $page) {
            if (!in_array($page, $pages)) {
                $remove[] = $page;
            }
        }

        // added?
        $add = array();
        foreach ($pages as $page) {
            if (!in_array($page, $currentPages)) {
                $add[] = $page;
            }
        }

        if (0 < count($remove)) {
            $sql = "DELETE FROM %table.sacs_permissions%
                    WHERE  type = 'role' AND name = :name
                      AND rid in (:rid)";
            \ZMRuntime::getDatabase()->updateObj($sql, array('name' => $role, 'rid' => $remove), 'sacs_permissions');
        }

        if (0 < count($add)) {
            $sql = "INSERT INTO %table.sacs_permissions%
                    (rid, type, name) VALUES (:rid, 'role', :name)";
            foreach ($add as $rid) {
                \ZMRuntime::getDatabase()->updateObj($sql, array('rid' => $rid, 'name' => $role), 'sacs_permissions');
            }
        }
    }

}
