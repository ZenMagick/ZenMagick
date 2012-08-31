<?php
/*
 * ZenMagick - Another PHP framework.
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

use ZenMagick\base\Runtime;
use ZenMagick\base\Toolbox;


class ZMRuntime {
    private static $databaseMap_ = array();

    /**
     * Set database connection parameters for a connection name.
     *
     * @param string name name of connection
     * @param array database connection parameters
     */
    public static function setDatabase($name, $conf) {
        if (!isset($conf['wrapperClass'])) {
            $conf['wrapperClass'] = 'ZenMagick\\base\\database\\Connection';
        }
        self::$databaseMap_[$name] = $conf;
    }

    /**
     * Get a database connection by name.
     *
     * @param string name get default connection if null.
     * @return ZenMagick\base\database\Connection
     */
    public static function getDatabase($conf='default') {
        if (is_array(self::$databaseMap_[$conf])) {
            self::$databaseMap_[$conf] = Doctrine\DBAL\DriverManager::getConnection(self::$databaseMap_[$conf]);
        }
        return self::$databaseMap_[$conf];
    }

    public static function getDatabases() {
        return self::$databaseMap_;
    }
}
