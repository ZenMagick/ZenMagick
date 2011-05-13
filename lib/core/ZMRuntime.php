<?php
/*
 * ZenMagick - Another PHP framework.
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
 * Central place for runtime stuff.
 *
 * <p>This is kind of the <em>application context</em>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.core
 */
class ZMRuntime {
    private static $databaseMap_ = array();
    private static $entityManagerMap_ = array();
    private static $context_ = null;
    private static $yaml_ = null;


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
     *  <dd>The requested implementation class; if omitted, this defaults to
     *   <code>ZMSettings::get('zenmagick.core.database.provider')</code>.</dd>
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
        if (is_string($conf)) {
            $dbconf = ZMLangUtils::toArray(ZMSettings::get('doctrine.dbal.connections.'.$conf));
        } else {
            $default = ZMLangUtils::toArray(ZMSettings::get('doctrine.dbal.connections.default'));
            $dbconf = array_merge($default, $conf);
        }

        ksort($dbconf);
        $key = serialize($dbconf);
        if (!array_key_exists($key, self::$databaseMap_)) {
            $provider = array_key_exists('provider', $dbconf) ? $dbconf['provider'] : ZMSettings::get('zenmagick.core.database.provider', 'ZMPdoDatabase');
            self::$databaseMap_[$key] = new $provider($dbconf);
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
     * Get a specific entity manager by configuration name
     *
     * @todo really only supports one entity manager
     * @return array List of <code>Doctrine\ORM\EntityManager</code> instances.
     */
    public static function getEntityManager($conf='default') {
        self::$entityManagerMap_[$conf] = self::getDatabase($conf)->getEntityManager(ZMLangUtils::toArray(ZMSettings::get('doctrine.orm')));
        return self::$entityManagerMap_[$conf];
    }

    /**
     * Get a list of all used entity managers
     *
     * @return array List of <code>Doctrine\ORM\EntityManager</code> instances.
     */
    public static function getEntityManagers() {
        return self::$entityManagerMap_;
    }

    /**
     * Get the full ZenMagick installation path.
     *
     * @return string The ZenMagick installation folder.
     */
    public static function getInstallationPath() {
        return Runtime::getInstallationPath();
    }

    /**
     * Get the full application path (if set).
     *
     * @return string The application base folder or <code>null</code>.
     */
    public static function getApplicationPath() {
        return Runtime::getApplicationPath();
    }

    /**
     * Return the plugin base directory.
     *
     * <p>May be configured via the setting <em></em>. Default is <em>../lib/plugins</em>.</p>
     *
     * @return array List of base directories for plugins.
     */
    public static function getPluginBasePath() {
        return Runtime::getPluginBasePath();
    }

    /**
     * Get the currently elapsed page execution time.
     *
     * @param string time Optional execution timestamp to be used instead of the current time.
     * @return long The execution time in milliseconds.
     */
    public static function getExecutionTime($time=null) {
        return Runtime::getExecutionTime($time);
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
        require_once Runtime::getInstallationPath().'lib/core/external/spyc.php';

        if ($override) {
            return Spyc::YAMLLoadString($yaml);
        } else {
            return ZMLangUtils::arrayMergeRecursive($defaults, Spyc::YAMLLoadString($yaml));
        }
        /*
        if (null == self::$yaml_) {
            self::$yaml_ = new sfYamlParser();
        }
        if ($override) {
            return self::$yaml_->parse($yaml);
        } else {
            return ZMLangUtils::arrayMergeRecursive($defaults, self::$yaml_->parse($yaml));
        }
        */
    }

}
