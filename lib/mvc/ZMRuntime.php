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

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;


class ZMRuntime {
    private static $databaseMap_ = array();


    public static function getDatabase($conf='default') {
        $settingsService = Runtime::getSettings();
        if (is_string($conf)) {
            $dbconf = Toolbox::toArray($settingsService->get('apps.store.database.'.$conf));
        } else {
            $default = Toolbox::toArray($settingsService->get('apps.store.database.default'));
            $dbconf = array_merge($default, $conf);
        }

        ksort($dbconf);
        $key = serialize($dbconf);
        if (!array_key_exists($key, self::$databaseMap_)) {
            $dbconf['wrapperClass'] = 'zenmagick\\base\\database\\Connection';
            self::$databaseMap_[$key] = Doctrine\DBAL\DriverManager::getConnection($dbconf);
        }

        return self::$databaseMap_[$key];
    }

    public static function getDatabases() {
        return self::$databaseMap_;
    }
}
