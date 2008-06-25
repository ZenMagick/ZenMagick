<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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

    // load ZenMagick core
    include dirname(dirname(__FILE__)) . '/external.php';

    $tables = array(TABLE_COUNTRIES);
    foreach ($tables as $table) {
        $rows = ZMRuntime::getDatabase()->query('SHOW FULL COLUMNS FROM '.$table);
        $name = str_replace(ZM_DB_PREFIX, '', $table);
        echo "'".$name."' => array(\n";
        foreach ($rows as $row) {
            $typeMap = array('int'=>'integer','char'=>'string','varchar'=>'string', 'tinyint'=>'integer', 'text'=>'string', 'decimal' => 'float');
            $type = preg_replace('/(.*)\(.*\)/', '\1', $row['Type']);
            if (isset($typeMap[$type])) {
                $type=$typeMap[$type];
            } 

            $line = "    '". $row['Field'] . "' => '" . 'column=' . $row['Field'] . ';type='.$type;
            if ('PRI' == $row['Key']) {
                $line .= ';key=true';
            }
            if (false !== strpos($row['Extra'], 'auto_increment')) {
                $line .= ';auto=true';
            }
            echo $line."',\n";
        }
        echo "),\n";
    }

?>
