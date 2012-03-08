<?php
/*
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
use zenmagick\apps\store\bundles\ZenCartBundle\ZenCartBundle;

/**
 * Cleaned up version of the Zen Cart auto loader
 *
 * @author Johnny Robeson
 */
reset($autoLoadConfig);
ksort($autoLoadConfig);
foreach ($autoLoadConfig as $actionPoint => $row) {
    foreach($row as $entry) {
        if (isset($entry['loaderPrefix']) && ($entry['loaderPrefix'] != $loaderPrefix)) continue;
        $files = array();
        $require = false;
        switch($entry['autoType']) {
            case 'classInstantiate':
                if(!isset($entry['checkInstantiated'])) $entry['checkInstantiated'] = false;
                if(!isset($entry['classSession'])) $entry['classSession'] = false;
                $objectName = $entry['objectName'];
                $className = $entry['className'];
                if ($entry['classSession']) {
                    if (!isset($_SESSION[$objectName]) || !$entry['checkInstantiated']) {
                        $_SESSION[$objectName] = new $className();
                    }
                } else {
                    $$objectName = new $className();
                }
            break;
            case 'objectMethod':
                $objectName = $entry['objectName'];
                $methodName = $entry['methodName'];
                $object = $_SESSION[$objectName];
                if (is_object($object)) {
                    $object->$methodName();
                } else {
                    $$objectName->$methodName();
                }
            break;
            case 'init_script':
                $files = ZenCartBundle::resolveFiles('includes/init_includes/'.$entry['loadFile']);
            break;
            case 'class':
                $files = ZenCartBundle::resolveFiles('includes/classes/'.$entry['loadFile']);
            break;
            case 'require':
                $require = true;
            case 'include':
            case 'include_glob':
                $files = ZenCartBundle::resolveFiles($entry['loadFile']);
            break;
        }
        foreach ($files as $file) {
            if ($require) {
                require $file;
            } else {
                include $file;
            }
        }
    }
}
