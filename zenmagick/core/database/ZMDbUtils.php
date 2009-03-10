<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * SQL/database utils.
 *
 * @author DerManoMann
 * @package org.zenmagick.database
 * @version $Id$
 */
class ZMDbUtils {
    /**
     * Mapping of native data types to API types.
     */
    public static $NATIVE_TO_API_TYPEMAP = array(
        'char' => 'string',
        'varchar' => 'string',
        'int' => 'integer',
        'tinyint' => 'integer',
        'smallint' => 'integer',
        'mediumint' => 'integer',
        'bigint' => 'integer',
        'int unsigned' => 'integer',
        'decimal' => 'float',
        'real' => 'float',
        'text' => 'string',
        'tinytext' => 'string',
        'mediumtext' => 'string',
        'mediumblob', 'blob'
    );


    /**
     * Execute a SQL patch.
     *
     * <p><strong>NOTE:</strong> This functionallity is only available in the context
     * of the ZenMagick installation or plugins page.</p>
     *
     * @param string sql The sql.
     * @param array Result message list.
     * @param boolean Debug flag.
     * @return boolean <code>true</code> for success, <code>false</code> if the execution fails.
     */
    public static function executePatch($sql, $messages, $debug=false) {
        if (!ZMSettings::get('isAdmin')) {
            return false;
        }

        if ($debug) {
            $_GET['debug'] = 'ON';
        }
        $sql = ZMTools::sanitize($sql);
        if (!empty($sql)) {
            $results = executeSql($sql, DB_DATABASE, DB_PREFIX);
            foreach (ZMDbUtils::processPatchResults($results) as $msg) {
                $messages[] = $msg;
            }
            return empty($results['error']);
        }

        return true;
    }

    /**
     * Resolve a given SQL file name.
     *
     * <p>This will try to find the most specific available file for the configured database type.</p>
     *
     * <p>Filenames are expected in the format <em>[name]-[driver].sql</em> if driver specific SQL is required. If no
     * specific file is found, the default <em>[name].sql</em> will is tried. If that is also not found, <code>null</code>
     * will be returned.</p>
     *
     * @param string filename The filename.
     * @return string The most specific filename or <code>null</code>.
     */
    public static function resolveSQLFilename($filename) {
        $config = ZMRuntime::getDatabase()->getConfig();
        $driver = $config['driver'];
        if (false !== ($ldot = strrpos($filename, '.'))) {
            $driverFilename = substr($filename, 0, $ldot) . '-' . $driver . substr($filename, $ldot);
            if (file_exists($driverFilename)) {
                return $driverFilename;
            }
        }

        if (file_exists($filename)) {
            return $filename;
        }

        return null;
    }

    /**
     * Process SQL patch messages.
     *
     * @param array The execution results.
     * @return array The results converted to messages.
     */
    private static function processPatchResults($results) {
        $messages = array();
        if ($results['queries'] > 0 && $results['queries'] != $results['ignored']) {
            array_push($messages, ZMLoader::make("Message", $results['queries'].' statements processed.', 'success'));
        } else {
            array_push($messages, ZMLoader::make("Message", 'Failed: '.$results['queries'].'.', 'error'));
        }

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $value) {
                array_push($messages, ZMLoader::make("Message", 'ERROR: '.$value.'.', 'error'));
            }
        }
        if ($results['ignored'] != 0) {
            array_push($messages, ZMLoader::make("Message", 'Note: '.$results['ignored'].' statements ignored. See "upgrade_exceptions" table for additional details.', 'warn'));
        }

        return $messages;
    }

    /**
     * Generate a database mapping for the given table.
     *
     * @param string table The table name.
     * @param ZMDatabase database Optional database; default is <code>null</code> to use the default.
     * @param boolean print Optional flag to also print the mapping in a form that can be used
     *  to cut&paste into a mapping file; default is <code>false</code>.
     * @return array The mapping.
     */
    public static function buildTableMapping($table, $database=null, $print=false) {
        if (null === $database) {
            $database = ZMRuntime::getDatabase();
        }
        // check for prefix
        if (null === ($tableMetaData = $database->getMetaData($table))) {
            // try adding the prefix
            $table = ZM_DB_PREFIX.$table;
            if (null === ($tableMetaData = $database->getMetaData($table))) {
                return null;
            }
        }

        $mapping = array();
        ob_start();
        echo "'".str_replace(ZM_DB_PREFIX, '', $table)."' => array(\n";
        $first = true;
        foreach ($tableMetaData as $column) {
            $type = preg_replace('/(.*)\(.*\)/', '\1', $column['type']);
            if (array_key_exists($type, ZMDbUtils::$NATIVE_TO_API_TYPEMAP)) {
                $type = ZMDbUtils::$NATIVE_TO_API_TYPEMAP[$type];
            } 

            $line = 'column=' . $column['name'] . ';type=' . $type;
            if ($column['key']) {
                $line .= ';key=true';
            }
            if ($column['autoIncrement']) {
                $line .= ';auto=true';
            }
            $mapping[$column['name']] = $line;
            if (!$first) {
                echo ",\n";
            }
            echo "    '" . $column['name'] . "' => '" . $line . "'";
            $first = false;
        }
        echo "\n),\n";

        $text = ob_get_clean();

        if ($print) {
            echo $text;
        }

        return $mapping;
    }

}

?>
