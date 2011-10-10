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
namespace zenmagick\apps\store\bundles\ZenCartBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use zenmagick\base\Runtime;

/**
 * Zencart support bundle.
 *
 * @author DerManoMann
 * @package zenmagick.apps.store.bundles.ZenCartBundle
 */
class ZenCartBundle extends Bundle {

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container) {
        parent::build($container);
    }

    /**
     * {@inheritDoc}
     */
    public function boot() {
        Runtime::getEventDispatcher()->addListener('init_request', array($this, 'onInitRequest'));
        $zcClassLoader = new ZenCartClassLoader();
        $zcClassLoader->register();
        $this->prepareConfig();

        define('ZC_INSTALL_PATH', dirname(Runtime::getInstallationPath()).DIRECTORY_SEPARATOR);
    }

    /**
     * Load configure.php to get the required store-container settings if required.
     *
     * @todo This will eventually be deprecated once we have an installer to write store-config.yaml
     */
    protected function prepareConfig() {
        $configure = dirname(Runtime::getInstallationPath()).(defined('ZC_ADMIN_FOLDER') ? '/'.ZC_ADMIN_FOLDER : '').'/includes/configure.php';
        if (!file_exists($configure)) {
            throw new \Exception('could not find zencart configure.php');
        }

        // check for existing defines
        foreach (array('DB_SERVER', 'DB_SERVER_USERNAME', 'DB_SERVER_PASSWORD', 'DB_DATABASE', 'DB_PREFIX', 'ENABLE_SSL', 'ENABLE_SSL_ADMIN', 'DIR_FS_DOWNLOAD', 'DIR_WS_CATALOG', 'ENABLE_SSL', 'ENABLE_SSL_ADMIN') as $key) {
            if (defined($key)) {
                define('ZM_'.$key, constant($key));
            }
        }

        // pick the lines we need
        $lines = file($configure);
        $defines = array();
        foreach ($lines as $line) {
            if (false !== strpos($line, 'define')) {
                $defines[] = str_replace(
                    array("define('", " DIR_WS_INCLUDES ", " DIR_WS_CATALOG ", " DIR_FS_CATALOG ", " DIR_WS_IMAGES ", " DIR_FS_SQL_CACHE.", " DIR_FS_ADMIN ", " HTTP_CATALOG_SERVER "),
                    array("define('ZM_", " ZM_DIR_WS_INCLUDES ", " ZM_DIR_WS_CATALOG ", " ZM_DIR_FS_CATALOG ", " ZM_DIR_WS_IMAGES ", " ZM_DIR_FS_SQL_CACHE.", " ZM_DIR_FS_ADMIN ", " ZM_HTTP_CATALOG_SERVER "),
                    $line
                );
            }
        }
        eval(implode("\n", $defines));
        $defaults = array(
            'host' => ZM_DB_SERVER,
            'user' => ZM_DB_SERVER_USERNAME,
            'password' => ZM_DB_SERVER_PASSWORD,
            'dbname' => ZM_DB_DATABASE,
            'prefix' => ZM_DB_PREFIX,
            'charset' => (defined("ZM_DB_CHARSET") ? ZM_DB_CHARSET : "utf8")
        );

        $settingsService = Runtime::getSettings();
        // merge with current settings
        $current = $settingsService->get('zenmagick/apps/store/database/default', array());
        $settingsService->set('zenmagick/apps/store/database/default', array_merge($defaults, $current));
        if (defined('ZM_ENABLE_SSL_ADMIN')) {
            $settingsService->set('zenmagick.mvc.request.secure', 'true' == ZM_ENABLE_SSL_ADMIN);
        } else {
            $settingsService->set('zenmagick.mvc.request.secure', 'true' == ZM_ENABLE_SSL);
        }
    }

    /**
     * Handle things that require a request.
     */
    public function onInitRequest($event) {
        if (defined('ZM_ENABLE_SSL_ADMIN')) {
            // non db settings (admin)
            $request = $event->get('request');
            $settingsService = Runtime::getSettings();
            $settingsService->set('apps.store.baseUrl', 'http://'.$request->getHostname().str_replace('zenmagick/apps/admin/web', '', $request->getContext()));
            $settingsService->set('apps.store.oldAdminUrl', $settingsService->get('apps.store.baseUrl').ZC_ADMIN_FOLDER.'/index.php');
        }
    }

}
