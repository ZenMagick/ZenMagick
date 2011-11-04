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

use zenmagick\base\Runtime;

/**
 * Admin user prefs service.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.services
 */
class ZMAdminUserPrefs extends ZMObject {

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
        return Runtime::getContainer()->get('adminUserPrefService');
    }


    /**
     * Get pref for the given name.
     *
     * @param int adminId The adminId.
     * @param string name The pref name.
     * @return string The value or <code>null</code>.
     */
    public function getPrefForName($adminId, $name) {
        $sql = "SELECT value
                FROM " . DB_PREFIX.'admin_prefs' . "
                WHERE admin_id = :admin_id AND name = :name";
        $args = array('admin_id' => $adminId, 'name' => $name);
        if (null != ($result = ZMRuntime::getDatabase()->querySingle($sql, $args, DB_PREFIX.'admin_prefs'))) {
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
                FROM " . DB_PREFIX.'admin_prefs' . "
                WHERE admin_id = :admin_id AND name = :name";
        if (null != ($result = ZMRuntime::getDatabase()->querySingle($sql, $args, DB_PREFIX.'admin_prefs'))) {
            $sql = "UPDATE " . DB_PREFIX.'admin_prefs' . "
                    SET value = :value
                    WHERE admin_id = :admin_id AND name = :name";
        } else {
            $sql = "INSERT INTO " . DB_PREFIX.'admin_prefs' . "
                    (admin_id, name, value)
                    VALUES (:admin_id, :name, :value)";
        }
        ZMRuntime::getDatabase()->update($sql, $args, DB_PREFIX.'admin_prefs');
    }

}
