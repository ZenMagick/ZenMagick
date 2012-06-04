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
 * Admin user prefs service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AdminUserPrefService extends ZMObject {

    /**
     * Get pref for the given name.
     *
     * @param int adminId The adminId.
     * @param string name The pref name.
     * @return string The value or <code>null</code>.
     */
    public function getPrefForName($adminId, $name) {
        $sql = "SELECT value
                FROM %table.admin_prefs%
                WHERE admin_id = :admin_id AND name = :name";
        $args = array('admin_id' => $adminId, 'name' => $name);
        if (null != ($result = \ZMRuntime::getDatabase()->querySingle($sql, $args, 'admin_prefs'))) {
            return $result['value'];
        }
        return null;
    }

    /**
     * Set pref for the given name.
     *
     * @param int adminId The adminId.
     * @param string name The pref name.
     * @param string value The pref value.
     */
    public function setPrefForName($adminId, $name, $value) {
        $args = array('admin_id' => $adminId, 'name' => $name, 'value' => $value);

        // check for insert/update first
        $sql = "SELECT value
                FROM %table.admin_prefs%
                WHERE admin_id = :admin_id AND name = :name";
        if (null != ($result = \ZMRuntime::getDatabase()->querySingle($sql, $args, 'admin_prefs'))) {
            $sql = "UPDATE %table.admin_prefs%
                    SET value = :value
                    WHERE admin_id = :admin_id AND name = :name";
        } else {
            $sql = "INSERT INTO %table.admin_prefs%
                    (admin_id, name, value)
                    VALUES (:admin_id, :name, :value)";
        }
        \ZMRuntime::getDatabase()->updateObj($sql, $args, 'admin_prefs');
    }

}
