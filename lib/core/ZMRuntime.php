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


/**
 * Central place for runtime stuff.
 *
 * <p>This is kind of the <em>application context</em>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package org.zenmagick.core
 */
class ZMRuntime {
    private static $databaseMap_ = array();


    /**
     * Get the database (provider).
     *
     * <p><code>ZMDatabase</code> instances are cached, based on the given <code>$conf</code> data.</p>
     *
     * <p>Supported keys for <em>$conf</em> are:</p>
     * <dl>
     *  <dt>driver</dt>
     *  <dd>The database driver/type; default is <code>pdo_mysql</code>.</dd>
     *  <dt>host</dt>
     *  <dd>The database host; default is <code>DB_SERVER</code>.</dd>
     *  <dt>port</dt>
     *  <dd>The database port; optional, no default.</dd>
     *  <dt>unix_socket</dt>
     *  <dd>Optional unix socket name if used to talk to the database instead of IP.</dd>
     *  <dt>user</dt>
     *  <dd>The database username; default is <code>DB_SERVER_USERNAME</code>.</dd>
     *  <dt>password</dt>
     *  <dd>The database password; default is <code>DB_SERVER_PASSWORD</code>.</dd>
     *  <dt>dbname</dt>
     *  <dd>The database name; default is <code>DB_DATABASE</code>.</dd>
     *  <dt>provider</dt>
     *  <dd>The requested implementation class; if omitted, this defaults to the setting
     *   <code>'zenmagick.core.database.provider'</code>.</dd>
     *  <dt>initQuery</dt>
     *  <dd>An optional init query to execute; useful to set the character encoding, etc.; default is <code>null</code>.</dd>
     * </dl>
     *
     * <p>If the given parameter <code>$conf</code> is a string, the method will
     * lookup database settings using a settings key build like:  <em>doctrine.dbal.connections.[<code>$conf</code>]</em>.</p>
     *
     * @param mixed conf Optional configuration; either an array with any of the supported keys, or a string; default is <em>default</em>.
     * @return ZMDatabase A <code>ZMDatabase</code> implementation.
     */
    public static function getDatabase($conf='default') {
        if (!Runtime::getContainer()->isFrozen()) {
            //Runtime::getLogging()->trace('database access with container not yet frozen!');
        }
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
            $dbconf['wrapperClass'] = 'ZMDatabase';
            self::$databaseMap_[$key] = Doctrine\DBAL\DriverManager::getConnection($dbconf);
        }

        return self::$databaseMap_[$key];
    }

    /**
     * Get a list of all used databases.
     *
     * @return array List of <code>ZMDatabase</code> instances.
     */
    public static function getDatabases() {
        return self::$databaseMap_;
    }

    /**
     * Load mappings from a YAML style string.
     *
     * @param string yaml The yaml style mappings.
     * @param array defaults Optional defaults for merging; default is an empty array.
     * @param boolean override Optional flag to control whether to override existing mappings or to merge;
     *  default is <code>true</code> to override.
     */
    public static function yamlParse($yaml, $defaults=array(), $override=true) {
        require_once Runtime::getInstallationPath().'/lib/core/spyc.php';

        if ($override) {
            return Spyc::YAMLLoadString($yaml);
        } else {
            return Toolbox::arrayMergeRecursive($defaults, Spyc::YAMLLoadString($yaml));
        }
    }

}
