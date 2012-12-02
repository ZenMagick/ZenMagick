<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace ZenMagick\AdminBundle\Installation\Patches\File;

use ZenMagick\Base\Runtime;
use ZenMagick\AdminBundle\Installation\Patches\FilePatch;
use Symfony\Component\Yaml\Yaml;

define('_ZM_STORE_CONFIG_YAML', Runtime::getInstallationPath().'/config/parameters.yml');

/**
 * Patch to create a config/store-config.yaml from zencart includes/configure.php
 *
 * @todo what to do about settings that get moved to plugins for plugins that might not exist
 *       yet like music_product_extra or phpbb3
 */
class ImportZencartConfigurePatch extends FilePatch
{
    protected $configurePhpFile;
    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct('importZencartConfigure');
        $this->label_ = 'Create or update ZenMagick store-config.yaml from configure.php';
        $this->configurePhpFile = Runtime::getSettings()->get('zencart.root_dir').'/includes/configure.php';
    }

    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    public function isOpen()
    {
        if (!file_exists(_ZM_STORE_CONFIG_YAML)) return true;
        $config = Yaml::parse(_ZM_STORE_CONFIG_YAML);

        return !isset($config['database_name']);
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    public function isReady()
    {
        $writeable = !file_exists(_ZM_STORE_CONFIG_YAML) || is_writeable(_ZM_STORE_CONFIG_YAML);
        $canWriteFile = is_writeable(dirname(_ZM_STORE_CONFIG_YAML)) && $writeable;

        return file_exists($this->configurePhpFile) && $canWriteFile;
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    public function getPreconditionsMessage()
    {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_STORE_CONFIG_YAML . " or " . $this->configurePhpFile . " does not exist";
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    public function patch($force=false)
    {
        if (!$this->isOpen()) return true;

        include_once $this->configurePhpFile;

        // find DB_CHARSET
        $zcPath = Runtime::getSettings()->get('zencart.root_dir');
        $extraConfigures = glob($zcPath.'/includes/extra_configures/*.php');
        foreach ($extraConfigures as $extraConfigure) {
            include_once $extraConfigure;
        }

        $dbServer = explode(':', DB_SERVER);
        $host = $dbServer[0];
        $port = (isset($dbServer[1]) && $dbServer[1] != 3306) ? $dbServer[1] : null;
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8';
        $parameters = array( // @todo should some of these defaults be in the application configuration defaults instead?
            'charset' => $charset,
            'database_name' => DB_DATABASE,
            'table_prefix' => DB_PREFIX,
            'database_host' => $host,
            'database_password' => DB_SERVER_PASSWORD,
            'database_port' => $port,
            'database_user' => DB_SERVER_USERNAME,
        );

        if (basename(DIR_FS_DOWNLOAD) != 'download') {
            $parameters['downloadBaseDir'] = DIR_FS_DOWNLOAD;
        }

        $secure = ENABLE_SSL == 'true';
        $parameters['zenmagick']['http']['request']['secure'] = $secure;

        $yaml = Yaml::dump($parameters, 5);
        $header = '##
##
## NOTE: This file is generated (and may be re-written) automatically - edit at own risk.
##
';
        file_put_contents(_ZM_STORE_CONFIG_YAML, $header.$yaml);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function canUndo()
    {
        return false;
    }
}
