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


/**
 * Show settings controlller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.settings
 */
class ZMSettingsShowController extends ZMPluginAdmin2Controller {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('settings');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get value for the given key and type.
     *
     * @param string key The key.
     * @param string type The type.
     * @return string The value as string.
     */
    protected function getStringValue($key, $type) {
        if (null === ($value = ZMSettings::get($key))) {
            return '-- NOT SET --';
        }

        switch ($type) {
        case 'password':
            return '********';
        case 'int':
        case 'string':
            break;
        case 'array':
            if (is_array($value)) {
                // find out if the array is a hash map
                $isMap = false;
                foreach (array_keys($value) as $ak) {
                    if (!is_numeric($ak)) {
                        $isMap = true;
                        break;
                    }
                }
                if ($isMap) {
                    $mv = '';
                    foreach ($value as $ak => $av) {
                        $mv .= $ak.'='.$av.',';
                    }
                    $value = $mv;
                } else {
                    $value = implode(',', $value);
                }
            }
            break;
        case 'octal':
            $value = '0'.decoct($value);
            break;
        case 'boolean':
            $value = ZMLangUtils::asBoolean($value) ? 'true' : 'false';
            break;
        default:
            echo $details['type']."<BR>";
            break;
        }

        $value = (string)$value;
        if (60 < strlen($value)) {
            $value = str_replace(array(','), array(', '), $value);
        }
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $settingDetails = array();
        // prepare values
        foreach (zm_get_settings_details() as $group => $groupDetails) {
            foreach ($groupDetails as $sub => $subDetails) {
                foreach ($subDetails as $subKey => $details) {
                    $key = $group.'.'.$sub.'.'.$details['key'];
                    $typeList = explode(':', $details['type']);
                    $type = array_pop($typeList);
                    if (false === strpos($details['type'], 'dynamic:')) {
                        $settingDetails[$group][$sub][$subKey]['fullkey'] = $key;
                        $settingDetails[$group][$sub][$subKey]['key'] = $details['key'];
                        $settingDetails[$group][$sub][$subKey]['desc'] = $details['desc'];
                        $settingDetails[$group][$sub][$subKey]['value'] = $this->getStringValue($key, $type);
                    } else {
                        // dynamic
                        $dt = explode(':', $details['type']);
                        $dynVar = '@'.$dt[1].'@';
                        $bits = explode($dynVar, $key);
                        $prefix = $bits[0];
                        $suffix = $bits[1];
                        foreach (ZMSettings::getAll() as $akey => $avalue) {
                            if (ZMLangUtils::startsWith($akey, $prefix) && ZMLangUtils::endsWith($akey, $suffix)) {
                                // potential match
                                $dynVal = substr($akey, strlen($prefix), -strlen($suffix));
                                if (!ZMLangUtils::isEmpty($dynVal)) {
                                    // yep
                                    $subKey = str_replace($dynVar, $dynVal, $details['key']);

                                    // build real key
                                    $key = $group.'.'.$sub.'.'.$subKey;
                                    $settingDetails[$group][$sub][$subKey]['fullkey'] = $key;
                                    $settingDetails[$group][$sub][$subKey]['key'] = $subKey;
                                    $settingDetails[$group][$sub][$subKey]['desc'] = '* '.str_replace($dynVar, $dynVal, $details['desc']);
                                    $settingDetails[$group][$sub][$subKey]['value'] = $this->getStringValue($key, $type);
                                }
                            }
                        }
                    }
                }
            }
        }

        // check for settings without details
        foreach (ZMSettings::getAll() as $key => $value) {
            foreach ($settingDetails as $group => $groupDetails) {
                if (ZMLangUtils::startsWith($key, $group.'.')) {
                    $found = false;
                    foreach ($groupDetails as $subDetails) {
                        foreach ($subDetails as $details) {
                            if ($key == $details['fullkey']) {
                                $found = true;
                                break;
                            }
                        }
                    }
                    if (!$found) {
                        $this->messageService->warn('No details found for key: "'.$key.'"');
                    }
                }
            }
        }

        return $this->findView(null, array('settingDetails' => $settingDetails));
    }

}
