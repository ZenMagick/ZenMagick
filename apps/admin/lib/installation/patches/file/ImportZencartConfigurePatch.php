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
namespace zenmagick\apps\store\admin\installation\patches\file;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\apps\store\admin\installation\patches\FilePatch;
use Symfony\Component\Yaml\Yaml;

define('_ZM_STORE_CONFIG_YAML', Runtime::getInstallationPath().'/config/store-config.yaml');
define('_ZM_ZEN_CONFIGURE_PHP', ZC_INSTALL_PATH .'includes/configure.php');


/**
 * Patch to create a config/store-config.yaml from zencart includes/configure.php
 *
 * @todo what to do about settings that get moved to plugins for plugins that might not exist
 *       yet like music_product_extra or phpbb3
 */
class ImportZencartConfigurePatch extends FilePatch {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('importZencartConfigure');
        $this->label_ = 'Create or update ZenMagick store-config.yaml from configure.php';
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        if (!file_exists(_ZM_STORE_CONFIG_YAML)) return true;
        $config = Toolbox::loadWithEnv(_ZM_STORE_CONFIG_YAML);
        // doesn't exist in etc/build/store-config.yaml
        return !isset($config['apps']['store']['database']['default']['dbname']);
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        $writeable = !file_exists(_ZM_STORE_CONFIG_YAML) || is_writeable(_ZM_STORE_CONFIG_YAML);
        $canWriteFile = is_writeable(dirname(_ZM_STORE_CONFIG_YAML)) && $writeable;
        return file_exists(_ZM_ZEN_CONFIGURE_PHP) && $canWriteFile;
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_STORE_CONFIG_YAML . " or " . _ZM_ZEN_CONFIGURE_PHP . " does not exist";
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        if (!$this->isOpen()) return true;
        
        include_once _ZM_ZEN_CONFIGURE_PHP;

        // find DB_CHARSET
        $extraConfigures = glob(ZC_INSTALL_PATH . '/includes/extra_configures/*.php');
        foreach ($extraConfigures as $extraConfigure) {
            include_once $extraConfigure;
        }

        $storeConfig = array();
        $dbServer = explode(':', DB_SERVER); 
        $host = $dbServer[0];
        $port = (isset($dbServer[1]) && $dbServer[1] != 3306) ? $dbServer[1] : null;
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8';
        $dbConfig = array( // @todo should some of these defaults be in the application configuration defaults instead?
            'charset' => $charset,
            'dbname' => DB_DATABASE,
            'driver' => 'pdo_mysql',
            'host' => $host,
            'password' => DB_SERVER_PASSWORD,
            'port' => $port,
            'unix_socket' => null, // @todo possible in zencart ?
            'user' => DB_SERVER_USERNAME,
            'collation' => null,
        );

        $storeConfig['apps']['store']['database']['default'] = $dbConfig;

        if (basename(DIR_FS_DOWNLOAD) != 'download') {
            $storeConfig['downloadBaseDir'] = DIR_FS_DOWNLOAD;
        }

        $secure = ENABLE_SSL == 'true';
        $storeConfig['zenmagick']['http']['request']['secure'] = $secure;
        $storeConfig['zenmagick']['http']['request']['enforceSecure'] = $secure;

        // @todo for some reason this indents at 4 space.
        $yaml = Yaml::dump($storeConfig, 5);
        $header = '##
## shared ZenMagick store config
##
## NOTE: This file is generated (and may be re-written) automatically - edit at own risk.
##
';
        file_put_contents(_ZM_STORE_CONFIG_YAML, $header.$yaml);

        Runtime::getSettings()->setAll($storeConfig);
        return true;
    }

    /**
     * {@inheritDoc} 
     */
    function canUndo() {
        return false;
    }
}
