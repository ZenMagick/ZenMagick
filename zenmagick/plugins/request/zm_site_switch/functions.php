<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 *
 * $Id$
 */
?>
<?php

define('ZM_FILE_SITE_SWITCHER', DIR_FS_CATALOG.'includes/local/zm_site_switch.php');
define('ZM_SITE_SWITCHER_CONFIGURE_LINE', '<?php include(dirname(__FILE__).\'/zm_site_switch.php\'); /* added by zm_site_switch plugin */ ?>');


    /**
     * Site switch manager page.
     *
     * @package org.zenmagick.plugins.zm_site_switch
     * @return ZMPluginPage A plugin page or <code>null</code>.
     */
    function zm_site_switch_admin() {
    global $zm_site_switch;

        eval(zm_globals());
        $template = file_get_contents($zm_site_switch->getPluginDir().'/views/site_switch_admin.php');
        eval('?>'.$template);
        return new ZMPluginPage('zm_site_switch_admin', zm_l10n_get('Site Switch'), null);
    }


    /**
     * Check required permissions.
     *
     * @package org.zenmagick.plugins.zm_site_switch
     * @return boolean <code>true</code> if permissions are ok, <code>false</code> if not.
     */
    function zm_site_switch_check_permissions() {
    global $zm_messages;

        $localDir = dirname(ZM_FILE_SITE_SWITCHER);
        if (!file_exists($localDir)) {
            // can we create folder than all ok...
            if (is_writeable(dirname($localDir))) {
                return true;
            }
            $zm_messages->error('need permission to write '.dirname($localDir));
            return false;
        }

        if (is_writeable(dirname($localDir))) {
            $localConfig = $localDir.'/configure.php';
            if (file_exists($localConfig) && !is_writeable($localConfig)) {
                $zm_messages->error('need permission to update '.$localConfig);
                return false;
            }
            return true;
        }

        $zm_messages->error('need permission to write '.$localDir);
        return false;
    }


    /**
     * Setup/validate local config setup.
     *
     * @package org.zenmagick.plugins.zm_site_switch
     */
    function zm_site_switch_setup_switcher() {
    global $zm_messages;

        $localDir = dirname(ZM_FILE_SITE_SWITCHER);
        if (!is_dir($localDir)) {
            zm_mkdir($localDir);
            if (!is_dir($localDir)) {
                $zm_messages->error('could not create directory: \''.$localDir.'\'');
                return;
            }
        }
        $localConfig = $localDir.'/configure.php';

        if (!file_exists($localConfig)) {
            if ($handle = fopen($localConfig, 'wb')) {
                $ok = fwrite($handle, ZM_SITE_SWITCHER_CONFIGURE_LINE);
                fclose($handle);
            } else {
                $zm_messages->error('could not create file: \''.ZM_SITE_SWITCHER_CONFIGURE_LINE.'\'');
                return;
            }
        } else {
            $lines = file($localConfig);
            $done = false;
            foreach ($lines as $line) {
                if ($line == ZM_SITE_SWITCHER_CONFIGURE_LINE) {
                    $done = true;
                    break;
                }
            }
            if (!$done) {
                $handle = fopen($localConfig.'.tmp', 'wb');
                if ($handle) {
                    fwrite($handle, ZM_SITE_SWITCHER_CONFIGURE_LINE."\n");
                    $lineCount = count($lines) - 1;
                    foreach ($lines as $ii => $line) {
                        $eol = $ii < $lineCount ? "\n" : '';
                        fwrite($handle, rtrim($line).$eol);
                    }
                    fclose($handle);
                    if (file_exists($localConfig)) {
                        unlink($localConfig);
                    }
                    rename($localConfig.'.tmp', $localConfig);
                } else {
                    $zm_messages->error('could not create file in \''.$localDir.'\'');
                    return;
                }
            }
        }
    }


    /**
     * Uninstall switcher.
     *
     * @package org.zenmagick.plugins.zm_site_switch
     */
    function zm_site_switch_remove_switcher() {
    global $zm_messages;

        if (file_exists(ZM_FILE_SITE_SWITCHER)) {
            unlink(ZM_FILE_SITE_SWITCHER);
            if (is_dir($localDir)) {
                $zm_messages->error('could not remove file: \''.ZM_FILE_SITE_SWITCHER.'\'');
                return;
            }
        }

        $localDir = dirname(ZM_FILE_SITE_SWITCHER);
        $localConfig = $localDir.'/configure.php';
        if (file_exists($localConfig)) {
            $lines = file($localConfig);
            $tmp = array();
            foreach ($lines as $line) {
                $line = rtrim($line);
                if ($line != ZM_SITE_SWITCHER_CONFIGURE_LINE) {
                    $tmp[] = $line;
                }
            }
            $handle = fopen($localConfig.'.tmp', 'wb');
            if ($handle) {
                $lineCount = count($tmp) - 1;
                foreach ($tmp as $ii => $line) {
                    $eol = $ii < $lineCount ? "\n" : '';
                    fwrite($handle, $line.$eol);
                }
                fclose($handle);
                if (file_exists($localConfig)) {
                    unlink($localConfig);
                }
                rename($localConfig.'.tmp', $localConfig);
            } else {
                $zm_messages->error('could not write temp file in: \''.$localDir.'\'');
                return;
            }
        }
    }


    /**
     * Update site switcher config.
     *
     * @package org.zenmagick.plugins.zm_site_switch
     * @param array List of sitemap/themeId mappings
     */
    function zm_site_switch_config_write($mappings) {
        if (0 < count($mappings)) {
            // update file
            $content = '<?php  
/* added by zm_site_switch plugin */
$zm_server_names = array([SERVER_NAMES]);
$_zm_server_name = $_SERVER["HTTP_HOST"];
if (isset($zm_server_names[$_zm_server_name])) {
  define("HTTP_SERVER", "http://$_zm_server_name");
  define("HTTPS_SERVER", "https://$_zm_server_name");
}
?>';
            $server_names = '';
            $first = true;
            foreach ($mappings as $hostname => $themeId) {
                if (!$first) { $server_names .= ', '; }
                $server_names .= "'".$hostname."' => '".$themeId."'";
                $first = false;
            }
            $content = str_replace('[SERVER_NAMES]', $server_names, $content);

            if ($handle = fopen(ZM_FILE_SITE_SWITCHER.'.tmp', 'wb')) {
                $ok = fwrite($handle, $content);
                fclose($handle);
                if (false !== $ok) {
                    if (file_exists(ZM_FILE_SITE_SWITCHER)) {
                        unlink(ZM_FILE_SITE_SWITCHER);
                    }
                    rename(ZM_FILE_SITE_SWITCHER.'.tmp', ZM_FILE_SITE_SWITCHER);
                } else {
                    //error!
                }
            }
        }
    }

?>
