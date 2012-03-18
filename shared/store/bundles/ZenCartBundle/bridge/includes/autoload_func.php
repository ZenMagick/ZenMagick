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

$map = array();
$request = Runtime::getContainer()->get('request');
$session = Runtime::getcontainer()->get('session');
// @todo remove zpid asap
$map['%current_page%'] = !Runtime::isContextMatch('admin') ? $request->getRequestId() : $request->getParameter('zpid', 'index');
$map['%language%'] = $request->getSelectedLanguage()->getDirectory(); //$session->getValue('language');
$map['%template_dir%'] = Runtime::getContainer()->get('themeService')->getActiveThemeId();

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
                    if (!is_object($session->getValue($objectName)) || !$entry['checkInstantiated']) {
                        $session->setValue($objectName, new $className());
                    }
                } else {
                    $$objectName = new $className();
                }
            break;
            case 'objectMethod':
                $objectName = $entry['objectName'];
                $methodName = $entry['methodName'];
                $object = $session->getValue($objectName);
                if (is_object($object)) {
                    $object->$methodName();
                } else {
                    $$objectName->$methodName();
                }
            break;
            case 'service': // simple container service support with no ability to set arguments.
                if (!isset($entry['name'])) break;

                $method = isset($entry['method']) ? $entry['method'] : null;
                $resultVar = isset($entry['resultVar']) ? $entry['resultVar'] : null;
                if (null != $method) {
                    $loaderResultVar = Runtime::getContainer()->get($entry['name'])->$method();
                } else {
                    $loaderResultVar = Runtime::getContainer()->get($entry['name']);
                }
                if (null != $resultVar) {
                    $$resultVar = $loaderResultVar;
                } else {
                    unset($loaderResultVar);
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
                $entry['loadFile'] = str_replace(array_keys($map), array_values($map), $entry['loadFile']);
                $files = ZenCartBundle::resolveFiles($entry['loadFile']);
            break;
        }
        if (!empty($files)) {
            $once = isset($entry['once']) && $entry['once'];
            foreach ($files as $file) {
                if ($require) {
                    if ($once) {
                        require_once $file;
                    } else {
                        require $file;
                    }
                } else {
                    if ($once) {
                        include_once $file;
                    } else {
                        include $file;
                    }
                }
            }   
        }
    }
}
