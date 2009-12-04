<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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

if (ZMSettings::get('isAdmin')) {
    define('ZM_STORE_LOCAL_CONFIGURE', DIR_FS_CATALOG.'includes/local/configure.php');
    define('ZM_ADMIN_LOCAL_CONFIGURE', DIR_FS_ADMIN.'includes/local/configure.php');
}

/**
 * Plugin that allows to switch themes based on the hostname.
 *
 * @package org.zenmagick.plugins.zm_site_switch
 * @author mano
 * @version $Id$
 */
class zm_site_switch extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Site Switch', 'Hostname based theme switching', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);

        // the new prices and customer flag
        $customFields = array(
            'customers' => 'site_id;string;siteId',
            'orders' => 'site_id;string;siteId'
        );
        foreach ($customFields as $table => $fields) {
            ZMSettings::append('zenmagick.core.database.sql.'.$table.'.customFields', $fields, ',');
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Install this plugin.
     */
    function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/install.sql")), $this->messages_);
    }


    /**
     * Init this plugin.
     */
    public function init() {
    global $zm_server_names;

        parent::init();

        ZMEvents::instance()->attach($this);

        define('ZM_FILE_SITE_SWITCHER', $this->getConfigPath('config.php'));
        define('ZM_SITE_SWITCHER_CONFIGURE_LINE', '<?php include("'.ZM_FILE_SITE_SWITCHER.'"); /* added by zm_site_switch plugin */ ?>');

        $this->addMenuItem('zm_site_switch', zm_l10n_get('Site Switching'), 'zm_site_switch_admin');

        $hostname = ZMRequest::instance()->getHostname();

        if (isset($zm_server_names[$hostname])) {
            Runtime::setThemeId($zm_server_names[$hostname]);
        }
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        $this->removeSwitcher(ZM_STORE_LOCAL_CONFIGURE);
        $this->removeSwitcher(ZM_ADMIN_LOCAL_CONFIGURE);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/uninstall.sql")), $this->messages_);
    }

    /**
     * Event handler to tag orders with the original domain.
     *
     * @param array args Optional parameter.
     */
    public function onNotifyCheckoutProcessAfterOrderCreateAddProducts($args=array()) {
        $orderId = $_SESSION['order_number_created'];
        $sql = 'UPDATE '.TABLE_ORDERS.'
                SET site_id = :siteId
                WHERE orders_id = :orderId';
        ZMRuntime::getDatabase()->update($sql, array('orderId' => $orderId, 'siteId' => ZMRequest::instance()->getHostname()), TABLE_ORDERS);
    }

    /**
     * Check required permissions.
     *
     * @return boolean <code>true</code> if permissions are ok, <code>false</code> if not.
     */
    public function checkPermissions() {
        $localDir = dirname(ZM_FILE_SITE_SWITCHER);
        if (!file_exists($localDir)) {
            // try to create
            ZMFileUtils::mkdir($localDir);
            // can we create folder than all ok...
            if (is_writeable(dirname($localDir))) {
                return true;
            }
            ZMMessages::instance()->error('need permission to write '.dirname($localDir));
            return false;
        }

        if (is_writeable(dirname($localDir))) {
            $localConfig = $localDir.'/configure.php';
            if (file_exists($localConfig) && !is_writeable($localConfig)) {
                ZMMessages::instance()->error('need permission to update '.$localConfig);
                return false;
            }
            return true;
        }

        ZMMessages::instance()->error('need permission to write '.$localDir);
        return false;
    }

    /**
     * Setup/validate local config setup.
     *
     * @param string localConfig The configure filename.
     */
    public function setupSwitcher($localConfig) {
        $localDir = dirname($localConfig);
        if (!is_dir($localDir)) {
            ZMFileUtils::mkdir($localDir);
            if (!is_dir($localDir)) {
                ZMMessages::instance()->error('could not create directory: \''.$localDir.'\'');
                return;
            }
        }

        if (!file_exists($localConfig)) {
            if ($handle = fopen($localConfig, 'wb')) {
                $ok = fwrite($handle, ZM_SITE_SWITCHER_CONFIGURE_LINE);
                fclose($handle);
                ZMFileUtils::setFilePerms($localConfig);
            } else {
                ZMMessages::instance()->error('could not create file: \''.ZM_SITE_SWITCHER_CONFIGURE_LINE.'\'');
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
                    ZMFileUtils::setFilePerms($localConfig.'.tmp');
                    if (file_exists($localConfig)) {
                        unlink($localConfig);
                    }
                    rename($localConfig.'.tmp', $localConfig);
                } else {
                    ZMMessages::instance()->error('could not create file in \''.$localDir.'\'');
                    return;
                }
            }
        }
    }

    /**
     * Update site switcher config.
     *
     * @param array List of sitemap/themeId mappings
     */
    public function writeConfig($mappings) {
        if (0 < count($mappings)) {
            // update file
            $content = '<?php  
$zm_server_names = array([SERVER_NAMES]);
if (!defined("HTTP_SERVER")) {
    $_zm_server_name = $_SERVER["HTTP_HOST"];
    if (isset($zm_server_names[$_zm_server_name])) {
      define("HTTP_SERVER", "http://$_zm_server_name");
      define("HTTPS_SERVER", "https://$_zm_server_name");
    }
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
                ZMFileUtils::setFilePerms($ZM_FILE_SITE_SWITCHER.'.tmp');
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

    /**
     * Uninstall switcher.
     *
     * @param string localConfig The configure filename.
     */
    protected function removeSwitcher($localConfig) {
        $localDir = dirname($localConfig);
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
                ZMFileUtils::setFilePerms($localConfig.'.tmp');
                if (file_exists($localConfig)) {
                    unlink($localConfig);
                }
                rename($localConfig.'.tmp', $localConfig);
            } else {
                ZMMessages::instance()->error('could not write temp file in: \''.$localDir.'\'');
                return;
            }
        }
    }

}

?>
